<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\IngresoLenguaLocal;
use App\Support\DestinoEntregaParser;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EntregaConformidadController extends Controller
{
    public function index(Request $request): View
    {
        $viendoTodos = $request->boolean('todos');
        $tz = (string) config('app.timezone', 'UTC');
        $inicioMes = Carbon::now($tz)->startOfMonth();
        $finMes = Carbon::now($tz)->endOfMonth();

        $query = Despacho::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count')
            ->orderByDesc('realizado_at')
            ->orderByDesc('id');

        if (! $viendoTodos) {
            $query->whereBetween('realizado_at', [$inicioMes, $finMes]);
        }

        $despachos = $query->paginate(25)->withQueryString();

        return view('entrega-conformidad.index', [
            'despachos' => $despachos,
            'viendoTodos' => $viendoTodos,
            'mesEtiqueta' => $inicioMes->copy()->locale('es')->translatedFormat('F Y'),
        ]);
    }

    public function pdf(Despacho $despacho): Response
    {
        $despacho->load('user');

        $lineas = IngresoLenguaLocal::query()
            ->where('despacho_id', $despacho->id)
            ->orderBy('id')
            ->get(['id_producto', 'destino', 'fecha_registro', 'hora_registro']);

        $expedicionAt = now()->timezone(config('app.timezone'));

        $pdf = Pdf::loadView('entrega-conformidad.pdf-documento', [
            'despacho' => $despacho,
            'lineas' => $lineas,
            'expedicionAt' => $expedicionAt,
            'logoDataUri' => $this->logoInstitucionalDataUri(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $nombre = 'documento-conformidad-despacho-'.$despacho->id.'.pdf';

        return $pdf->stream($nombre);
    }

    /**
     * Manifiesto de destinos (HTML imprimible) a partir del campo destino de cada lengua despachada.
     */
    public function destinosManifest(Despacho $despacho): Response
    {
        $despacho->load('user');
        $payload = $this->manifiestoDestinosPayload($despacho);

        $html = view('entrega-conformidad.destinos-manifest', [
            'despacho' => $despacho,
            'filas' => $payload['filas'],
            'numDestinos' => $payload['num_destinos'],
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * Datos del manifiesto para generar PNG en la misma página (sin abrir pestaña nueva).
     */
    public function destinosManifestJson(Despacho $despacho): JsonResponse
    {
        $payload = $this->manifiestoDestinosPayload($despacho);

        return response()->json([
            'despacho' => [
                'id' => $despacho->id,
                'conductor' => $despacho->conductor ?? '',
                'placa' => $despacho->placa ?? '',
            ],
            'num_destinos' => $payload['num_destinos'],
            'filas' => $payload['filas'],
        ]);
    }

    /**
     * Avisos de despachos nuevos para el rol Auxiliar (sonido + sacar conformidad).
     * Otros roles reciben lista vacía.
     */
    public function avisosNuevos(Request $request): JsonResponse
    {
        $role = (string) ($request->user()?->role ?? '');
        if (! in_array($role, ['auxiliar', 'user'], true)) {
            return response()->json([
                'enabled' => false,
                'avisos' => [],
                'latest_id' => 0,
            ]);
        }

        $afterId = max(0, (int) $request->query('after_id', 0));
        $latestId = (int) (Despacho::query()->max('id') ?? 0);
        $cacheAviso = Cache::get('despacho.conformidad.aviso');
        if (is_array($cacheAviso) && (int) ($cacheAviso['id'] ?? 0) > $latestId) {
            $latestId = (int) $cacheAviso['id'];
        }

        // Primera carga: si hay aviso reciente en caché (< 3 min), devolverlo para no perderlo.
        if ($afterId <= 0) {
            $avisosInicial = [];
            if (is_array($cacheAviso)) {
                $cacheId = (int) ($cacheAviso['id'] ?? 0);
                $ts = (int) ($cacheAviso['ts'] ?? 0);
                if ($cacheId > 0 && $ts > 0 && (now()->getTimestamp() - $ts) <= 180) {
                    $avisosInicial[] = $this->formatearAvisoDespacho(
                        $cacheId,
                        (string) ($cacheAviso['placa'] ?? ''),
                        (string) ($cacheAviso['conductor'] ?? ''),
                        (string) ($cacheAviso['pdf_url'] ?? ''),
                        (string) ($cacheAviso['realizado_at'] ?? ''),
                        isset($cacheAviso['numero']) ? (int) $cacheAviso['numero'] : null,
                    );
                }
            }

            return response()->json([
                'enabled' => true,
                'avisos' => $avisosInicial,
                'latest_id' => $latestId,
            ]);
        }

        $rows = Despacho::query()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(15)
            ->get(['id', 'placa', 'conductor', 'realizado_at']);

        $avisos = $rows->map(function (Despacho $d): array {
            return $this->formatearAvisoDespacho(
                (int) $d->id,
                (string) ($d->placa ?? ''),
                (string) ($d->conductor ?? ''),
                route('entrega.conformidad.pdf', $d),
                optional($d->realizado_at)?->timezone(config('app.timezone'))->format('H:i:s') ?? '',
                null,
            );
        })->values()->all();

        // Refuerzo: aviso en caché del último despacho (por si el poll coincide justo al cerrar).
        if (is_array($cacheAviso)) {
            $cacheId = (int) ($cacheAviso['id'] ?? 0);
            if ($cacheId > $afterId) {
                $ya = false;
                foreach ($avisos as $a) {
                    if ((int) ($a['id'] ?? 0) === $cacheId) {
                        $ya = true;
                        break;
                    }
                }
                if (! $ya) {
                    $avisos[] = $this->formatearAvisoDespacho(
                        $cacheId,
                        (string) ($cacheAviso['placa'] ?? ''),
                        (string) ($cacheAviso['conductor'] ?? ''),
                        (string) ($cacheAviso['pdf_url'] ?? ''),
                        (string) ($cacheAviso['realizado_at'] ?? ''),
                        isset($cacheAviso['numero']) ? (int) $cacheAviso['numero'] : null,
                    );
                }
            }
        }

        return response()->json([
            'enabled' => true,
            'avisos' => $avisos,
            'latest_id' => max($latestId, $afterId),
        ]);
    }

    /**
     * @return array{id: int, numero: int, placa: string, conductor: string, pdf_url: string, realizado_at: string}
     */
    private function formatearAvisoDespacho(
        int $id,
        string $placa,
        string $conductor,
        string $pdfUrl,
        string $realizadoAt,
        ?int $numero = null,
    ): array {
        if ($numero === null || $numero <= 0) {
            $numero = (int) Despacho::query()->where('id', '<=', $id)->count();
            if ($numero <= 0) {
                $numero = (int) Despacho::query()->count();
            }
        }

        return [
            'id' => $id,
            'numero' => $numero,
            'placa' => $placa,
            'conductor' => $conductor,
            'pdf_url' => $pdfUrl !== '' ? $pdfUrl : route('entrega.conformidad.pdf', $id),
            'realizado_at' => $realizadoAt,
        ];
    }

    /**
     * @return array{filas: list<array{codigo: string, direccion: string}>, num_destinos: int}
     */
    private function manifiestoDestinosPayload(Despacho $despacho): array
    {
        $lineas = IngresoLenguaLocal::query()
            ->where('despacho_id', $despacho->id)
            ->orderBy('id')
            ->get(['destino']);

        $vistos = [];
        $filas = [];
        foreach ($lineas as $linea) {
            $parsed = DestinoEntregaParser::destinoYDireccion($linea->destino);
            $clave = Str::lower($parsed['codigo'].'|'.$parsed['direccion']);
            if (isset($vistos[$clave])) {
                continue;
            }
            $vistos[$clave] = true;
            $filas[] = $parsed;
        }

        return [
            'filas' => $filas,
            'num_destinos' => count($filas),
        ];
    }

    private function logoInstitucionalDataUri(): ?string
    {
        foreach (['images/fond.png', 'images/lengua.png'] as $rel) {
            $path = public_path($rel);
            if (! is_readable($path)) {
                continue;
            }
            $raw = @file_get_contents($path);
            if ($raw === false || $raw === '') {
                continue;
            }

            return 'data:image/png;base64,'.base64_encode($raw);
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\IngresoLenguaLocal;
use App\Support\DestinoEntregaParser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EntregaConformidadController extends Controller
{
    public function index(): View
    {
        $despachos = Despacho::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count')
            ->orderByDesc('realizado_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('entrega-conformidad.index', [
            'despachos' => $despachos,
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

<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use App\Models\Desposte;
use App\Models\IngresoLenguaLocal;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HistoriaDespachoLenguasController extends Controller
{
    public function index(): View
    {
        $despachos = Despacho::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count')
            ->get();

        $despostes = Desposte::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count')
            ->get();

        $filas = Collection::make();

        foreach ($despachos as $d) {
            $ts = $d->realizado_at?->format('Y-m-d H:i:s') ?? '1970-01-01 00:00:00';
            $filas->push((object) [
                'movimiento_tipo' => 'despacho',
                'realizado_at' => $d->realizado_at,
                'empresa' => $d->empresa,
                'conductor' => $d->conductor,
                'placa' => $d->placa,
                'lenguas_count' => (int) ($d->lenguas_count ?? 0),
                'user' => $d->user,
                'detalle_url' => route('historia.despacho.lenguas.detalle', $d),
                'dialog_title' => 'Detalle del despacho',
                '_sort' => $ts.' A '.str_pad((string) $d->id, 10, '0', STR_PAD_LEFT),
            ]);
        }

        foreach ($despostes as $x) {
            $ts = $x->realizado_at?->format('Y-m-d H:i:s') ?? '1970-01-01 00:00:00';
            $filas->push((object) [
                'movimiento_tipo' => 'desposte',
                'realizado_at' => $x->realizado_at,
                'empresa' => null,
                'conductor' => null,
                'placa' => null,
                'lenguas_count' => (int) ($x->lenguas_count ?? 0),
                'user' => $x->user,
                'detalle_url' => route('historia.desposte.lenguas.detalle', $x),
                'dialog_title' => 'Detalle — Planta de Desposte',
                '_sort' => $ts.' B '.str_pad((string) $x->id, 10, '0', STR_PAD_LEFT),
            ]);
        }

        $ordenados = $filas->sortByDesc(static fn (object $r): string => $r->_sort)->values();

        $porPagina = 30;
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $items = $ordenados->forPage($pagina, $porPagina)->map(static function (object $r): object {
            unset($r->_sort);

            return $r;
        })->values();

        $movimientos = new LengthAwarePaginator(
            $items,
            $ordenados->count(),
            $porPagina,
            $pagina,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ],
        );
        $movimientos->withQueryString();

        return view('historia-despacho-lenguas', [
            'movimientos' => $movimientos,
        ]);
    }

    public function detalle(Despacho $despacho): JsonResponse
    {
        $lineas = IngresoLenguaLocal::query()
            ->where('despacho_id', $despacho->id)
            ->orderBy('id')
            ->get(['id_producto', 'propietario', 'destino'])
            ->map(static fn (IngresoLenguaLocal $r): array => [
                'id_producto' => (string) $r->id_producto,
                'propietario' => $r->propietario !== null && $r->propietario !== '' ? (string) $r->propietario : '',
                'destino' => $r->destino !== null && $r->destino !== '' ? (string) $r->destino : '',
            ])
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'despacho_id' => $despacho->id,
            'realizado_at' => $despacho->realizado_at?->toIso8601String(),
            'lineas' => $lineas,
        ]);
    }

    public function detalleDesposte(Desposte $desposte): JsonResponse
    {
        $lineas = IngresoLenguaLocal::query()
            ->where('desposte_id', $desposte->id)
            ->orderBy('id')
            ->get(['id_producto', 'propietario', 'destino'])
            ->map(static fn (IngresoLenguaLocal $r): array => [
                'id_producto' => (string) $r->id_producto,
                'propietario' => $r->propietario !== null && $r->propietario !== '' ? (string) $r->propietario : '',
                'destino' => $r->destino !== null && $r->destino !== '' ? (string) $r->destino : '',
            ])
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'desposte_id' => $desposte->id,
            'realizado_at' => $desposte->realizado_at?->toIso8601String(),
            'lineas' => $lineas,
        ]);
    }
}

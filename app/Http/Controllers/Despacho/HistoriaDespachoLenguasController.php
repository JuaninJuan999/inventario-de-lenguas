<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use App\Models\IngresoLenguaLocal;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HistoriaDespachoLenguasController extends Controller
{
    public function index(): View
    {
        $despachos = Despacho::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count')
            ->orderByDesc('realizado_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('historia-despacho-lenguas', [
            'despachos' => $despachos,
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
}

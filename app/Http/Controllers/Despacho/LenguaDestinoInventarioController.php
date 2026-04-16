<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Models\IngresoLenguaLocal;
use App\Support\DespachoCodigoBarras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenguaDestinoInventarioController extends Controller
{
    /**
     * Resuelve destino desde la réplica local (inventario) usando id_producto normalizado.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $codigo = substr(trim((string) $request->query('codigo', '')), 0, 80);
        if ($codigo === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Indique un código de producto.',
            ], 422);
        }

        $idProducto = DespachoCodigoBarras::normalizarIdProducto($codigo);

        $row = IngresoLenguaLocal::query()
            ->sinDespachar()
            ->where('id_producto', $idProducto)
            ->orderByDesc('imported_at')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'ok' => true,
            'codigo_leido' => $codigo,
            'id_producto' => $idProducto,
            'destino' => $row?->destino,
            'encontrado' => $row !== null,
        ]);
    }
}

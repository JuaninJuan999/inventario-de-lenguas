<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Support\DespachoCodigoBarras;
use App\Support\DespachoInventarioMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenguaDestinoInventarioController extends Controller
{
    /**
     * Resuelve destino desde la réplica local (inventario) usando id_producto normalizado.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $codigo = substr(trim((string) $request->get('codigo', '')), 0, 80);
        if ($codigo === '') {
            $codigo = substr(trim((string) $request->header('X-Despacho-Codigo-Lengua', '')), 0, 80);
        }
        if ($codigo === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Indique un código de producto.',
            ], 422);
        }

        $row = DespachoInventarioMatch::findAvailableRow($codigo);
        $idProducto = $row !== null
            ? (string) $row->id_producto
            : DespachoCodigoBarras::normalizarIdProducto($codigo);

        return response()->json([
            'ok' => true,
            'codigo_leido' => $codigo,
            'id_producto' => $idProducto,
            'propietario' => $row?->propietario,
            'destino' => $row?->destino,
            'encontrado' => $row !== null,
        ]);
    }
}

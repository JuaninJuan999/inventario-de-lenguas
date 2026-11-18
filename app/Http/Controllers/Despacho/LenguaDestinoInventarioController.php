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

        $decomiso = null;
        if ($row === null) {
            $decomiso = DespachoInventarioMatch::infoDecomiso($codigo);
            if ($decomiso !== null && ! empty($decomiso['id_producto'])) {
                $idProducto = (string) $decomiso['id_producto'];
            }
        }

        $decomisado = $decomiso !== null && ($decomiso['decomisado'] ?? false);

        return response()->json([
            'ok' => true,
            'codigo_leido' => $codigo,
            'id_producto' => $idProducto,
            'propietario' => $row?->propietario,
            'destino' => $row?->destino,
            'es_adicional' => (bool) ($row?->es_adicional ?? false),
            'encontrado' => $row !== null,
            'decomisado' => $decomisado,
            'decomiso' => $decomisado ? [
                'nombre_dictamen' => $decomiso['nombre_dictamen'] ?? null,
                'nombre_enfermedad' => $decomiso['nombre_enfermedad'] ?? null,
                'origen' => $decomiso['origen'] ?? null,
            ] : null,
        ]);
    }
}

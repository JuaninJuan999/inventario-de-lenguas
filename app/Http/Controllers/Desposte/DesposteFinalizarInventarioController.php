<?php

namespace App\Http\Controllers\Desposte;

use App\Http\Controllers\Controller;
use App\Models\Desposte;
use App\Models\IngresoLenguaLocal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DesposteFinalizarInventarioController extends Controller
{
    /**
     * Da de baja en inventario local cada id de producto enviado a Planta de Desposte
     * y registra cabecera en historial de despostes.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigos' => ['required', 'array', 'min:1', 'max:500'],
            'codigos.*' => ['required', 'string', 'max:80'],
        ]);

        $notFound = [];
        $removed = 0;

        try {
            DB::transaction(function () use ($validated, $request, &$notFound, &$removed): void {
                $realizadoAt = now();
                $desposte = Desposte::query()->create([
                    'user_id' => $request->user()?->id,
                    'realizado_at' => $realizadoAt,
                ]);

                foreach ($validated['codigos'] as $codigo) {
                    $codigo = trim((string) $codigo);
                    if ($codigo === '') {
                        continue;
                    }

                    $row = IngresoLenguaLocal::query()
                        ->sinDespachar()
                        ->where('id_producto', $codigo)
                        ->orderByDesc('imported_at')
                        ->orderByDesc('id')
                        ->first();

                    if ($row === null) {
                        $notFound[] = $codigo;

                        continue;
                    }

                    $row->despachado_at = $realizadoAt;
                    $row->desposte_id = $desposte->id;
                    $row->save();
                    $removed++;
                }

                if ($removed === 0) {
                    $desposte->delete();
                }
            });
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo actualizar el inventario local. Intente nuevamente.',
            ], 500);
        }

        if ($removed === 0) {
            return response()->json([
                'ok' => false,
                'removed' => 0,
                'not_found' => $notFound,
                'message' => 'Ningún id de producto coincidía con el inventario local; no se aplicaron bajas.',
            ], 422);
        }

        $message = $removed === 1
            ? 'Se registró 1 lengua enviada a Planta de Desposte y se retiró del inventario local.'
            : "Se registraron {$removed} lenguas enviadas a Planta de Desposte y se retiraron del inventario local.";

        if ($notFound !== []) {
            $slice = array_slice($notFound, 0, 15);
            $message .= ' Sin fila en inventario (no descontados): '.implode(', ', $slice);
            if (count($notFound) > 15) {
                $message .= '…';
            }
        }

        return response()->json([
            'ok' => true,
            'removed' => $removed,
            'not_found' => $notFound,
            'message' => $message,
        ]);
    }
}

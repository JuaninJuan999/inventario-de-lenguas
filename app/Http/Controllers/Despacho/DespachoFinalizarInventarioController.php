<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Despacho\Concerns\ResuelveUsuarioVehiculoDespacho;
use App\Models\Despacho;
use App\Services\Despacho\DespachoParteProductoVehiculoConsulta;
use App\Support\DespachoInventarioMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DespachoFinalizarInventarioController extends Controller
{
    use ResuelveUsuarioVehiculoDespacho;

    /**
     * Da de baja en inventario local una fila por cada código despachado (id_producto más reciente)
     * y registra cabecera en historial de despachos.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigos' => ['required', 'array', 'min:1', 'max:500'],
            'codigos.*' => ['required', 'string', 'max:80'],
            'empresa' => ['nullable', 'string', 'max:512'],
            'conductor' => ['nullable', 'string', 'max:255'],
            'placa' => ['nullable', 'string', 'max:64'],
            'id_vehiculo_asignado' => ['nullable', 'integer', 'min:1'],
        ]);

        $codigosParaBaja = array_values(array_filter(array_map(
            static fn ($c): string => trim((string) $c),
            $validated['codigos']
        ), static fn (string $s): bool => $s !== ''));

        if ($codigosParaBaja === []) {
            return response()->json([
                'ok' => false,
                'message' => 'No se enviaron códigos válidos.',
            ], 422);
        }

        $idVa = isset($validated['id_vehiculo_asignado'])
            ? (int) $validated['id_vehiculo_asignado']
            : 0;

        if ($idVa > 0) {
            $consulta = app(DespachoParteProductoVehiculoConsulta::class);
            $username = $this->usernameVehiculoEfectivo($request);
            $expectedRaw = $consulta->idsProductoPendientes($idVa, $username, true);

            if ($expectedRaw === []) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No hay pendientes Colbeef para ese vehículo o no coincide el filtro de usuario.',
                ], 422);
            }

            $esp = DespachoInventarioMatch::multisetCanonicoGreedy($expectedRaw);
            $env = DespachoInventarioMatch::multisetCanonicoGreedy($codigosParaBaja);

            if ($esp === false) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Hay códigos del checklist sin stock disponible en inventario local (multiset esperado inválido).',
                ], 422);
            }

            if ($env === false) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Algún código enviado no tiene coincidencia disponible en inventario local.',
                ], 422);
            }

            $freqEsp = array_count_values($esp);
            $freqEnv = array_count_values($env);
            foreach ($freqEsp as $codigo => $necesario) {
                if (($freqEnv[$codigo] ?? 0) < $necesario) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'La lista debe incluir al menos todos los códigos del checklist Colbeef de este vehículo (mismas unidades e id de producto en inventario). Puede añadir códigos adicionales, pero no faltan del listado base.',
                    ], 422);
                }
            }
        }

        $notFound = [];
        $removed = 0;

        try {
            DB::transaction(function () use ($validated, $codigosParaBaja, $request, &$notFound, &$removed, $idVa): void {
                $realizadoAt = now();
                $despacho = Despacho::query()->create([
                    'user_id' => $request->user()?->id,
                    'id_vehiculo_asignado' => $idVa > 0 ? $idVa : null,
                    'empresa' => $this->nullIfBlank($validated['empresa'] ?? null),
                    'conductor' => $this->nullIfBlank($validated['conductor'] ?? null),
                    'placa' => $this->nullIfBlank($validated['placa'] ?? null),
                    'realizado_at' => $realizadoAt,
                ]);

                foreach ($codigosParaBaja as $codigo) {
                    $row = DespachoInventarioMatch::findAvailableRow($codigo);

                    if ($row === null) {
                        $notFound[] = $codigo;

                        continue;
                    }

                    $row->despachado_at = $realizadoAt;
                    $row->despacho_id = $despacho->id;
                    $row->save();
                    $removed++;
                }

                if ($removed === 0) {
                    $despacho->delete();
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
                'message' => 'Ningún código coincidía con el inventario local; no se aplicaron bajas.',
            ], 422);
        }

        $message = $removed === 1
            ? 'Se marcó como despachado 1 registro en el inventario local (no volverá a mostrarse ni al sincronizar con SIRT).'
            : "Se marcaron como despachados {$removed} registros en el inventario local (no volverán a mostrarse ni al sincronizar con SIRT).";

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
            'redirect_url' => $idVa > 0 ? route('despacho.lenguas') : null,
        ]);
    }

    private function nullIfBlank(?string $value): ?string
    {
        $t = $value !== null ? trim($value) : '';

        return $t === '' ? null : $t;
    }
}

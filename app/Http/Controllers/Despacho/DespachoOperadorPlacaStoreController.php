<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Models\DespachoOperadorPlaca;
use App\Services\Despacho\DespachoParteProductoVehiculoConsulta;
use Database\Seeders\DespachoOperadorPlacaSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class DespachoOperadorPlacaStoreController extends Controller
{
    /**
     * Alta de par operador logístico + placa en el catálogo local.
     * Si el operador ya existe (mismo nombre, sin importar mayúsculas), se reutiliza
     * y solo se añade la placa nueva.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operador_logistico' => ['required', 'string', 'max:255'],
            'placa' => ['required', 'string', 'max:64'],
        ]);

        $operadorIn = trim((string) $validated['operador_logistico']);
        $placaNorm = DespachoParteProductoVehiculoConsulta::normalizarPlacaVehiculo(
            (string) $validated['placa'],
        );

        if ($operadorIn === '' || $placaNorm === '') {
            $msgs = [];
            if ($operadorIn === '') {
                $msgs['operador_logistico'] = 'Indique el operador logístico.';
            }
            if ($placaNorm === '') {
                $msgs['placa'] = 'Indique la placa del vehículo.';
            }
            throw ValidationException::withMessages($msgs);
        }

        $operadorExistente = DespachoOperadorPlaca::query()
            ->whereRaw('LOWER(TRIM(operador_logistico)) = ?', [mb_strtolower($operadorIn)])
            ->orderBy('id')
            ->value('operador_logistico');

        $operador = is_string($operadorExistente) && trim($operadorExistente) !== ''
            ? trim($operadorExistente)
            : $operadorIn;

        // Si la tabla está vacía/casi vacía, volcar primero el catálogo de config
        // para que un alta puntual no deje el filtro de alistamiento con 1 sola placa.
        if (DespachoOperadorPlaca::query()->count() < 10) {
            try {
                (new DespachoOperadorPlacaSeeder)->run();
            } catch (Throwable $e) {
                report($e);
            }
            $operadorExistente = DespachoOperadorPlaca::query()
                ->whereRaw('LOWER(TRIM(operador_logistico)) = ?', [mb_strtolower($operadorIn)])
                ->orderBy('id')
                ->value('operador_logistico');
            if (is_string($operadorExistente) && trim($operadorExistente) !== '') {
                $operador = trim($operadorExistente);
            }
        }

        $yaExiste = false;
        foreach (DespachoOperadorPlaca::filasParaDespacho() as $fila) {
            $mismaPlaca = DespachoParteProductoVehiculoConsulta::normalizarPlacaVehiculo(
                (string) ($fila['placa'] ?? ''),
            ) === $placaNorm;
            $mismoOp = mb_strtolower(trim((string) ($fila['operador'] ?? ''))) === mb_strtolower($operador);
            if ($mismaPlaca && $mismoOp) {
                $yaExiste = true;
                break;
            }
        }

        if ($yaExiste) {
            // Asegura la fila en BD aunque solo estuviera en config.
            DespachoOperadorPlaca::query()->firstOrCreate(
                [
                    'operador_logistico' => $operador,
                    'placa' => $placaNorm,
                ],
            );

            return response()->json([
                'ok' => false,
                'message' => 'Esa placa ya está registrada para ese operador logístico.',
                'filas' => DespachoOperadorPlaca::filasParaDespacho(),
            ], 422);
        }

        $row = DespachoOperadorPlaca::query()->create([
            'operador_logistico' => $operador,
            'placa' => $placaNorm,
        ]);

        return response()->json([
            'ok' => true,
            'message' => $operadorExistente
                ? 'Placa añadida al operador existente.'
                : 'Operador y placa añadidos al listado.',
            'fila' => [
                'operador' => (string) $row->operador_logistico,
                'placa' => (string) $row->placa,
            ],
            'filas' => DespachoOperadorPlaca::filasParaDespacho(),
            'operador_reutilizado' => (bool) $operadorExistente,
        ], 201);
    }
}

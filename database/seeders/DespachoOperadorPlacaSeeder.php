<?php

namespace Database\Seeders;

use App\Models\DespachoOperadorPlaca;
use Illuminate\Database\Seeder;

class DespachoOperadorPlacaSeeder extends Seeder
{
    /**
     * Carga el listado inicial desde config/despacho_operadores_placas.php.
     * Idempotente: actualiza updated_at si el par placa+operador ya existe.
     */
    public function run(): void
    {
        $filas = config('despacho_operadores_placas.filas', []);
        if ($filas === []) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($filas as $f) {
            $placa = trim((string) ($f['placa'] ?? ''));
            $op = trim((string) ($f['operador'] ?? ''));
            if ($placa === '' || $op === '') {
                continue;
            }
            $rows[] = [
                'placa' => $placa,
                'operador_logistico' => $op,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows === []) {
            return;
        }

        DespachoOperadorPlaca::query()->upsert(
            $rows,
            ['placa', 'operador_logistico'],
            ['updated_at'],
        );
    }
}

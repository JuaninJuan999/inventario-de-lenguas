<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DespachoOperadorPlaca extends Model
{
    protected $table = 'despacho_operador_placas';

    protected $fillable = [
        'placa',
        'operador_logistico',
    ];

    /**
     * Catálogo efectivo: filas de BD + las de config (por si la tabla aún no tiene el seeder
     * o solo se añadió una placa nueva y no debe “apagar” el listado COLBEEF).
     *
     * @return list<array{placa: string, operador: string}>
     */
    public static function filasParaDespacho(): array
    {
        $merged = [];

        foreach (config('despacho_operadores_placas.filas', []) as $fila) {
            if (! is_array($fila)) {
                continue;
            }
            $placa = trim((string) ($fila['placa'] ?? ''));
            $operador = trim((string) ($fila['operador'] ?? ''));
            if ($placa === '' || $operador === '') {
                continue;
            }
            $key = mb_strtolower($operador)."\0".mb_strtoupper(preg_replace('/\s+/u', '', $placa) ?? '');
            $merged[$key] = [
                'placa' => $placa,
                'operador' => $operador,
            ];
        }

        foreach (static::query()->orderBy('id')->get() as $r) {
            $placa = trim((string) $r->placa);
            $operador = trim((string) $r->operador_logistico);
            if ($placa === '' || $operador === '') {
                continue;
            }
            $key = mb_strtolower($operador)."\0".mb_strtoupper(preg_replace('/\s+/u', '', $placa) ?? '');
            $merged[$key] = [
                'placa' => $placa,
                'operador' => $operador,
            ];
        }

        $list = array_values($merged);
        usort($list, static function (array $a, array $b): int {
            $op = strcasecmp($a['operador'], $b['operador']);
            if ($op !== 0) {
                return $op;
            }

            return strcasecmp($a['placa'], $b['placa']);
        });

        return $list;
    }
}

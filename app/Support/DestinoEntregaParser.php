<?php

namespace App\Support;

/**
 * Interpreta el texto de destino (concat_ws desde SIRT) para manifiestos de entrega.
 *
 * Formato esperado: ciudad / empresa / nombre corto / dirección… (partes separadas por «/»).
 */
final class DestinoEntregaParser
{
    /**
     * @return array{codigo: string, direccion: string}
     */
    public static function destinoYDireccion(?string $destino): array
    {
        $destino = trim((string) $destino);
        if ($destino === '') {
            return ['codigo' => '—', 'direccion' => '—'];
        }

        $parts = preg_split('#\s*/\s*#u', $destino, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($parts)) {
            return ['codigo' => $destino, 'direccion' => ''];
        }

        $parts = array_values(array_map(static fn (string $s): string => trim($s), $parts));
        $n = count($parts);

        if ($n >= 4) {
            $codigo = $parts[2] !== '' ? $parts[2] : '—';
            $direccion = trim(implode(' / ', array_slice($parts, 3)));

            return ['codigo' => $codigo, 'direccion' => $direccion !== '' ? $direccion : '—'];
        }

        if ($n === 3) {
            return [
                'codigo' => $parts[2] !== '' ? $parts[2] : '—',
                'direccion' => '',
            ];
        }

        if ($n === 2) {
            return ['codigo' => $parts[0], 'direccion' => $parts[1]];
        }

        return ['codigo' => $parts[0] ?? '—', 'direccion' => ''];
    }
}

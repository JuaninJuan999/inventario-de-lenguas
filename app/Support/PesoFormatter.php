<?php

namespace App\Support;

/**
 * Formato de lectura para pesos: siempre un decimal (p. ej. 12.0, 3.5).
 */
class PesoFormatter
{
    public static function mostrar(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        if (! is_numeric($valor)) {
            return (string) $valor;
        }

        $n = (float) $valor;
        if (! is_finite($n)) {
            return '—';
        }

        return number_format($n, 1, '.', '');
    }
}

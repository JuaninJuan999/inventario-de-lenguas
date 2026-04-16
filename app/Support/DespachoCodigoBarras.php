<?php

namespace App\Support;

/**
 * Normaliza lecturas de código de barras al id_producto almacenado en inventario local.
 * Por defecto: si el código tiene al menos tres segmentos separados por "-" y el último
 * segmento son exactamente N dígitos (p. ej. 6000), se elimina ese sufijo.
 */
final class DespachoCodigoBarras
{
    public static function normalizarIdProducto(string $leido): string
    {
        $s = trim($leido);
        if ($s === '') {
            return '';
        }

        $digitos = (int) config('despacho.codigo_barras_suffix_digitos', 4);
        if ($digitos < 1) {
            return $s;
        }

        $parts = explode('-', $s);
        if (count($parts) < 3) {
            return $s;
        }

        $last = (string) end($parts);
        if (strlen($last) === $digitos && ctype_digit($last)) {
            array_pop($parts);

            return implode('-', $parts);
        }

        return $s;
    }
}

<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Formatea instantes almacenados (p. ej. en UTC) para la UI en la zona de operación del módulo.
 */
class FechaHoraLocalUi
{
    public static function timezoneOperacion(): string
    {
        return (string) config('ingresos_lenguas.fecha_operacion_timezone', 'America/Bogota');
    }

    public static function fechaHora(?CarbonInterface $value, string $pattern = 'Y-m-d H:i'): string
    {
        if ($value === null) {
            return '—';
        }

        return $value->copy()->timezone(self::timezoneOperacion())->format($pattern);
    }
}

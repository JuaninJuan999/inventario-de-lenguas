<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Vencimiento operativo: 4 días calendario después de la fecha de registro (zona de operación).
 */
class VencimientoLengua
{
    public static function zona(): string
    {
        return (string) config('ingresos_lenguas.fecha_operacion_timezone', 'America/Bogota');
    }

    public static function fechaVencimiento(?CarbonInterface $fechaRegistro): ?Carbon
    {
        if ($fechaRegistro === null) {
            return null;
        }

        $z = self::zona();
        $base = Carbon::parse($fechaRegistro->format('Y-m-d'), $z)->startOfDay();

        return $base->copy()->addDays(4);
    }

    /**
     * Días hasta la fecha de vencimiento (hoy en zona operación vs. vencimiento a medianoche local).
     * Positivo: faltan N días; cero: vence hoy; negativo: vencido hace N días.
     */
    public static function diasHastaVencimiento(?CarbonInterface $fechaRegistro): ?int
    {
        if ($fechaRegistro === null) {
            return null;
        }

        $v = self::fechaVencimiento($fechaRegistro);
        $hoy = Carbon::now(self::zona())->startOfDay();

        return (int) $hoy->diffInDays($v, false);
    }

    public static function fechaVencimientoTexto(?CarbonInterface $fechaRegistro, string $pattern = 'Y-m-d'): string
    {
        $v = self::fechaVencimiento($fechaRegistro);

        return $v === null ? '—' : $v->format($pattern);
    }

    public static function diasHastaVencimientoTexto(?CarbonInterface $fechaRegistro): string
    {
        $n = self::diasHastaVencimiento($fechaRegistro);
        if ($n === null) {
            return '—';
        }

        $palabra = abs($n) === 1 ? 'día' : 'días';

        return $n.' '.$palabra;
    }
}

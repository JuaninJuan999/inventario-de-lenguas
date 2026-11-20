<?php

namespace App\Console\Commands;

use App\Models\IngresoLenguaLocal;
use App\Support\VencimientoLengua;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Elimina del inventario disponible las lenguas con vencimiento más antiguo que el umbral.
 * Por defecto conserva vencidos en -1 y -2 días; borra los de -3 o menos.
 *
 * @see docs/inventario/depurar-vencidos.md
 */
class DepurarVencidosInventarioCommand extends Command
{
    protected $signature = 'inventario:depurar-vencidos
                            {--min-dias=-2 : Conservar vencidos hasta este valor inclusive (p. ej. -2 = deja -1 y -2)}
                            {--dry-run : Solo mostrar conteos, no borrar}
                            {--force : No pedir confirmación}';

    protected $description = 'Depura inventario: borra vencidos más antiguos que -1/-2 (docs/inventario/depurar-vencidos.md)';

    public function handle(): int
    {
        $minDias = (int) $this->option('min-dias');
        // Borrar si diasHastaVencimiento < minDias (con min=-2 → borra -3, -4, …).
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $hoyYmd = Carbon::now(VencimientoLengua::zona())->startOfDay()->toDateString();
        $diasExpr = $this->sqlDiasHastaVencimiento();

        $base = IngresoLenguaLocal::query()
            ->sinDespachar()
            ->whereNotNull('fecha_registro');

        $totalDisp = (clone $base)->count();
        $aBorrar = (clone $base)
            ->whereRaw("({$diasExpr}) < ?", [$hoyYmd, $minDias])
            ->count();
        $vencidosConservar = (clone $base)
            ->whereRaw("({$diasExpr}) < 0", [$hoyYmd])
            ->whereRaw("({$diasExpr}) >= ?", [$hoyYmd, $minDias])
            ->count();
        $noVencidos = (clone $base)
            ->whereRaw("({$diasExpr}) >= 0", [$hoyYmd])
            ->count();

        $this->info("Hoy operación: {$hoyYmd}");
        $this->line("Disponibles con fecha_registro: {$totalDisp}");
        $this->line("No vencidos (≥ 0 días): {$noVencidos}");
        $this->line("Vencidos a conservar ({$minDias} … -1): {$vencidosConservar}");
        $this->warn("A eliminar (días < {$minDias}): {$aBorrar}");

        if ($aBorrar === 0) {
            $this->info('Nada que depurar.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->comment('Dry-run: no se borró nada.');

            return self::SUCCESS;
        }

        if (! $force && ! $this->confirm("¿Eliminar {$aBorrar} registro(s) del inventario local?", false)) {
            $this->info('Cancelado.');

            return self::SUCCESS;
        }

        $eliminados = (clone $base)
            ->whereRaw("({$diasExpr}) < ?", [$hoyYmd, $minDias])
            ->delete();

        $quedan = (int) IngresoLenguaLocal::query()->sinDespachar()->count();
        $this->info("Eliminados: {$eliminados}. Inventario disponible restante: {$quedan}.");

        return self::SUCCESS;
    }

    private function sqlDiasHastaVencimiento(): string
    {
        return match (DB::getDriverName()) {
            'pgsql' => "((fecha_registro + INTERVAL '4 days')::date - CAST(? AS date))",
            'sqlite' => 'CAST(ROUND(julianday(date(fecha_registro, \'+4 days\')) - julianday(?)) AS INTEGER)',
            default => 'DATEDIFF(DATE_ADD(fecha_registro, INTERVAL 4 DAY), ?)',
        };
    }
}

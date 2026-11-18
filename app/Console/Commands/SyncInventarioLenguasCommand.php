<?php

namespace App\Console\Commands;

use App\Services\Inventario\InventarioLenguasSynchronizer;
use Illuminate\Console\Command;

/**
 * Importa/actualiza ingreso_lenguas_locales desde SIRT (día de operación o rango).
 *
 * Programado en routes/console.php cada INVENTARIO_LENGUAS_SCHEDULE_MINUTES.
 * Requiere despertador del schedule (cron schedule:run o schedule:work).
 *
 * @see docs/sincronizacion-inventario.md
 * @see \App\Services\Inventario\InventarioLenguasSynchronizer
 */
class SyncInventarioLenguasCommand extends Command
{
    protected $signature = 'inventario:sync-lenguas
                            {--desde= : Fecha turno desde (Y-m-d); por defecto día de operación}
                            {--hasta= : Fecha turno hasta (Y-m-d); por defecto igual a --desde o día de operación}';

    protected $description = 'Sincroniza la réplica local de inventario de lenguas desde SIRT/Colbeef (ver docs/sincronizacion-inventario.md)';

    public function handle(InventarioLenguasSynchronizer $synchronizer): int
    {
        $desde = $this->option('desde');
        $hasta = $this->option('hasta');

        $desde = is_string($desde) && trim($desde) !== '' ? trim($desde) : null;
        $hasta = is_string($hasta) && trim($hasta) !== '' ? trim($hasta) : null;

        if ($desde === null && $hasta === null) {
            // Día de operación (primeraCarga dentro del synchronizer).
            $result = $synchronizer->sync();
        } else {
            $result = $synchronizer->sync($desde, $hasta);
        }

        if (! $result['success']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $this->info($result['message']);
        if ($result['hit_limit']) {
            $this->warn('Se alcanzó el tope de consulta SIRT ('.$result['limite'].').');
        }

        return self::SUCCESS;
    }
}

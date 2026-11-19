<?php

namespace App\Console\Commands;

use App\Services\Inventario\InventarioLenguasSynchronizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vacía datos operativos locales y deja la réplica alineada desde ayer (o --desde).
 *
 * No toca SIRT, usuarios, módulos ni role_modulo.
 *
 * @see docs/sincronizacion-inventario.md
 */
class LimpiarOperativoInventarioCommand extends Command
{
    protected $signature = 'inventario:limpiar-operativo
                            {--desde= : Fecha turno desde (Y-m-d); por defecto ayer (día operación − sync_dias_atras)}
                            {--hasta= : Fecha turno hasta (Y-m-d); por defecto día de operación}
                            {--solo-inventario : No borrar despachos/despostes/decomisos/placas/accesos}
                            {--force : No pedir confirmación}';

    protected $description = 'Borra datos operativos locales y reimporta inventario desde ayer (docs/sincronizacion-inventario.md)';

    public function handle(InventarioLenguasSynchronizer $synchronizer): int
    {
        $soloInventario = (bool) $this->option('solo-inventario');
        $force = (bool) $this->option('force');

        $this->warn('Esto borra datos LOCALES (réplica y, salvo --solo-inventario, módulos operativos).');
        $this->line('No modifica SIRT ni usuarios/roles/módulos de configuración.');

        if (! $force && ! $this->confirm('¿Continuar?', false)) {
            $this->info('Cancelado.');

            return self::SUCCESS;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        $deleted = [];

        if (Schema::hasTable('ingreso_lenguas_locales')) {
            if (Schema::hasColumn('ingreso_lenguas_locales', 'despacho_id')) {
                DB::table('ingreso_lenguas_locales')->update(['despacho_id' => null]);
            }
            if (Schema::hasColumn('ingreso_lenguas_locales', 'desposte_id')) {
                DB::table('ingreso_lenguas_locales')->update(['desposte_id' => null]);
            }
            if (Schema::hasColumn('ingreso_lenguas_locales', 'despachado_at')) {
                DB::table('ingreso_lenguas_locales')->update(['despachado_at' => null]);
            }
        }

        if (! $soloInventario) {
            $deleted['despacho_operador_placas'] = $this->vaciarTabla('despacho_operador_placas');
            $deleted['despachos'] = $this->vaciarTabla('despachos');
            $deleted['despostes'] = $this->vaciarTabla('despostes');
            $deleted['decomisos_manuales'] = $this->vaciarTabla('decomisos_manuales');
            $deleted['modulo_accesos'] = $this->vaciarTabla('modulo_accesos');
        }

        $deleted['ingreso_lenguas_locales'] = $this->vaciarTabla('ingreso_lenguas_locales');

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        Cache::forget('inventario_lenguas_sync_meta');
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            $this->warn('Aviso al limpiar caché: '.$e->getMessage());
        }

        foreach ($deleted as $tabla => $n) {
            $this->line("  · {$tabla}: {$n} fila(s) eliminadas");
        }

        $desde = $this->option('desde');
        $hasta = $this->option('hasta');
        $desde = is_string($desde) && trim($desde) !== '' ? trim($desde) : null;
        $hasta = is_string($hasta) && trim($hasta) !== '' ? trim($hasta) : null;

        $this->info('Reimportando réplica (ventana por defecto: desde ayer → hoy)…');
        $result = $synchronizer->sync($desde, $hasta);

        if (! $result['success']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $this->info($result['message']);
        $this->line('Importados/actualizados: '.(int) ($result['importados'] ?? 0));
        if (! empty($result['hit_limit'])) {
            $this->warn('Se alcanzó el tope de consulta SIRT ('.($result['limite'] ?? '?').').');
        }

        $restantes = Schema::hasTable('ingreso_lenguas_locales')
            ? (int) DB::table('ingreso_lenguas_locales')->count()
            : 0;
        $this->info("Inventario local actual: {$restantes} registro(s).");

        return self::SUCCESS;
    }

    private function vaciarTabla(string $tabla): int
    {
        if (! Schema::hasTable($tabla)) {
            return 0;
        }

        return (int) DB::table($tabla)->delete();
    }
}

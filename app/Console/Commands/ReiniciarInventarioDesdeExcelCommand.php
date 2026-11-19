<?php

namespace App\Console\Commands;

use App\Models\IngresoLenguaLocal;
use App\Services\IngresosLenguas\IngresosLenguasConsultaSirt;
use App\Services\Inventario\InventarioBaseline;
use App\Services\Inventario\InventarioLenguasSynchronizer;
use App\Support\DespachoCodigoBarras;
use App\Support\DespachoInventarioMatch;
use App\Support\SimpleXlsxReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vacía la réplica local y deja solo las lenguas cuyos códigos están en un Excel de baseline.
 *
 * @see docs/inventario/reiniciar-desde-excel.md
 */
class ReiniciarInventarioDesdeExcelCommand extends Command
{
    protected $signature = 'inventario:reiniciar-desde-excel
                            {archivo : Ruta al .xlsx (columna Codigo) o .txt/.csv (un código por línea)}
                            {--desde=2026-10-01 : Fecha turno desde para reimportar desde SIRT}
                            {--hasta= : Fecha turno hasta (default: día de operación)}
                            {--fecha-corte= : Desde qué REF. TURNO se alimenta lo nuevo (default: --hasta / hoy)}
                            {--columna=Codigo : Nombre del encabezado de códigos en el Excel}
                            {--solo-inventario : No borrar despachos/despostes/decomisos/placas/accesos}
                            {--sin-sync : Solo vaciar y no consultar SIRT}
                            {--force : No pedir confirmación}';

    protected $description = 'Borra la réplica local y siembra inventario solo con códigos de un Excel/lista (docs/inventario/reiniciar-desde-excel.md)';

    public function handle(
        InventarioLenguasSynchronizer $synchronizer,
        IngresosLenguasConsultaSirt $consultaSirt,
        InventarioBaseline $baseline,
    ): int {
        $archivoOpt = (string) $this->argument('archivo');
        $path = $this->resolverRuta($archivoOpt);
        if ($path === null) {
            $this->error('No se encontró el archivo: '.$archivoOpt);

            return self::FAILURE;
        }

        try {
            $codigosExcel = $this->leerCodigos($path, (string) $this->option('columna'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($codigosExcel === []) {
            $this->error('El archivo no tiene códigos.');

            return self::FAILURE;
        }

        $allow = $this->construirAllowlist($codigosExcel);
        $unicosNorm = array_values(array_unique(array_filter(array_map(
            static fn (string $c): string => DespachoCodigoBarras::normalizarIdProducto(
                DespachoInventarioMatch::normalizarGuionesIdProducto($c)
            ),
            $codigosExcel
        ))));
        $this->info('Códigos en archivo: '.count($codigosExcel).' · únicos normalizados: '.count($unicosNorm).' · claves: '.count($allow));

        $soloInventario = (bool) $this->option('solo-inventario');
        $sinSync = (bool) $this->option('sin-sync');
        $force = (bool) $this->option('force');

        $this->warn('Se borrarán datos LOCALES y el inventario quedará limitado a los códigos del archivo.');
        $this->line('No modifica SIRT ni usuarios/roles.');

        if (! $force && ! $this->confirm('¿Continuar?', false)) {
            $this->info('Cancelado.');

            return self::SUCCESS;
        }

        $this->vaciarOperativo($soloInventario);

        $desde = trim((string) ($this->option('desde') ?: '2026-10-01'));
        $hasta = trim((string) ($this->option('hasta') ?: ''));
        // Si solo se pasa --desde, InventarioLenguasSynchronizer lo toma como día único: forzar hasta=hoy.
        if ($hasta === '') {
            $hasta = $consultaSirt->fechaHoyOperacion();
        }

        $fechaCorte = trim((string) ($this->option('fecha-corte') ?: ''));
        if ($fechaCorte === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorte) !== 1) {
            $fechaCorte = $hasta;
        }

        // Persistir baseline ANTES de la sync: Excel + altas con REF. TURNO >= fecha_corte.
        $baseline->save($unicosNorm, $fechaCorte, $path);
        $this->info('Baseline guardado: '.InventarioBaseline::STORAGE_PATH.' · fecha_corte='.$fechaCorte.' · códigos='.count($unicosNorm));

        if (! $sinSync) {
            $this->info("Sincronizando desde SIRT ({$desde} → {$hasta}) por tramos…");
            $tramos = $this->tramosMensuales($desde, $hasta);
            $importadosTotal = 0;
            foreach ($tramos as [$d, $h]) {
                $this->line("  · tramo {$d} → {$h}");
                $result = $synchronizer->sync($d, $h, [], null, true);
                if (! $result['success']) {
                    $this->error($result['message']);

                    return self::FAILURE;
                }
                $importadosTotal += (int) ($result['importados'] ?? 0);
                if (! empty($result['hit_limit'])) {
                    $this->warn("    tope SIRT en tramo ({$result['limite']}). Suba INGRESOS_LENGUAS_CONSULTA_LIMIT si faltan códigos.");
                }
            }
            $this->line("Importados acumulados en tramos: {$importadosTotal}");
        } else {
            $this->warn('Omitida sync SIRT (--sin-sync).');
        }

        $antes = (int) IngresoLenguaLocal::query()->count();
        $eliminados = $this->purgarFueraDeAllowlist($allow);
        $despues = (int) IngresoLenguaLocal::query()->count();
        $encontrados = $this->codigosCubiertos($allow);
        $faltantes = array_values(array_diff($unicosNorm, $encontrados));

        $this->info("Réplica tras sync: {$antes} · eliminados fuera de lista: {$eliminados} · quedan: {$despues}");
        $this->info('Códigos del Excel cubiertos: '.count($encontrados).' / '.count($unicosNorm));

        if ($faltantes !== [] && ! $sinSync) {
            // Sin tope artificial: hay que cubrir toda la lista del Excel (puede tardar).
            $maxRecup = count($faltantes);
            $this->warn('Faltan '.count($faltantes).'. Recuperación individual ('.$maxRecup.')…');
            $bar = $this->output->createProgressBar($maxRecup);
            $bar->start();
            $recuperados = 0;
            foreach ($faltantes as $codigo) {
                $r = $synchronizer->sync($desde, $hasta, ['id_producto' => $codigo], null, false);
                if ($r['success'] && (int) ($r['importados'] ?? 0) > 0) {
                    $recuperados++;
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->purgarFueraDeAllowlist($allow);
            $encontrados = $this->codigosCubiertos($allow);
            $despues = (int) IngresoLenguaLocal::query()->count();
            $this->line("Recuperación con importaciones: {$recuperados}");
            $this->info('Cubiertos tras recuperación: '.count($encontrados).' / '.count($unicosNorm));
            $this->info("Inventario local final: {$despues} registro(s).");
        }

        $this->line('Listo. Baseline activo: sync añade códigos del Excel o con REF. TURNO >= '.$fechaCorte.'.');

        return self::SUCCESS;
    }

    private function resolverRuta(string $archivo): ?string
    {
        foreach ([$archivo, base_path($archivo), storage_path('app/'.$archivo)] as $c) {
            if (is_file($c)) {
                return realpath($c) ?: $c;
            }
        }

        return null;
    }

    /** @return list<string> */
    private function leerCodigos(string $path, string $columna): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['xlsx', 'xlsm'], true)) {
            return SimpleXlsxReader::columnByHeader($path, $columna);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
        $out = [];
        foreach ($lines as $i => $line) {
            $line = trim((string) $line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if ($i === 0 && preg_match('/^codigo$/i', $line) === 1) {
                continue;
            }
            if (str_contains($line, ',')) {
                $line = trim((string) str_getcsv($line)[0]);
            }
            if ($line !== '') {
                $out[] = $line;
            }
        }

        return $out;
    }

    /**
     * @param  list<string>  $codigos
     * @return array<string, true>
     */
    private function construirAllowlist(array $codigos): array
    {
        $allow = [];
        foreach ($codigos as $codigo) {
            foreach (DespachoInventarioMatch::matchKeysForProductId($codigo) as $key) {
                $allow[$key] = true;
                $allow[mb_strtolower($key)] = true;
            }
        }

        return $allow;
    }

    /** @param  array<string, true>  $allow */
    private function purgarFueraDeAllowlist(array $allow): int
    {
        $idsBorrar = [];
        IngresoLenguaLocal::query()
            ->orderBy('id')
            ->select(['id', 'id_producto'])
            ->chunkById(500, function ($rows) use ($allow, &$idsBorrar): void {
                foreach ($rows as $row) {
                    $keys = DespachoInventarioMatch::matchKeysForProductId((string) $row->id_producto);
                    $ok = false;
                    foreach ($keys as $key) {
                        if (isset($allow[$key]) || isset($allow[mb_strtolower($key)])) {
                            $ok = true;
                            break;
                        }
                    }
                    if (! $ok) {
                        $idsBorrar[] = (int) $row->id;
                    }
                }
            });

        if ($idsBorrar === []) {
            return 0;
        }

        foreach (array_chunk($idsBorrar, 500) as $chunk) {
            IngresoLenguaLocal::query()->whereIn('id', $chunk)->delete();
        }

        return count($idsBorrar);
    }

    /**
     * @param  array<string, true>  $allow
     * @return list<string>
     */
    private function codigosCubiertos(array $allow): array
    {
        $presentes = [];
        IngresoLenguaLocal::query()
            ->select(['id', 'id_producto'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($allow, &$presentes): void {
                foreach ($rows as $row) {
                    $keys = DespachoInventarioMatch::matchKeysForProductId((string) $row->id_producto);
                    foreach ($keys as $key) {
                        if (isset($allow[$key]) || isset($allow[mb_strtolower($key)])) {
                            $norm = DespachoCodigoBarras::normalizarIdProducto(
                                DespachoInventarioMatch::normalizarGuionesIdProducto((string) $row->id_producto)
                            );
                            if ($norm !== '') {
                                $presentes[$norm] = true;
                            }
                            break;
                        }
                    }
                }
            });

        return array_keys($presentes);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function tramosMensuales(string $desde, string $hasta): array
    {
        try {
            $start = \Carbon\Carbon::parse($desde)->startOfDay();
            $end = \Carbon\Carbon::parse($hasta)->startOfDay();
        } catch (\Throwable) {
            return [[$desde, $hasta]];
        }

        if ($start->gt($end)) {
            return [[$hasta, $desde]];
        }

        $tramos = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $chunkEnd = $cursor->copy()->endOfMonth()->startOfDay();
            if ($chunkEnd->gt($end)) {
                $chunkEnd = $end->copy();
            }
            $tramos[] = [$cursor->toDateString(), $chunkEnd->toDateString()];
            $cursor = $chunkEnd->copy()->addDay();
        }

        return $tramos !== [] ? $tramos : [[$desde, $hasta]];
    }

    private function vaciarOperativo(bool $soloInventario): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

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
            foreach (['despacho_operador_placas', 'despachos', 'despostes', 'decomisos_manuales', 'modulo_accesos'] as $tabla) {
                if (Schema::hasTable($tabla)) {
                    DB::table($tabla)->delete();
                }
            }
        }

        if (Schema::hasTable('ingreso_lenguas_locales')) {
            DB::table('ingreso_lenguas_locales')->delete();
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        Cache::forget('inventario_lenguas_sync_meta');
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            $this->warn('Aviso caché: '.$e->getMessage());
        }

        $this->info('Datos operativos locales vaciados.');
    }
}

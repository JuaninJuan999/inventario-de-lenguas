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

/**
 * Añade a la réplica local los códigos de un Excel que aún no existan (sin borrar nada).
 * Actualiza el baseline para que la sync no los rechace.
 *
 * @see docs/inventario/importar-excel-adicionales.md
 */
class ImportarExcelInventarioCommand extends Command
{
    protected $signature = 'inventario:importar-excel
                            {archivo : Ruta al .xlsx (columna Codigo) o .txt/.csv}
                            {--desde=2026-10-01 : Fecha turno desde para consultar SIRT}
                            {--hasta= : Fecha turno hasta (default: día de operación)}
                            {--columna=Codigo : Encabezado de códigos en el Excel}
                            {--dry-run : Solo listar faltantes, no importar}
                            {--force : Sin confirmación}';

    protected $description = 'Añade códigos de un Excel al inventario local sin duplicar ni borrar (docs/inventario/importar-excel-adicionales.md)';

    public function handle(
        InventarioBaseline $baseline,
        InventarioLenguasSynchronizer $synchronizer,
        IngresosLenguasConsultaSirt $consultaSirt,
    ): int {
        $path = $this->resolverRuta((string) $this->argument('archivo'));
        if ($path === null) {
            $this->error('No se encontró el archivo: '.$this->argument('archivo'));

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

        $unicos = [];
        foreach ($codigosExcel as $c) {
            $raw = trim((string) $c);
            $norm = DespachoCodigoBarras::normalizarIdProducto(
                DespachoInventarioMatch::normalizarGuionesIdProducto($raw)
            );
            if ($norm !== '') {
                $unicos[$norm] = $raw !== '' ? $raw : $norm;
            }
        }

        $presentes = $this->codigosPresentesLocal();
        $faltantes = [];
        foreach ($unicos as $norm => $raw) {
            if (! isset($presentes[$norm])) {
                $faltantes[$norm] = $raw;
            }
        }

        $this->info('Excel: '.count($codigosExcel).' filas · únicos: '.count($unicos));
        $this->info('Ya en inventario: '.(count($unicos) - count($faltantes)).' · a importar: '.count($faltantes));

        if ($faltantes === []) {
            $this->info('Nada que agregar (sin duplicados pendientes).');

            return self::SUCCESS;
        }

        if ((bool) $this->option('dry-run')) {
            $this->comment('Dry-run. Ejemplos faltantes: '.implode(', ', array_slice(array_values($faltantes), 0, 12)));

            return self::SUCCESS;
        }

        if (! (bool) $this->option('force') && ! $this->confirm('¿Importar '.count($faltantes).' código(s) desde SIRT?', false)) {
            $this->info('Cancelado.');

            return self::SUCCESS;
        }

        $desde = trim((string) ($this->option('desde') ?: '2026-10-01'));
        $hasta = trim((string) ($this->option('hasta') ?: ''));
        if ($hasta === '') {
            $hasta = $consultaSirt->fechaHoyOperacion();
        }

        // Ampliar baseline para que la sync no rechace estos códigos.
        $corte = $baseline->fechaCorte() ?: $hasta;
        $merged = array_values(array_unique(array_merge(
            array_map('strval', $baseline->data()['codigos_norm'] ?? []),
            array_keys($unicos)
        )));
        $baseline->save($merged, $corte, $path);
        $this->line('Baseline actualizado: '.count($merged).' códigos · fecha_corte='.$corte);

        $ok = 0;
        $fail = 0;
        $bar = $this->output->createProgressBar(count($faltantes));
        $bar->start();
        $quedan = [];

        foreach ($faltantes as $norm => $raw) {
            $imported = false;
            foreach (array_unique([$raw, $norm, $norm.'-6000']) as $candidato) {
                $r = $synchronizer->sync($desde, $hasta, ['id_producto' => $candidato], null, false);
                if (($r['success'] ?? false) && (int) ($r['importados'] ?? 0) > 0) {
                    $imported = true;
                    break;
                }
                if (! ($r['success'] ?? false)) {
                    $this->newLine();
                    $this->error($r['message'] ?? 'Error SIRT');

                    return self::FAILURE;
                }
            }
            if ($imported) {
                $ok++;
            } else {
                $fail++;
                $quedan[] = $raw;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        if ($quedan !== []) {
            $this->warn('Reintento con rango amplio (2026-01-01 → '.$hasta.') para '.count($quedan).'…');
            $bar2 = $this->output->createProgressBar(count($quedan));
            $bar2->start();
            $left = [];
            foreach ($quedan as $raw) {
                $norm = DespachoCodigoBarras::normalizarIdProducto(
                    DespachoInventarioMatch::normalizarGuionesIdProducto($raw)
                );
                $imported = false;
                foreach (array_unique([$raw, $norm, $norm.'-6000']) as $candidato) {
                    $r = $synchronizer->sync('2026-01-01', $hasta, ['id_producto' => $candidato], null, false);
                    if (($r['success'] ?? false) && (int) ($r['importados'] ?? 0) > 0) {
                        $imported = true;
                        break;
                    }
                }
                if ($imported) {
                    $ok++;
                    $fail--;
                } else {
                    $left[] = $raw;
                }
                $bar2->advance();
            }
            $bar2->finish();
            $this->newLine();
            $quedan = $left;
        }

        $disp = (int) IngresoLenguaLocal::query()->sinDespachar()->count();
        $this->info("Importados desde SIRT: {$ok}. Sin match: ".count($quedan).". Disponibles ahora: {$disp}.");
        if ($quedan !== []) {
            $this->warn('Ejemplos sin SIRT: '.implode(', ', array_slice($quedan, 0, 15)));
        }

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

    /** @return array<string, true> */
    private function codigosPresentesLocal(): array
    {
        $presentes = [];
        IngresoLenguaLocal::query()
            ->select(['id', 'id_producto'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$presentes): void {
                foreach ($rows as $row) {
                    $norm = DespachoCodigoBarras::normalizarIdProducto(
                        DespachoInventarioMatch::normalizarGuionesIdProducto((string) $row->id_producto)
                    );
                    if ($norm !== '') {
                        $presentes[$norm] = true;
                    }
                }
            });

        return $presentes;
    }
}

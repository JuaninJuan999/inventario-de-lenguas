<?php

namespace App\Console\Commands;

use App\Models\IngresoLenguaLocal;
use App\Services\IngresosLenguas\IngresosLenguasConsultaSirt;
use App\Services\Inventario\InventarioBaseline;
use App\Services\Inventario\InventarioLenguasSynchronizer;
use App\Support\DespachoCodigoBarras;
use App\Support\DespachoInventarioMatch;
use Illuminate\Console\Command;

/**
 * Completa códigos del baseline que faltan en la réplica (sin vaciar de nuevo).
 */
class CompletarBaselineExcelCommand extends Command
{
    protected $signature = 'inventario:completar-baseline
                            {--desde=2026-10-01 : Fecha turno desde}
                            {--hasta= : Fecha turno hasta (default: día de operación)}';

    protected $description = 'Recupera desde SIRT los códigos del baseline Excel que aún no están en inventario local';

    public function handle(
        InventarioBaseline $baseline,
        InventarioLenguasSynchronizer $synchronizer,
        IngresosLenguasConsultaSirt $consultaSirt,
    ): int {
        if (! $baseline->active()) {
            $this->error('No hay baseline activo (storage/app/inventario/baseline.json). Ejecute inventario:reiniciar-desde-excel primero.');

            return self::FAILURE;
        }

        $data = $baseline->data();
        $unicosNorm = array_values(array_unique(array_filter(array_map(
            'strval',
            $data['codigos_norm'] ?? []
        ))));

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

        $faltantes = array_values(array_filter(
            $unicosNorm,
            static fn (string $c): bool => ! isset($presentes[$c])
        ));

        $this->info('Baseline: '.count($unicosNorm).' · en local: '.count($presentes).' · faltan: '.count($faltantes));

        if ($faltantes === []) {
            $this->info('Nada que recuperar.');

            return self::SUCCESS;
        }

        $desde = trim((string) ($this->option('desde') ?: '2026-10-01'));
        $hasta = trim((string) ($this->option('hasta') ?: ''));
        if ($hasta === '') {
            $hasta = $consultaSirt->fechaHoyOperacion();
        }

        $bar = $this->output->createProgressBar(count($faltantes));
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

        $final = (int) IngresoLenguaLocal::query()->count();
        $this->info("Recuperados con importación: {$recuperados}");
        $this->info("Inventario local final: {$final} registro(s).");

        return self::SUCCESS;
    }
}

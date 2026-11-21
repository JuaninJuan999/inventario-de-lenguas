<?php

namespace App\Services\Despacho;

use App\Models\DespachoAlistamientoCompromiso;
use App\Models\DespachoAlistamientoCompromisoItem;
use App\Support\DespachoInventarioMatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Congela placas/códigos asignados (Colbeef ∩ inventario) y arma el backlog «Comprometidas / por salir».
 */
final class DespachoAlistamientoCompromisoRegistrar
{
    /** Días hacia atrás para recuperar/mostrar compromisos con stock aún disponible. */
    public const VENTANA_DIAS = 7;

    /**
     * Upsert snapshot: une códigos asignados a la placa mientras sigan existiendo en inventario.
     *
     * @param  list<object{
     *     id_vehiculo_asignado: int,
     *     placa_vehiculo: string|null,
     *     id_productos?: list<string>
     * }>  $placas
     */
    public function registrarDesdePlacasVivas(string $diaOperacion, array $placas): void
    {
        if ($placas === []) {
            return;
        }

        $ahora = Carbon::now();

        DB::transaction(function () use ($diaOperacion, $placas, $ahora): void {
            foreach ($placas as $placa) {
                $vid = (int) ($placa->id_vehiculo_asignado ?? 0);
                if ($vid < 1) {
                    continue;
                }

                $productos = [];
                foreach ($placa->id_productos ?? [] as $idProducto) {
                    $idProducto = trim((string) $idProducto);
                    if ($idProducto !== '') {
                        $productos[$idProducto] = true;
                    }
                }
                if ($productos === []) {
                    continue;
                }

                $placaTxt = isset($placa->placa_vehiculo) ? trim((string) $placa->placa_vehiculo) : '';
                $placaTxt = $placaTxt !== '' ? $placaTxt : null;

                // Un compromiso abierto por vehículo: reutiliza el más reciente de la ventana
                // o crea uno en el día operativo actual.
                $compromiso = DespachoAlistamientoCompromiso::query()
                    ->where('id_vehiculo_asignado', $vid)
                    ->whereDate('dia_operacion', '>=', Carbon::parse($diaOperacion)->subDays(self::VENTANA_DIAS)->toDateString())
                    ->orderByDesc('dia_operacion')
                    ->orderByDesc('id')
                    ->first();

                if ($compromiso === null) {
                    $compromiso = new DespachoAlistamientoCompromiso([
                        'dia_operacion' => $diaOperacion,
                        'id_vehiculo_asignado' => $vid,
                        'first_seen_at' => $ahora,
                    ]);
                }

                $compromiso->placa = $placaTxt ?? $compromiso->placa;
                $compromiso->last_seen_at = $ahora;
                if (! $compromiso->exists) {
                    $compromiso->dia_operacion = $diaOperacion;
                    $compromiso->first_seen_at = $ahora;
                }
                $compromiso->save();

                $existentes = $compromiso->items()
                    ->pluck('id_producto')
                    ->map(static fn ($v): string => trim((string) $v))
                    ->filter(static fn (string $v): bool => $v !== '')
                    ->all();
                $existentesSet = array_fill_keys($existentes, true);

                foreach (array_keys($productos) as $idProducto) {
                    if (isset($existentesSet[$idProducto])) {
                        continue;
                    }
                    DespachoAlistamientoCompromisoItem::query()->create([
                        'compromiso_id' => $compromiso->id,
                        'id_producto' => $idProducto,
                    ]);
                    $existentesSet[$idProducto] = true;
                }
            }
        });
    }

    /**
     * Placas con códigos asignados que aún están en inventario y ya no salen en «Colbeef hoy».
     * Compara snapshot (asignados) vs inventario sin despachar; abarca varios días.
     *
     * @param  list<object{id_vehiculo_asignado: int}>  $placasVivasHoy
     * @return list<object{
     *     id_vehiculo_asignado: int,
     *     placa_vehiculo: string|null,
     *     pendientes: int,
     *     checklist_disponible: bool,
     *     dia_operacion: string|null
     * }>
     */
    public function backlogComprometidas(string $diaOperacion, array $placasVivasHoy): array
    {
        $vivosIds = [];
        foreach ($placasVivasHoy as $p) {
            $vid = (int) ($p->id_vehiculo_asignado ?? 0);
            if ($vid > 0) {
                $vivosIds[$vid] = true;
            }
        }

        $desde = Carbon::parse($diaOperacion)->subDays(self::VENTANA_DIAS)->toDateString();

        $compromisos = DespachoAlistamientoCompromiso::query()
            ->with('items')
            ->whereDate('dia_operacion', '>=', $desde)
            ->whereDate('dia_operacion', '<=', $diaOperacion)
            ->orderByDesc('last_seen_at')
            ->orderBy('placa')
            ->orderBy('id')
            ->get();

        if ($compromisos->isEmpty()) {
            return [];
        }

        $mapaInv = DespachoInventarioMatch::mapaClavesDisponibles();
        if ($mapaInv === []) {
            return [];
        }

        /** @var array<int, object> $porVehiculo */
        $porVehiculo = [];

        foreach ($compromisos as $compromiso) {
            $vid = (int) $compromiso->id_vehiculo_asignado;
            if ($vid < 1 || isset($vivosIds[$vid])) {
                continue;
            }

            if (! isset($porVehiculo[$vid])) {
                $porVehiculo[$vid] = (object) [
                    'id_vehiculo_asignado' => $vid,
                    'placa_vehiculo' => $compromiso->placa,
                    'pendientes' => 0,
                    'checklist_disponible' => false,
                    'dia_operacion' => $compromiso->dia_operacion?->format('Y-m-d'),
                    '_codigos' => [],
                ];
            }

            if ($porVehiculo[$vid]->placa_vehiculo === null && $compromiso->placa) {
                $porVehiculo[$vid]->placa_vehiculo = $compromiso->placa;
            }

            foreach ($compromiso->items as $item) {
                $codigo = trim((string) $item->id_producto);
                if ($codigo === '' || isset($porVehiculo[$vid]->_codigos[$codigo])) {
                    continue;
                }
                $porVehiculo[$vid]->_codigos[$codigo] = true;
                if (DespachoInventarioMatch::codigoEnMapaDisponibles($codigo, $mapaInv)) {
                    $porVehiculo[$vid]->pendientes++;
                }
            }
        }

        $lista = [];
        foreach ($porVehiculo as $row) {
            unset($row->_codigos);
            if ((int) $row->pendientes < 1) {
                continue;
            }
            $lista[] = $row;
        }

        usort($lista, static function (object $a, object $b): int {
            $pa = strtolower(trim((string) ($a->placa_vehiculo ?? '')));
            $pb = strtolower(trim((string) ($b->placa_vehiculo ?? '')));

            return $pa <=> $pb;
        });

        return $lista;
    }
}

<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Despacho\Concerns\ResuelveUsuarioVehiculoDespacho;
use App\Models\DespachoOperadorPlaca;
use App\Services\Despacho\DespachoParteProductoVehiculoConsulta;
use App\Support\DespachoInventarioMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DespachoLenguasController extends Controller
{
    use ResuelveUsuarioVehiculoDespacho;

    /** Modo manual (sin checklist Colbeef). */
    public function __invoke(Request $request): View
    {
        return view('despacho-lenguas', $this->buildBaseViewData(null));
    }

    /**
     * Checklist físico vs Colbeef para un vehículo asignado (parte_producto_vehiculo).
     */
    public function checklist(Request $request, int $id_vehiculo_asignado): RedirectResponse|View
    {
        $username = $this->usernameVehiculoEfectivo($request);

        try {
            $consulta = app(DespachoParteProductoVehiculoConsulta::class);
            $bundle = $consulta->pendientesPorVehiculo($id_vehiculo_asignado, $username, true);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('despacho.lenguas')
                ->with(
                    'despacho_flash_error',
                    config('app.debug')
                        ? 'No se pudo cargar el checklist: '.$e->getMessage()
                        : 'No se pudo cargar el checklist corporativo. Revise la conexión a Colbeef.'
                );
        }

        $vehiculo = $bundle['vehiculo'];
        $lineas = $bundle['lineas'];

        if ($vehiculo === null || $lineas === []) {
            return redirect()
                ->route('despacho.lenguas')
                ->with(
                    'despacho_flash_error',
                    'No hay códigos de este vehículo con fila disponible en inventario local: el checklist solo lista lo que aún se puede despachar. Revise importación a inventario, fecha en Colbeef y filtros.'
                );
        }

        $checklistLines = [];
        foreach ($lineas as $line) {
            $resumen = DespachoInventarioMatch::resumenParaChecklist($line->id_producto);
            $checklistLines[] = [
                'ppv_id' => $line->ppv_id,
                'id_producto_colbeef' => $line->id_producto,
                'match_keys' => $resumen['match_keys'],
                'canonical_id_producto' => $resumen['canonical_id_producto'],
                'en_inventario' => $resumen['en_inventario'],
                'propietario' => $resumen['propietario'],
                'destino' => $resumen['destino'],
            ];
        }

        $base = $this->buildBaseViewData((int) $vehiculo->id_vehiculo_asignado);

        $checklistTotalConInv = count(array_filter($checklistLines, static fn (array $l): bool => $l['en_inventario']));
        $checklistSinInv = count(array_filter($checklistLines, static fn (array $l): bool => ! $l['en_inventario']));

        return view('despacho-lenguas', array_merge($base, [
            'checklistMode' => true,
            'vehiculoEmpresaChecklist' => '',
            'vehiculoPlacaChecklist' => '',
            'vehiculoConductorChecklist' => '',
            'checklistLines' => $checklistLines,
            'checklistTotalConInventario' => $checklistTotalConInv,
            'checklistSinInventarioCount' => $checklistSinInv,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBaseViewData(?int $idVehiculoAsignadoChecklist): array
    {
        $filas = DespachoOperadorPlaca::filasParaDespacho();
        if ($filas === []) {
            $filas = config('despacho_operadores_placas.filas', []);
        }

        return [
            'operadorPlacaFilas' => $filas,
            'checklistMode' => false,
            'checklistVehicleId' => $idVehiculoAsignadoChecklist,
            'vehiculoEmpresaChecklist' => '',
            'vehiculoPlacaChecklist' => '',
            'vehiculoConductorChecklist' => '',
            'checklistLines' => [],
            'checklistTotalConInventario' => 0,
            'checklistSinInventarioCount' => 0,
        ];
    }
}

<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Despacho\Concerns\ResuelveUsuarioVehiculoDespacho;
use App\Models\Despacho;
use App\Models\DespachoOperadorPlaca;
use App\Services\Despacho\DespachoParteProductoVehiculoConsulta;
use App\Support\DespachoInventarioMatch;
use Carbon\Carbon;
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
            $tz = (string) config('app.timezone', 'America/Bogota');
            $diaOp = app(DespachoParteProductoVehiculoConsulta::class)->fechaRegistroReferenciaHoy();
            $yaCerrado = Despacho::query()
                ->where('id_vehiculo_asignado', $id_vehiculo_asignado)
                ->where('realizado_at', '>=', Carbon::parse($diaOp, $tz)->startOfDay())
                ->exists();

            $msg = $yaCerrado
                ? 'Ese vehículo ya tiene un despacho cerrado en este turno. Si aún aparece en la lista, recargue la página de placas.'
                : 'No hay códigos de este vehículo con fila disponible en inventario local: el checklist solo lista lo que aún se puede despachar. Revise importación a inventario, fecha en Colbeef y filtros.';

            return redirect()
                ->route('despacho.lenguas')
                ->with('despacho_flash_error', $msg);
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

        $placaPrefill = trim((string) ($vehiculo->placa_vehiculo ?? ''));
        $empresaPrefill = $this->resolverOperadorPorPlaca(
            $placaPrefill,
            trim((string) ($vehiculo->empresa ?? '')),
            $base['operadorPlacaFilas'] ?? [],
        );

        $checklistTotalConInv = count(array_filter($checklistLines, static fn (array $l): bool => $l['en_inventario']));
        $checklistSinInv = count(array_filter($checklistLines, static fn (array $l): bool => ! $l['en_inventario']));

        return view('despacho-lenguas', array_merge($base, [
            'checklistMode' => true,
            'vehiculoEmpresaChecklist' => $empresaPrefill,
            'vehiculoPlacaChecklist' => $placaPrefill,
            // Conductor siempre manual: quien despacha lo elige del listado o lo escribe.
            'vehiculoConductorChecklist' => '',
            'checklistLines' => $checklistLines,
            'checklistTotalConInventario' => $checklistTotalConInv,
            'checklistSinInventarioCount' => $checklistSinInv,
        ]));
    }

    /**
     * Prefiere el operador del catálogo local (despacho_operador_placas) según la placa;
     * si no hay coincidencia, usa el nombre de empresa que venga de Colbeef.
     *
     * @param  list<array{placa: string, operador: string}>  $filas
     */
    private function resolverOperadorPorPlaca(string $placa, string $empresaColbeef, array $filas): string
    {
        $placaNorm = strtoupper(preg_replace('/[\s\-]+/', '', $placa) ?? '');
        if ($placaNorm !== '') {
            foreach ($filas as $fila) {
                $p = strtoupper(preg_replace('/[\s\-]+/', '', (string) ($fila['placa'] ?? '')) ?? '');
                if ($p !== '' && $p === $placaNorm) {
                    $op = trim((string) ($fila['operador'] ?? ''));
                    if ($op !== '') {
                        return $op;
                    }
                }
            }
        }

        return $empresaColbeef;
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

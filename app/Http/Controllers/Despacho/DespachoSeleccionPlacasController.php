<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Despacho\Concerns\ResuelveUsuarioVehiculoDespacho;
use App\Models\Despacho;
use App\Services\Despacho\DespachoParteProductoVehiculoConsulta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DespachoSeleccionPlacasController extends Controller
{
    use ResuelveUsuarioVehiculoDespacho;

    public function __invoke(Request $request): View
    {
        $error = null;
        $placas = [];

        $username = $this->usernameVehiculoEfectivo($request);

        try {
            $consulta = app(DespachoParteProductoVehiculoConsulta::class);
            $placas = $consulta->placasConPendientes($username);
            $placas = $this->excluirPlacasDespachadasHoy($placas);
        } catch (Throwable $e) {
            report($e);
            $error = config('app.debug')
                ? 'No se pudo consultar Colbeef/SIRT: '.$e->getMessage()
                : 'No se pudo consultar la base de despacho corporativa. Revise POSTGRES_* / COLBEEF_DB_* y la VPN.';
        }

        return view('despacho-seleccion-placas', [
            'placas' => $placas,
            'error' => $error,
            'filtroUserName' => $username,
            'filtraPorUserName' => (bool) config('despacho.filtrar_ppv_por_user_name_vehiculo', false),
        ]);
    }

    /**
     * Oculta vehículos ya despachados en el día operativo actual (00:00 → ahora),
     * aunque el cierre sea después de medianoche.
     *
     * Incluye:
     * - checklist Colbeef (id_vehiculo_asignado), y
     * - despacho manual con la misma placa (sin id_vehiculo_asignado), para que no
     *   vuelva a listarse en alistamiento si ya se cerró por «Despacho manual».
     *
     * @param  list<object>  $placas
     * @return list<object>
     */
    protected function excluirPlacasDespachadasHoy(array $placas): array
    {
        if ($placas === []) {
            return $placas;
        }

        $tz = (string) config('app.timezone', 'America/Bogota');
        $consulta = app(DespachoParteProductoVehiculoConsulta::class);
        $diaOperacion = $consulta->fechaRegistroReferenciaHoy();
        $inicioOperacion = Carbon::parse($diaOperacion, $tz)->startOfDay();

        $despachosHoy = Despacho::query()
            ->where('realizado_at', '>=', $inicioOperacion)
            ->get(['id_vehiculo_asignado', 'placa']);

        $cerradosIds = $despachosHoy
            ->pluck('id_vehiculo_asignado')
            ->filter(static fn ($id): bool => $id !== null && (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->all();

        // Solo despacho manual (sin id_vehiculo_asignado): ocultar esa placa el resto del día.
        // Los de checklist ya se ocultan por id_vehiculo_asignado.
        $cerradasPlacas = [];
        foreach ($despachosHoy as $d) {
            if ($d->id_vehiculo_asignado !== null && (int) $d->id_vehiculo_asignado > 0) {
                continue;
            }
            $norm = DespachoParteProductoVehiculoConsulta::normalizarPlacaVehiculo(
                $d->placa !== null ? (string) $d->placa : null,
            );
            if ($norm !== '') {
                $cerradasPlacas[$norm] = true;
            }
        }

        if ($cerradosIds === [] && $cerradasPlacas === []) {
            return $placas;
        }

        $cerrSet = array_fill_keys($cerradosIds, true);

        return array_values(array_filter($placas, static function ($p) use ($cerrSet, $cerradasPlacas): bool {
            $vid = (int) ($p->id_vehiculo_asignado ?? 0);
            if ($vid > 0 && isset($cerrSet[$vid])) {
                return false;
            }
            $placaNorm = DespachoParteProductoVehiculoConsulta::normalizarPlacaVehiculo(
                isset($p->placa_vehiculo) ? (string) $p->placa_vehiculo : null,
            );
            if ($placaNorm !== '' && isset($cerradasPlacas[$placaNorm])) {
                return false;
            }

            return true;
        }));
    }
}

<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Despacho\Concerns\ResuelveUsuarioVehiculoDespacho;
use App\Models\Despacho;
use App\Services\Despacho\DespachoParteProductoVehiculoConsulta;
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

        $diagnosticoSeleccion = null;
        $username = $this->usernameVehiculoEfectivo($request);

        try {
            $consulta = app(DespachoParteProductoVehiculoConsulta::class);
            $placas = $consulta->placasConPendientes($username);
            $placas = $this->excluirPlacasDespachadasHoy($placas);
            if (config('app.debug')) {
                $diagnosticoSeleccion = $consulta->diagnosticoSeleccionPlacas($username);
            }
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
            'diagnosticoSeleccion' => $diagnosticoSeleccion,
        ]);
    }

    /**
     * Oculta en el listado los vehículos cuyo checklist ya se cerró hoy (despacho registrado localmente).
     *
     * @param  list<object>  $placas
     * @return list<object>
     */
    protected function excluirPlacasDespachadasHoy(array $placas): array
    {
        if ($placas === []) {
            return $placas;
        }

        $hoy = now()->timezone((string) config('app.timezone'))->toDateString();
        $cerradosIds = Despacho::query()
            ->whereNotNull('id_vehiculo_asignado')
            ->whereDate('realizado_at', $hoy)
            ->pluck('id_vehiculo_asignado')
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->all();

        if ($cerradosIds === []) {
            return $placas;
        }

        $cerrSet = array_fill_keys($cerradosIds, true);

        return array_values(array_filter($placas, static function ($p) use ($cerrSet): bool {
            $vid = (int) ($p->id_vehiculo_asignado ?? 0);

            return $vid < 1 || ! isset($cerrSet[$vid]);
        }));
    }
}

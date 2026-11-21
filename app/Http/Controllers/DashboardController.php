<?php

namespace App\Http\Controllers;

use App\Models\DecomisoManual;
use App\Models\Despacho;
use App\Models\Desposte;
use App\Models\IngresoLenguaLocal;
use App\Services\Inventario\InventarioLenguasSynchronizer;
use App\Support\VencimientoLengua;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        protected InventarioLenguasSynchronizer $inventarioSynchronizer,
    ) {}

    public function __invoke(): View
    {
        // KPIs / gráfico de ingresos leen la réplica local; sin sync quedan viejos
        // hasta abrir Inventario. Aquí se actualiza SIRT→local si ya venció el intervalo.
        try {
            $this->inventarioSynchronizer->syncIfDue(auth()->id());
        } catch (Throwable $e) {
            report($e);
        }

        $tz = VencimientoLengua::zona();
        $hoy = Carbon::now($tz)->startOfDay();
        $hoyYmd = $hoy->toDateString();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $inventarioTotal = IngresoLenguaLocal::query()->sinDespachar()->count();
        $vencimiento = $this->conteosVencimiento($hoyYmd);

        $despachosMes = Despacho::query()
            ->whereBetween('realizado_at', [$inicioMes, $finMes])
            ->count();

        $lenguasDespachadasMes = IngresoLenguaLocal::query()
            ->whereNotNull('despacho_id')
            ->whereHas('despacho', function ($q) use ($inicioMes, $finMes): void {
                $q->whereBetween('realizado_at', [$inicioMes, $finMes]);
            })
            ->count();

        $despostesMes = Desposte::query()
            ->whereBetween('realizado_at', [$inicioMes, $finMes])
            ->count();

        $decomisos = $this->conteosDecomisosMes(
            $inicioMes->toDateString(),
            $finMes->toDateString(),
            $hoyYmd,
        );

        $disponiblesOk = max(0, $inventarioTotal - $vencimiento['por_vencer'] - $vencimiento['vencidas']);
        $pctOk = $inventarioTotal > 0
            ? (int) round(($disponiblesOk / $inventarioTotal) * 100)
            : 0;

        $serieDespachos = $this->serieDespachosUltimosDias(7, $tz);
        $serieIngresos = $this->serieIngresosUltimosDias(5, $tz);
        $ingresosCincoDiasTotal = collect($serieIngresos)->sum('total');
        $ultimosDespachos = Despacho::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count')
            ->orderByDesc('realizado_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $accesos = $this->accesosRapidos();

        return view('dashboard', [
            'mesEtiqueta' => $inicioMes->copy()->locale('es')->translatedFormat('F Y'),
            'inventarioTotal' => $inventarioTotal,
            'porVencer' => $vencimiento['por_vencer'],
            'vencidas' => $vencimiento['vencidas'],
            'disponiblesOk' => $disponiblesOk,
            'pctOk' => $pctOk,
            'despachosMes' => $despachosMes,
            'lenguasDespachadasMes' => $lenguasDespachadasMes,
            'despostesMes' => $despostesMes,
            'decomisosMes' => $decomisos['total_mes'],
            'decomisosHoy' => $decomisos['total_hoy'],
            'decomisosManualesMes' => $decomisos['manual_mes'],
            'decomisosSirtMes' => $decomisos['sirt_mes'],
            'decomisosSirtOk' => $decomisos['sirt_ok'],
            'serieDespachos' => $serieDespachos,
            'serieIngresos' => $serieIngresos,
            'ingresosCincoDiasTotal' => $ingresosCincoDiasTotal,
            'ultimosDespachos' => $ultimosDespachos,
            'accesos' => $accesos,
        ]);
    }

    /**
     * Misma idea que el módulo Decomisos: manuales locales + inspecciones SIRT del mes.
     *
     * @return array{manual_mes: int, sirt_mes: int, total_mes: int, total_hoy: int, sirt_ok: bool}
     */
    private function conteosDecomisosMes(string $inicioMes, string $finMes, string $hoyYmd): array
    {
        $manualMes = DecomisoManual::query()
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->count();

        $manualHoy = DecomisoManual::query()
            ->whereDate('fecha_registro', $hoyYmd)
            ->count();

        $sirtMes = 0;
        $sirtHoy = 0;
        $sirtOk = false;

        if ($this->sirtDecomisosEnabled()) {
            $connection = (string) config('decomisos.sirt_connection');
            $idParte = (int) config('decomisos.id_parte_producto');
            $idDictamen = (int) config('decomisos.id_dictamen');

            if ($connection !== '') {
                $sql = <<<'SQL'
SELECT
    COUNT(*)::int AS total,
    COUNT(*) FILTER (WHERE i.fecha_registro = ?)::int AS hoy
FROM sai.inspeccion i
WHERE i.id_parte_producto = ?
  AND i.id_dictamen = ?
  AND i.fecha_registro >= ?
  AND i.fecha_registro <= ?
SQL;

                try {
                    $row = DB::connection($connection)->selectOne($sql, [
                        $hoyYmd,
                        $idParte,
                        $idDictamen,
                        $inicioMes,
                        $finMes,
                    ]);
                    $sirtMes = (int) ($row->total ?? 0);
                    $sirtHoy = (int) ($row->hoy ?? 0);
                    $sirtOk = true;
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }

        return [
            'manual_mes' => $manualMes,
            'sirt_mes' => $sirtMes,
            'total_mes' => $manualMes + $sirtMes,
            'total_hoy' => $manualHoy + $sirtHoy,
            'sirt_ok' => $sirtOk,
        ];
    }

    private function sirtDecomisosEnabled(): bool
    {
        if (filter_var(env('COLBEEF_DB_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (filter_var(env('POSTGRES_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return Str::of((string) env('POSTGRES_HOST', ''))->trim()->isNotEmpty();
    }

    /**
     * @return array{por_vencer: int, vencidas: int}
     */
    private function conteosVencimiento(string $hoyYmd): array
    {
        $hoy = Carbon::parse($hoyYmd, VencimientoLengua::zona())->startOfDay();
        $rangoPorVencer = VencimientoLengua::rangoFechaRegistroPorVencer($hoy);
        $vencidasAntesDe = VencimientoLengua::fechaRegistroVencidasAntesDe($hoy);

        $base = IngresoLenguaLocal::query()
            ->sinDespachar()
            ->whereNotNull('fecha_registro');

        $porVencer = (clone $base)
            ->whereDate('fecha_registro', '>=', $rangoPorVencer['desde'])
            ->whereDate('fecha_registro', '<=', $rangoPorVencer['hasta'])
            ->count();

        $vencidas = (clone $base)
            ->whereDate('fecha_registro', '<', $vencidasAntesDe)
            ->count();

        return [
            'por_vencer' => $porVencer,
            'vencidas' => $vencidas,
        ];
    }

    /**
     * @return list<array{fecha: string, etiqueta: string, total: int}>
     */
    private function serieDespachosUltimosDias(int $dias, string $tz): array
    {
        $fin = Carbon::now($tz)->startOfDay();
        $inicio = $fin->copy()->subDays($dias - 1);
        $counts = Despacho::query()
            ->where('realizado_at', '>=', $inicio)
            ->get(['realizado_at'])
            ->groupBy(fn (Despacho $d): string => $d->realizado_at?->timezone($tz)->format('Y-m-d') ?? '')
            ->map->count();

        $out = [];
        for ($i = 0; $i < $dias; $i++) {
            $d = $inicio->copy()->addDays($i);
            $key = $d->format('Y-m-d');
            $out[] = [
                'fecha' => $key,
                'etiqueta' => $d->copy()->locale('es')->translatedFormat('D d'),
                'total' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * Lenguas registradas (ingresos) por fecha_registro en los últimos N días.
     *
     * @return list<array{fecha: string, etiqueta: string, total: int}>
     */
    private function serieIngresosUltimosDias(int $dias, string $tz): array
    {
        $fin = Carbon::now($tz)->startOfDay();
        $inicio = $fin->copy()->subDays($dias - 1);
        $inicioYmd = $inicio->toDateString();
        $finYmd = $fin->toDateString();

        $raw = IngresoLenguaLocal::query()
            ->whereNotNull('fecha_registro')
            ->whereBetween('fecha_registro', [$inicioYmd, $finYmd])
            ->selectRaw('fecha_registro as dia, COUNT(*) as total')
            ->groupBy('fecha_registro')
            ->pluck('total', 'dia');

        $out = [];
        for ($i = 0; $i < $dias; $i++) {
            $d = $inicio->copy()->addDays($i);
            $key = $d->format('Y-m-d');
            $total = 0;
            foreach ($raw as $dia => $n) {
                $diaStr = $dia instanceof \DateTimeInterface
                    ? $dia->format('Y-m-d')
                    : substr((string) $dia, 0, 10);
                if ($diaStr === $key) {
                    $total = (int) $n;
                    break;
                }
            }
            $out[] = [
                'fecha' => $key,
                'etiqueta' => $d->copy()->locale('es')->translatedFormat('D d'),
                'total' => $total,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{titulo: string, desc: string, route: string}>
     */
    private function accesosRapidos(): array
    {
        $candidatos = [
            ['titulo' => 'Inventario', 'desc' => 'Stock disponible y sync', 'route' => 'inventario.lenguas'],
            ['titulo' => 'Despacho', 'desc' => 'Alistar y cerrar carga', 'route' => 'despacho.lenguas'],
            ['titulo' => 'Decomisos', 'desc' => 'Manuales y consulta SIRT', 'route' => 'decomisos.lenguas'],
            ['titulo' => 'Conformidad', 'desc' => 'Acta PDF y manifiestos', 'route' => 'entrega.conformidad'],
            ['titulo' => 'Destinos y etiquetas', 'desc' => 'Destino, Adicional y Salida', 'route' => 'cambios.destinos'],
            ['titulo' => 'Historial', 'desc' => 'Despachos y despostes', 'route' => 'historia.despacho.lenguas'],
        ];

        $out = [];
        foreach ($candidatos as $item) {
            if (Route::has($item['route'])) {
                $out[] = $item;
            }
        }

        return $out;
    }
}

<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\IngresoLenguaLocal;
use App\Services\IngresosLenguas\IngresosLenguasConsultaSirt;
use App\Services\Inventario\InventarioLenguasSynchronizer;
use App\Support\PesoFormatter;
use App\Support\SimpleXlsxWriter;
use App\Support\VencimientoLengua;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorInstance;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventarioLenguasController extends Controller
{
    public function __construct(
        protected IngresosLenguasConsultaSirt $consultaSirt,
        protected InventarioLenguasSynchronizer $synchronizer,
    ) {}

    public function index(Request $request): View
    {
        $interval = (int) config('ingresos_lenguas.inventario_sync_interval_seconds', 120);
        if ($interval > 0) {
            $interval = max(30, $interval);
        }

        $payload = $this->inventarioListadoPayload($request);
        $syncMeta = $this->synchronizer->meta();

        $viewData = [
            'rows' => $payload['rows'],
            'filters' => $payload['filters'],
            'filtrosActivos' => $payload['filtrosActivos'],
            'listadoEmptyHint' => $payload['listadoEmptyHint'],
            'inventarioSyncIntervalSeconds' => $interval,
            'hoyOperacion' => $this->consultaSirt->fechaHoyOperacion(),
            'inventarioSyncMeta' => $syncMeta,
        ];

        if ($payload['validator']->fails()) {
            return view('inventario-lenguas', $viewData)->withErrors($payload['validator']);
        }

        return view('inventario-lenguas', $viewData);
    }

    public function exportExcel(Request $request): StreamedResponse|RedirectResponse
    {
        $payload = $this->inventarioListadoPayload($request);

        if ($payload['validator']->fails()) {
            return redirect()
                ->route('inventario.lenguas', $request->query())
                ->withErrors($payload['validator']);
        }

        $headers = [
            'Id producto',
            'Fecha registro',
            'Hora registro',
            'Propietario',
            'Destino',
            'Peso',
            'Ref. turno',
            'Fecha de vencimiento',
            'Vida útil',
        ];

        $excelRows = [];
        foreach ($payload['rows'] as $row) {
            /** @var IngresoLenguaLocal $row */
            $excelRows[] = [
                (string) $row->id_producto,
                $row->fecha_registro?->format('Y-m-d') ?? '',
                $row->hora_registro !== null ? (string) $row->hora_registro : '',
                $row->propietario !== null ? (string) $row->propietario : '',
                $row->destino !== null ? (string) $row->destino : '',
                PesoFormatter::mostrar($row->peso),
                $row->fecha_turno_referencia?->format('Y-m-d') ?? '',
                VencimientoLengua::fechaVencimientoTexto($row->fecha_registro),
                VencimientoLengua::diasHastaVencimientoTexto($row->fecha_registro),
            ];
        }

        $binary = SimpleXlsxWriter::build($headers, $excelRows, 'Inventario');
        $filename = 'inventario-lenguas-'.Carbon::now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(static function () use ($binary): void {
            echo $binary;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importarDesdeHoy(Request $request): RedirectResponse|JsonResponse
    {
        if (! $request->expectsJson()) {
            $request->session()->forget(['import_message', 'import_error']);
        }

        $outcome = $this->importarDesdeHoyInterno($request);
        $payload = $this->inventarioListadoPayload($request);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => $outcome['success'],
                'hit_limit' => (bool) ($outcome['hit_limit'] ?? false),
                'limite' => $outcome['limite'] ?? null,
                'html' => view('inventario-lenguas._listado', [
                    'rows' => $payload['rows'],
                    'importMessage' => $outcome['success'] ? $outcome['message'] : null,
                    'importError' => $outcome['success'] ? null : $outcome['message'],
                    'filtrosActivos' => $payload['filtrosActivos'],
                    'listadoEmptyHint' => $payload['listadoEmptyHint'],
                    'inventarioSyncMeta' => $this->synchronizer->meta(),
                ])->render(),
            ]);
        }

        $queryFiltros = $request->only([
            'fecha_desde',
            'fecha_hasta',
            'id_producto',
            'propietario',
            'vida_util',
            'todos',
            'por_vencer',
            'vencidas',
        ]);
        if ($request->boolean('todos')) {
            $queryFiltros['todos'] = 1;
        } else {
            unset($queryFiltros['todos']);
        }
        if ($request->boolean('por_vencer')) {
            $queryFiltros['por_vencer'] = 1;
            unset($queryFiltros['fecha_desde'], $queryFiltros['fecha_hasta'], $queryFiltros['todos'], $queryFiltros['vencidas']);
        } else {
            unset($queryFiltros['por_vencer']);
        }
        if ($request->boolean('vencidas')) {
            $queryFiltros['vencidas'] = 1;
            unset($queryFiltros['fecha_desde'], $queryFiltros['fecha_hasta'], $queryFiltros['todos'], $queryFiltros['por_vencer']);
        } else {
            unset($queryFiltros['vencidas']);
        }

        if ($outcome['success']) {
            return redirect()
                ->route('inventario.lenguas', $queryFiltros)
                ->with('import_message', $outcome['message']);
        }

        return redirect()
            ->route('inventario.lenguas', $queryFiltros)
            ->with('import_error', $outcome['message']);
    }

    /**
     * @return array{
     *     rows: Collection<int, IngresoLenguaLocal>,
     *     filters: array{
     *         fecha_desde: string,
     *         fecha_hasta: string,
     *         id_producto: string,
     *         propietario: string,
     *         vida_util: string,
     *     },
     *     filtrosActivos: bool,
     *     listadoEmptyHint: 'validation'|'filtered'|'global',
     *     validator: ValidatorInstance
     * }
     */
    private function inventarioListadoPayload(Request $request): array
    {
        $porVencer = $request->boolean('por_vencer');
        $vencidas = $request->boolean('vencidas');
        // Un solo modo de alerta a la vez.
        if ($vencidas) {
            $porVencer = false;
        }

        $fechas = $this->normalizarFechasTurnoInventario($request);
        $fechaDesdeTrim = $fechas['desde'];
        $fechaHastaTrim = $fechas['hasta'];

        // Alertas de vida útil: stock completo (sin acotar ref. turno), mismo criterio que el KPI.
        if ($porVencer || $vencidas) {
            $fechaDesdeTrim = '';
            $fechaHastaTrim = '';
        }

        // Por defecto: día de operación (alinea listado con la sync SIRT del día).
        if ($fechaDesdeTrim === '' && $fechaHastaTrim === '' && ! $request->boolean('todos') && ! $porVencer && ! $vencidas) {
            $hoy = $this->consultaSirt->fechaHoyOperacion();
            $fechaDesdeTrim = $hoy;
            $fechaHastaTrim = $hoy;
        }

        $filters = [
            'fecha_desde' => $fechaDesdeTrim,
            'fecha_hasta' => $fechaHastaTrim,
            'id_producto' => substr($request->string('id_producto')->trim()->toString(), 0, 80),
            'propietario' => substr($request->string('propietario')->trim()->toString(), 0, 200),
            'vida_util' => substr($request->string('vida_util')->trim()->toString(), 0, 32),
            'por_vencer' => $porVencer,
            'vencidas' => $vencidas,
        ];

        $filtrosActivos = $fechaDesdeTrim !== ''
            || $fechaHastaTrim !== ''
            || $filters['id_producto'] !== ''
            || $filters['propietario'] !== ''
            || $filters['vida_util'] !== ''
            || $porVencer
            || $vencidas;

        $validator = $this->buildInventarioValidator($fechaDesdeTrim, $fechaHastaTrim, $filters);

        if ($validator->fails()) {
            return [
                'rows' => collect(),
                'filters' => $filters,
                'filtrosActivos' => $filtrosActivos,
                'listadoEmptyHint' => 'validation',
                'validator' => $validator,
            ];
        }

        $rows = $this->ejecutarConsultaInventario(
            $fechaDesdeTrim,
            $fechaHastaTrim,
            $filters['id_producto'],
            $filters['propietario'],
            $filters['vida_util'],
            $porVencer,
            $vencidas,
        );

        $listadoEmptyHint = 'global';
        if ($rows->isEmpty()) {
            $listadoEmptyHint = $filtrosActivos ? 'filtered' : 'global';
        }

        return [
            'rows' => $rows,
            'filters' => $filters,
            'filtrosActivos' => $filtrosActivos,
            'listadoEmptyHint' => $listadoEmptyHint,
            'validator' => $validator,
        ];
    }

    /**
     * @param  array{
     *     id_producto: string,
     *     propietario: string,
     *     vida_util: string,
     * }  $filters
     */
    private function buildInventarioValidator(
        string $fechaDesdeTrim,
        string $fechaHastaTrim,
        array $filters,
    ): ValidatorInstance {
        $data = [
            'id_producto' => $filters['id_producto'],
            'propietario' => $filters['propietario'],
            'fecha_desde' => $fechaDesdeTrim === '' ? null : $fechaDesdeTrim,
            'fecha_hasta' => $fechaHastaTrim === '' ? null : $fechaHastaTrim,
            'vida_util' => $filters['vida_util'] === '' ? null : $filters['vida_util'],
        ];

        $validator = Validator::make($data, [
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'vida_util' => ['nullable', 'string', 'max:32'],
        ]);

        $validator->after(function ($v) use ($fechaDesdeTrim, $fechaHastaTrim): void {
            $d1Present = $fechaDesdeTrim !== '';
            $d2Present = $fechaHastaTrim !== '';
            if ($d1Present && $d2Present && $fechaDesdeTrim > $fechaHastaTrim) {
                $v->errors()->add('fecha_hasta', 'La fecha hasta debe ser mayor o igual que la fecha desde.');
            }
        });

        return $validator;
    }

    /**
     * Si solo se envía una fecha de ref. turno, replica el valor en la otra (un solo día de consulta).
     *
     * @return array{desde: string, hasta: string}
     */
    private function normalizarFechasTurnoInventario(Request $request): array
    {
        $desde = trim((string) $request->input('fecha_desde', ''));
        $hasta = trim((string) $request->input('fecha_hasta', ''));
        if ($desde !== '' && $hasta === '') {
            $hasta = $desde;
        }
        if ($hasta !== '' && $desde === '') {
            $desde = $hasta;
        }

        return ['desde' => $desde, 'hasta' => $hasta];
    }

    private function ejecutarConsultaInventario(
        string $fechaDesdeTrim,
        string $fechaHastaTrim,
        string $idProducto,
        string $propietario,
        string $vidaUtilTrim,
        bool $porVencer = false,
        bool $vencidas = false,
    ): Collection {
        $op = $this->likeOperator();
        $patId = '%'.$idProducto.'%';
        $patProp = '%'.$propietario.'%';

        $hoyYmd = Carbon::now(VencimientoLengua::zona())->startOfDay()->format('Y-m-d');
        $castDiasTexto = $this->sqlCastDiasHastaVencimientoAsText();
        $diasExpr = $this->sqlDiasHastaVencimientoColumn();

        $q = IngresoLenguaLocal::query()
            ->sinDespachar()
            ->when($fechaDesdeTrim !== '' && $fechaHastaTrim !== '', function ($q) use ($fechaDesdeTrim, $fechaHastaTrim): void {
                $q->whereBetween('fecha_turno_referencia', [$fechaDesdeTrim, $fechaHastaTrim]);
            })
            ->when($idProducto !== '', function ($q) use ($op, $patId): void {
                $q->where('id_producto', $op, $patId);
            })
            ->when($propietario !== '', function ($q) use ($op, $patProp): void {
                $q->where('propietario', $op, $patProp);
            })
            ->when($porVencer, function ($q) use ($diasExpr, $hoyYmd): void {
                // Vencen hoy o mañana (0–1 días), igual que DashboardController::conteosVencimiento.
                $q->whereNotNull('fecha_registro')
                    ->whereRaw("({$diasExpr}) BETWEEN 0 AND 1", [$hoyYmd]);
            })
            ->when($vencidas, function ($q) use ($diasExpr, $hoyYmd): void {
                // Ya superaron la fecha de vencimiento (< 0 días).
                $q->whereNotNull('fecha_registro')
                    ->whereRaw("({$diasExpr}) < 0", [$hoyYmd]);
            })
            ->when($vidaUtilTrim !== '' && ! $porVencer && ! $vencidas, function ($q) use ($castDiasTexto, $hoyYmd, $vidaUtilTrim, $op): void {
                $needle = '%'.addcslashes($vidaUtilTrim, '%_\\').'%';
                if ($op === 'ilike') {
                    $q->whereRaw("({$castDiasTexto}) ilike ?", [$hoyYmd, $needle]);
                } else {
                    $q->whereRaw("({$castDiasTexto}) like ?", [$hoyYmd, $needle]);
                }
            })
            ->orderByDesc('imported_at')
            ->orderByDesc('id')
            ->limit(2000);

        return $q->get();
    }

    /**
     * Días hasta vencimiento (fecha registro + 4 días calendario vs. hoy en zona operación), alineado a {@see VencimientoLengua::diasHastaVencimiento()}.
     * El fragmento incluye un marcador ? para la fecha de referencia (Y-m-d).
     */
    private function sqlDiasHastaVencimientoColumn(): string
    {
        return match (DB::getDriverName()) {
            'pgsql' => '(CASE WHEN fecha_registro IS NULL THEN NULL ELSE ((fecha_registro + INTERVAL \'4 days\')::date - CAST(? AS date))::integer END)',
            'sqlite' => '(CASE WHEN fecha_registro IS NULL THEN NULL ELSE CAST(ROUND(julianday(date(fecha_registro, \'+4 days\')) - julianday(?)) AS INTEGER) END)',
            default => '(CASE WHEN fecha_registro IS NULL THEN NULL ELSE DATEDIFF(DATE_ADD(fecha_registro, INTERVAL 4 DAY), ?) END)',
        };
    }

    /** Texto del número de días (misma idea que la columna, sin la palabra «días»), para búsqueda libre con LIKE. */
    private function sqlCastDiasHastaVencimientoAsText(): string
    {
        $inner = $this->sqlDiasHastaVencimientoColumn();

        return match (DB::getDriverName()) {
            'pgsql' => "CAST(({$inner}) AS TEXT)",
            default => "CAST(({$inner}) AS CHAR)",
        };
    }

    private function likeOperator(): string
    {
        return DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    /**
     * @return array{success: bool, message: string, hit_limit?: bool, limite?: int}
     */
    private function importarDesdeHoyInterno(Request $request): array
    {
        $fechas = $this->normalizarFechasTurnoInventario($request);
        $fechaDesdeTrim = $fechas['desde'];
        $fechaHastaTrim = $fechas['hasta'];

        if (
            $fechaDesdeTrim === ''
            && $fechaHastaTrim === ''
            && ! $request->boolean('todos')
            && ! $request->boolean('por_vencer')
            && ! $request->boolean('vencidas')
        ) {
            $hoy = $this->consultaSirt->fechaHoyOperacion();
            $fechaDesdeTrim = $hoy;
            $fechaHastaTrim = $hoy;
        }

        return $this->synchronizer->sync(
            $fechaDesdeTrim !== '' ? $fechaDesdeTrim : null,
            $fechaHastaTrim !== '' ? $fechaHastaTrim : null,
            [
                'id_producto' => substr($request->string('id_producto')->trim()->toString(), 0, 80),
                'propietario' => substr($request->string('propietario')->trim()->toString(), 0, 200),
            ],
            $request->user()?->id,
        );
    }
}

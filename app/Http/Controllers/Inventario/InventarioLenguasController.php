<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\IngresoLenguaLocal;
use App\Services\IngresosLenguas\IngresosLenguasConsultaSirt;
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
use Throwable;

class InventarioLenguasController extends Controller
{
    public function __construct(
        protected IngresosLenguasConsultaSirt $consultaSirt
    ) {}

    public function index(Request $request): View
    {
        $interval = (int) config('ingresos_lenguas.inventario_sync_interval_seconds', 120);
        if ($interval > 0) {
            $interval = max(30, $interval);
        }

        $payload = $this->inventarioListadoPayload($request);

        if ($payload['validator']->fails()) {
            return view('inventario-lenguas', [
                'rows' => $payload['rows'],
                'filters' => $payload['filters'],
                'filtrosActivos' => $payload['filtrosActivos'],
                'listadoEmptyHint' => $payload['listadoEmptyHint'],
                'inventarioSyncIntervalSeconds' => $interval,
                'hoyOperacion' => $this->consultaSirt->fechaHoyOperacion(),
            ])->withErrors($payload['validator']);
        }

        return view('inventario-lenguas', [
            'rows' => $payload['rows'],
            'filters' => $payload['filters'],
            'filtrosActivos' => $payload['filtrosActivos'],
            'listadoEmptyHint' => $payload['listadoEmptyHint'],
            'inventarioSyncIntervalSeconds' => $interval,
            'hoyOperacion' => $this->consultaSirt->fechaHoyOperacion(),
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
                'html' => view('inventario-lenguas._listado', [
                    'rows' => $payload['rows'],
                    'importMessage' => $outcome['success'] ? $outcome['message'] : null,
                    'importError' => $outcome['success'] ? null : $outcome['message'],
                    'filtrosActivos' => $payload['filtrosActivos'],
                    'listadoEmptyHint' => $payload['listadoEmptyHint'],
                ])->render(),
            ]);
        }

        $queryFiltros = $request->only([
            'fecha_desde',
            'fecha_hasta',
            'id_producto',
            'propietario',
            'vida_util',
        ]);

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
        $fechas = $this->normalizarFechasTurnoInventario($request);
        $fechaDesdeTrim = $fechas['desde'];
        $fechaHastaTrim = $fechas['hasta'];

        $filters = [
            'fecha_desde' => $fechaDesdeTrim,
            'fecha_hasta' => $fechaHastaTrim,
            'id_producto' => substr($request->string('id_producto')->trim()->toString(), 0, 80),
            'propietario' => substr($request->string('propietario')->trim()->toString(), 0, 200),
            'vida_util' => substr($request->string('vida_util')->trim()->toString(), 0, 32),
        ];

        $filtrosActivos = $fechaDesdeTrim !== ''
            || $fechaHastaTrim !== ''
            || $filters['id_producto'] !== ''
            || $filters['propietario'] !== ''
            || $filters['vida_util'] !== '';

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
    ): Collection {
        $op = $this->likeOperator();
        $patId = '%'.$idProducto.'%';
        $patProp = '%'.$propietario.'%';

        $hoyYmd = Carbon::now(VencimientoLengua::zona())->startOfDay()->format('Y-m-d');
        $castDiasTexto = $this->sqlCastDiasHastaVencimientoAsText();

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
            ->when($vidaUtilTrim !== '', function ($q) use ($castDiasTexto, $hoyYmd, $vidaUtilTrim, $op): void {
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
     * @return array{success: bool, message: string}
     */
    private function importarDesdeHoyInterno(Request $request): array
    {
        if (! $this->consultaSirt->externalPostgresEnabled()) {
            return [
                'success' => false,
                'message' => 'Configure la conexión SIRT/Colbeef en .env para importar.',
            ];
        }

        $connection = (string) config('ingresos_lenguas.connection');
        if ($connection === '') {
            return [
                'success' => false,
                'message' => 'Conexión no configurada (ingresos_lenguas.connection).',
            ];
        }

        $hoy = $this->consultaSirt->fechaHoyOperacion();

        $fechas = $this->normalizarFechasTurnoInventario($request);
        $fechaDesdeTrim = $fechas['desde'];
        $fechaHastaTrim = $fechas['hasta'];

        $useRangoTurno = false;
        if ($fechaDesdeTrim !== '' && $fechaHastaTrim !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesdeTrim) === 1
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHastaTrim) === 1
                && $fechaDesdeTrim <= $fechaHastaTrim) {
                $useRangoTurno = true;
            }
        }

        $primeraCarga = ! $useRangoTurno;
        $fechaSqlDesde = $useRangoTurno ? $fechaDesdeTrim : '';
        $fechaSqlHasta = $useRangoTurno ? $fechaHastaTrim : '';

        $filters = [
            'id_producto' => substr($request->string('id_producto')->trim()->toString(), 0, 80),
            'propietario' => substr($request->string('propietario')->trim()->toString(), 0, 200),
        ];

        $result = $this->consultaSirt->consultar(
            $connection,
            $hoy,
            $primeraCarga,
            $fechaSqlDesde,
            $fechaSqlHasta,
            $filters,
            true,
        );

        if ($result['exception'] instanceof Throwable) {
            report($result['exception']);
            $message = 'No se pudo leer desde SIRT. Revise conexión y permisos.';
            if (config('app.debug')) {
                $message .= ' Detalle: '.$result['exception']->getMessage();
            }

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $userId = $request->user()?->id;
        $importados = 0;

        try {
            $fechaTurnoFallback = $useRangoTurno ? $fechaHastaTrim : $hoy;

            DB::transaction(function () use ($result, $userId, &$importados, $fechaTurnoFallback): void {
                foreach ($result['rows'] as $row) {
                    $r = (array) $row;
                    $insId = (int) ($r['insensibilizacion_id'] ?? 0);
                    if ($insId <= 0) {
                        continue;
                    }

                    $fechaReg = $r['fecha_registro'] ?? null;
                    if ($fechaReg instanceof \DateTimeInterface) {
                        $fechaReg = $fechaReg->format('Y-m-d');
                    }

                    $hora = $r['hora_registro'] ?? null;
                    if ($hora instanceof \DateTimeInterface) {
                        $hora = $hora->format('H:i:s');
                    }

                    $fechaTurnoRef = $r['fecha_turno_referencia'] ?? null;
                    if ($fechaTurnoRef instanceof \DateTimeInterface) {
                        $fechaTurnoRef = $fechaTurnoRef->format('Y-m-d');
                    } elseif (is_string($fechaTurnoRef)) {
                        $fechaTurnoRef = trim($fechaTurnoRef);
                        $fechaTurnoRef = $fechaTurnoRef !== '' ? $fechaTurnoRef : null;
                    } else {
                        $fechaTurnoRef = null;
                    }

                    IngresoLenguaLocal::query()->updateOrCreate(
                        ['insensibilizacion_id' => $insId],
                        [
                            'id_producto' => (string) ($r['id_producto'] ?? ''),
                            'fecha_registro' => $fechaReg,
                            'hora_registro' => $hora !== null && $hora !== '' ? (string) $hora : null,
                            'propietario' => isset($r['propietario']) ? (string) $r['propietario'] : null,
                            'destino' => isset($r['destino']) ? (string) $r['destino'] : null,
                            'peso' => isset($r['peso']) && $r['peso'] !== '' ? $r['peso'] : null,
                            'fecha_turno_referencia' => $fechaTurnoRef ?? $fechaTurnoFallback,
                            'imported_at' => now(),
                            'user_id' => $userId,
                        ],
                    );
                    $importados++;
                }
            });
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Error al guardar en la base local.',
            ];
        }

        $limiteConsulta = max(100, min(10000, (int) config('ingresos_lenguas.consulta_insensibilizacion_limit', 2000)));
        $msg = $useRangoTurno
            ? "Sincronización SIRT (ref. turno {$fechaDesdeTrim} a {$fechaHastaTrim}): {$importados} registro(s) actualizados o creados en la réplica local."
            : "Importación del día de operación ({$hoy}): {$importados} registro(s) actualizados o creados.";
        if ($importados >= $limiteConsulta) {
            $msg .= " Aviso: se alcanzó el tope de filas por consulta ({$limiteConsulta}); si faltan códigos, aumente INGRESOS_LENGUAS_CONSULTA_LIMIT o acote fechas/propietario.";
        }

        return [
            'success' => true,
            'message' => $msg,
        ];
    }
}

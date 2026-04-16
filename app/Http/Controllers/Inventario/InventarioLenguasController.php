<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\IngresoLenguaLocal;
use App\Services\IngresosLenguas\IngresosLenguasConsultaSirt;
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

        $queryFiltros = $request->only(['fecha_desde', 'fecha_hasta', 'id_producto', 'propietario']);

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
     *     filters: array{fecha_desde: string, fecha_hasta: string, id_producto: string, propietario: string},
     *     filtrosActivos: bool,
     *     listadoEmptyHint: 'validation'|'filtered'|'global',
     *     validator: ValidatorInstance
     * }
     */
    private function inventarioListadoPayload(Request $request): array
    {
        $fechaDesdeTrim = trim((string) $request->input('fecha_desde', ''));
        $fechaHastaTrim = trim((string) $request->input('fecha_hasta', ''));

        $filters = [
            'fecha_desde' => (string) $request->input('fecha_desde', ''),
            'fecha_hasta' => (string) $request->input('fecha_hasta', ''),
            'id_producto' => substr($request->string('id_producto')->trim()->toString(), 0, 80),
            'propietario' => substr($request->string('propietario')->trim()->toString(), 0, 200),
        ];

        $filtrosActivos = $fechaDesdeTrim !== ''
            || $fechaHastaTrim !== ''
            || $filters['id_producto'] !== ''
            || $filters['propietario'] !== '';

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
     * @param  array{id_producto: string, propietario: string}  $filters
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
        ];

        $validator = Validator::make($data, [
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);

        $validator->after(function ($v) use ($fechaDesdeTrim, $fechaHastaTrim): void {
            $d1Present = $fechaDesdeTrim !== '';
            $d2Present = $fechaHastaTrim !== '';
            if ($d1Present xor $d2Present) {
                $v->errors()->add('fecha_hasta', 'Indique ambas fechas de referencia de turno o déjelas vacías.');

                return;
            }
            if ($d1Present && $d2Present && $fechaDesdeTrim > $fechaHastaTrim) {
                $v->errors()->add('fecha_hasta', 'La fecha hasta debe ser mayor o igual que la fecha desde.');
            }
        });

        return $validator;
    }

    private function ejecutarConsultaInventario(
        string $fechaDesdeTrim,
        string $fechaHastaTrim,
        string $idProducto,
        string $propietario,
    ): Collection {
        $op = $this->likeOperator();
        $patId = '%'.$idProducto.'%';
        $patProp = '%'.$propietario.'%';

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
            ->orderByDesc('imported_at')
            ->orderByDesc('id')
            ->limit(2000);

        return $q->get();
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
        $filters = ['id_producto' => '', 'propietario' => ''];

        $result = $this->consultaSirt->consultar(
            $connection,
            $hoy,
            true,
            '',
            '',
            $filters,
            true,
        );

        if ($result['exception'] instanceof Throwable) {
            return [
                'success' => false,
                'message' => 'No se pudo leer desde SIRT. Revise conexión y permisos.',
            ];
        }

        $userId = $request->user()?->id;
        $importados = 0;

        try {
            DB::transaction(function () use ($result, $hoy, $userId, &$importados): void {
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

                    IngresoLenguaLocal::query()->updateOrCreate(
                        ['insensibilizacion_id' => $insId],
                        [
                            'id_producto' => (string) ($r['id_producto'] ?? ''),
                            'fecha_registro' => $fechaReg,
                            'hora_registro' => $hora !== null && $hora !== '' ? (string) $hora : null,
                            'propietario' => isset($r['propietario']) ? (string) $r['propietario'] : null,
                            'destino' => isset($r['destino']) ? (string) $r['destino'] : null,
                            'peso' => isset($r['peso']) && $r['peso'] !== '' ? $r['peso'] : null,
                            'fecha_turno_referencia' => $hoy,
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

        return [
            'success' => true,
            'message' => "Importación del día ({$hoy}) finalizada: {$importados} registro(s) actualizados o creados.",
        ];
    }
}

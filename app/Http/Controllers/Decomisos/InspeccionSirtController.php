<?php

namespace App\Http\Controllers\Decomisos;

use App\Http\Controllers\Controller;
use App\Models\DecomisoManual;
use App\Models\IngresoLenguaLocal;
use App\Support\DespachoCodigoBarras;
use App\Support\DespachoInventarioMatch;
use App\Support\SimpleXlsxWriter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InspeccionSirtController extends Controller
{
    protected function sirtLookupEnabled(): bool
    {
        if (filter_var(env('COLBEEF_DB_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (filter_var(env('POSTGRES_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return Str::of((string) env('POSTGRES_HOST', ''))->trim()->isNotEmpty();
    }

    public function index(Request $request): View
    {
        $idParte = (int) config('decomisos.id_parte_producto');
        $idDictamen = (int) config('decomisos.id_dictamen');

        $ahora = Carbon::now();
        $inicioMes = $ahora->copy()->startOfMonth()->toDateString();
        $finMes = $ahora->copy()->endOfMonth()->toDateString();

        $qRaw = substr($request->string('q')->trim()->toString(), 0, 200);
        $tieneBusquedaLibre = str_replace(['%', '_'], '', $qRaw) !== '';

        $fechaDesdeIn = $request->filled('fecha_desde') ? (string) $request->input('fecha_desde') : null;
        $fechaHastaIn = $request->filled('fecha_hasta') ? (string) $request->input('fecha_hasta') : null;

        // Sin filtros de fecha ni búsqueda libre: listar decomisos del mes en curso.
        // El usuario puede cambiar desde/hasta libremente cuando lo necesite.
        if (! $tieneBusquedaLibre && $fechaDesdeIn === null && $fechaHastaIn === null) {
            $fechaDesdeIn = $inicioMes;
            $fechaHastaIn = $finMes;
        }

        if ($tieneBusquedaLibre) {
            $fechaDesdeIn = null;
            $fechaHastaIn = null;
        }

        $filters = [
            'q' => $qRaw,
            'fecha_desde' => $fechaDesdeIn,
            'fecha_hasta' => $fechaHastaIn,
        ];

        $catalogos = $this->catalogosDictamenEnfermedad($idParte);

        $baseView = [
            'filters' => $filters,
            'idParteProducto' => $idParte,
            'idDictamen' => $idDictamen,
            'opcionesDictamen' => $catalogos['dictamenes'],
            'opcionesEnfermedad' => $catalogos['enfermedades'],
        ];

        $validator = Validator::make(
            [
                'q' => $filters['q'],
                'fecha_desde' => $filters['fecha_desde'],
                'fecha_hasta' => $filters['fecha_hasta'],
            ],
            [
                'q' => ['nullable', 'string', 'max:200'],
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date'],
            ],
        );

        $validator->after(function ($v) use ($filters): void {
            $d1 = $filters['fecha_desde'] ?? null;
            $d2 = $filters['fecha_hasta'] ?? null;
            if (is_string($d1) && $d1 !== '' && is_string($d2) && $d2 !== '' && $d1 > $d2) {
                $v->errors()->add('fecha_hasta', 'La fecha hasta debe ser mayor o igual que la fecha desde.');
            }
        });

        if ($validator->fails()) {
            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => $this->filasManualesComoRows($filters),
                'error' => null,
                'invRetiradosDecomiso' => 0,
                'invSyncDecomisoError' => null,
            ]))->withErrors($validator);
        }

        $manualRows = $this->filasManualesComoRows($filters);

        if (! $this->sirtLookupEnabled()) {
            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => $manualRows,
                'error' => 'Configure la conexión SIRT en .env (POSTGRES_HOST, POSTGRES_DB, POSTGRES_USER, POSTGRES_PASSWORD). Los decomisos manuales sí están disponibles.',
                'invRetiradosDecomiso' => 0,
                'invSyncDecomisoError' => null,
            ]));
        }

        $connection = (string) config('decomisos.sirt_connection');
        if ($connection === '') {
            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => $manualRows,
                'error' => 'Conexión SIRT no configurada (decomisos.sirt_connection).',
                'invRetiradosDecomiso' => 0,
                'invSyncDecomisoError' => null,
            ]));
        }

        $sql = <<<'SQL'
SELECT
    i.id_producto,
    i.fecha_registro,
    i.hora_registro,
    d.nombre AS nombre_dictamen,
    e.nombre AS nombre_enfermedad
FROM sai.inspeccion i
JOIN sai.dictamen d ON i.id_dictamen = d.id
LEFT JOIN sai.enfermedad_detectada ed ON i.id = ed.id_inspeccion
LEFT JOIN sai.enfermedad e ON ed.id_enfermedad = e.id
WHERE i.id_parte_producto = ?
  AND i.id_dictamen = ?
SQL;

        $bindings = [$idParte, $idDictamen];

        $q = str_replace(['%', '_'], '', $filters['q']);
        if ($q !== '') {
            $pat = '%'.$q.'%';
            $sql .= ' AND (
    i.id_producto::text ILIKE ?
    OR d.nombre ILIKE ?
    OR COALESCE(e.nombre, \'\') ILIKE ?
)';
            $bindings[] = $pat;
            $bindings[] = $pat;
            $bindings[] = $pat;
        }

        if ($q === '') {
            if (! empty($filters['fecha_desde'])) {
                $sql .= ' AND i.fecha_registro >= ?';
                $bindings[] = $filters['fecha_desde'];
            }

            if (! empty($filters['fecha_hasta'])) {
                $sql .= ' AND i.fecha_registro <= ?';
                $bindings[] = $filters['fecha_hasta'];
            }
        }

        $sql .= ' ORDER BY i.id DESC LIMIT 500';

        try {
            $sirtRows = DB::connection($connection)->select($sql, $bindings);
        } catch (Throwable $e) {
            report($e);

            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => $manualRows,
                'error' => 'No se pudo consultar SIRT. Revise red/VPN, credenciales y que existan los esquemas sai.*. Los decomisos manuales sí están disponibles.',
                'invRetiradosDecomiso' => 0,
                'invSyncDecomisoError' => null,
            ]));
        }

        $sync = $this->retirarInventarioLocalPorIdsDecomisosSirt($sirtRows);

        $rows = $this->fusionarRows($manualRows, $sirtRows);

        return view('decomisos-lenguas', array_merge($baseView, [
            'rows' => $rows,
            'error' => null,
            'invRetiradosDecomiso' => $sync['removed'],
            'invSyncDecomisoError' => $sync['error'],
        ]));
    }

    public function exportExcel(Request $request): StreamedResponse|RedirectResponse
    {
        $resolved = $this->resolverListadoDecomisos($request);

        if ($resolved['validator']->fails()) {
            return redirect()
                ->route('decomisos.lenguas', $request->query())
                ->withErrors($resolved['validator']);
        }

        $headers = [
            'Id producto',
            'Fecha registro',
            'Hora registro',
            'Nombre dictamen',
            'Nombre enfermedad',
            'Origen',
        ];

        $excelRows = [];
        foreach ($resolved['rows'] as $row) {
            $r = (array) $row;
            $idProd = isset($r['id_producto']) ? (string) $r['id_producto'] : (isset($r['ID_PRODUCTO']) ? (string) $r['ID_PRODUCTO'] : '');

            $hora = $r['hora_registro'] ?? null;
            if ($hora instanceof \DateTimeInterface) {
                $horaTxt = $hora->format('H:i:s');
            } else {
                $horaTxt = $hora !== null && $hora !== '' ? (string) $hora : '';
            }

            $fecha = $r['fecha_registro'] ?? null;
            if ($fecha instanceof \DateTimeInterface) {
                $fechaTxt = $fecha->format('Y-m-d');
            } else {
                $fechaTxt = $fecha !== null && $fecha !== '' ? (string) $fecha : '';
            }

            $origen = strtolower((string) ($r['origen'] ?? 'sirt')) === 'manual' ? 'Manual' : 'SIRT';

            $excelRows[] = [
                $idProd,
                $fechaTxt,
                $horaTxt,
                isset($r['nombre_dictamen']) ? (string) $r['nombre_dictamen'] : '',
                isset($r['nombre_enfermedad']) && $r['nombre_enfermedad'] !== null ? (string) $r['nombre_enfermedad'] : '',
                $origen,
            ];
        }

        $binary = SimpleXlsxWriter::build($headers, $excelRows, 'Decomisos');
        $filename = 'decomisos-lenguas-'.Carbon::now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(static function () use ($binary): void {
            echo $binary;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'id_producto' => ['required', 'string', 'max:80'],
                'nombre_dictamen' => ['required', 'string', 'max:255'],
                'nombre_enfermedad' => ['nullable', 'string', 'max:255'],
            ],
            [
                'id_producto.required' => 'Indique el id de producto a decomisar.',
                'nombre_dictamen.required' => 'Seleccione el nombre del dictamen.',
            ],
        );

        $idProducto = DespachoCodigoBarras::normalizarIdProducto(trim((string) $validated['id_producto']));
        if ($idProducto === '') {
            return back()
                ->withInput()
                ->withErrors(['id_producto' => 'El id de producto no es válido.']);
        }

        $nombreDictamen = trim((string) $validated['nombre_dictamen']);
        $nombreEnfermedad = isset($validated['nombre_enfermedad'])
            ? trim((string) $validated['nombre_enfermedad'])
            : '';
        if ($nombreEnfermedad === '') {
            $nombreEnfermedad = null;
        }

        try {
            $ok = false;
            DB::transaction(function () use ($request, $idProducto, $nombreDictamen, $nombreEnfermedad, &$ok): void {
                $ingreso = DespachoInventarioMatch::findAvailableRow($idProducto);
                if ($ingreso !== null) {
                    $ingreso = IngresoLenguaLocal::query()
                        ->whereKey($ingreso->id)
                        ->lockForUpdate()
                        ->first();
                }

                if ($ingreso === null) {
                    return;
                }

                $ahora = now();

                DecomisoManual::query()->create([
                    'id_producto' => $idProducto,
                    'fecha_registro' => $ahora->toDateString(),
                    'hora_registro' => $ahora->format('H:i:s'),
                    'nombre_dictamen' => $nombreDictamen,
                    'nombre_enfermedad' => $nombreEnfermedad,
                    'ingreso_lengua_local_id' => $ingreso->id,
                    'user_id' => $request->user()?->id,
                ]);

                $ingreso->despachado_at = $ahora;
                $ingreso->despacho_id = null;
                $ingreso->save();
                $ok = true;
            });

            if (! $ok) {
                return back()
                    ->withInput()
                    ->withErrors(['id_producto' => 'No hay una lengua disponible en inventario local con ese id de producto.']);
            }
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['id_producto' => 'No se pudo registrar el decomiso manual ni retirar del inventario.']);
        }

        return redirect()
            ->route('decomisos.lenguas')
            ->with('decomiso_manual_ok', "Decomiso manual registrado y retirado del inventario: {$idProducto}.");
    }

    /**
     * @return array{dictamenes: list<string>, enfermedades: list<string>}
     */
    private function catalogosDictamenEnfermedad(int $idParte): array
    {
        $dictamenes = DecomisoManual::query()
            ->whereNotNull('nombre_dictamen')
            ->where('nombre_dictamen', '!=', '')
            ->distinct()
            ->orderBy('nombre_dictamen')
            ->pluck('nombre_dictamen')
            ->map(static fn ($v): string => trim((string) $v))
            ->filter()
            ->values()
            ->all();

        $enfermedades = DecomisoManual::query()
            ->whereNotNull('nombre_enfermedad')
            ->where('nombre_enfermedad', '!=', '')
            ->distinct()
            ->orderBy('nombre_enfermedad')
            ->pluck('nombre_enfermedad')
            ->map(static fn ($v): string => trim((string) $v))
            ->filter()
            ->values()
            ->all();

        if ($this->sirtLookupEnabled()) {
            $connection = (string) config('decomisos.sirt_connection');
            if ($connection !== '') {
                try {
                    $sirtDictamenes = DB::connection($connection)->select(
                        <<<'SQL'
SELECT DISTINCT d.nombre AS nombre
FROM sai.inspeccion i
JOIN sai.dictamen d ON i.id_dictamen = d.id
WHERE i.id_parte_producto = ?
  AND d.nombre IS NOT NULL
  AND TRIM(d.nombre) <> ''
ORDER BY d.nombre
LIMIT 300
SQL,
                        [$idParte],
                    );

                    foreach ($sirtDictamenes as $row) {
                        $n = trim((string) (($row->nombre ?? '') ?: ''));
                        if ($n !== '') {
                            $dictamenes[] = $n;
                        }
                    }

                    $sirtEnfermedades = DB::connection($connection)->select(
                        <<<'SQL'
SELECT DISTINCT e.nombre AS nombre
FROM sai.inspeccion i
JOIN sai.enfermedad_detectada ed ON i.id = ed.id_inspeccion
JOIN sai.enfermedad e ON ed.id_enfermedad = e.id
WHERE i.id_parte_producto = ?
  AND e.nombre IS NOT NULL
  AND TRIM(e.nombre) <> ''
ORDER BY e.nombre
LIMIT 500
SQL,
                        [$idParte],
                    );

                    foreach ($sirtEnfermedades as $row) {
                        $n = trim((string) (($row->nombre ?? '') ?: ''));
                        if ($n !== '') {
                            $enfermedades[] = $n;
                        }
                    }
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }

        $dictamenes = collect($dictamenes)->unique(static fn (string $s): string => mb_strtolower($s))->sort()->values()->all();
        $enfermedades = collect($enfermedades)->unique(static fn (string $s): string => mb_strtolower($s))->sort()->values()->all();

        return [
            'dictamenes' => $dictamenes,
            'enfermedades' => $enfermedades,
        ];
    }

    /**
     * Misma lógica de filtros/listado que index, sin baja automática de inventario (para Excel).
     *
     * @return array{filters: array{q: string, fecha_desde: ?string, fecha_hasta: ?string}, rows: list<object>, validator: \Illuminate\Validation\Validator}
     */
    private function resolverListadoDecomisos(Request $request): array
    {
        $idParte = (int) config('decomisos.id_parte_producto');
        $idDictamen = (int) config('decomisos.id_dictamen');

        $ahora = Carbon::now();
        $inicioMes = $ahora->copy()->startOfMonth()->toDateString();
        $finMes = $ahora->copy()->endOfMonth()->toDateString();

        $qRaw = substr($request->string('q')->trim()->toString(), 0, 200);
        $tieneBusquedaLibre = str_replace(['%', '_'], '', $qRaw) !== '';

        $fechaDesdeIn = $request->filled('fecha_desde') ? (string) $request->input('fecha_desde') : null;
        $fechaHastaIn = $request->filled('fecha_hasta') ? (string) $request->input('fecha_hasta') : null;

        if (! $tieneBusquedaLibre && $fechaDesdeIn === null && $fechaHastaIn === null) {
            $fechaDesdeIn = $inicioMes;
            $fechaHastaIn = $finMes;
        }

        if ($tieneBusquedaLibre) {
            $fechaDesdeIn = null;
            $fechaHastaIn = null;
        }

        $filters = [
            'q' => $qRaw,
            'fecha_desde' => $fechaDesdeIn,
            'fecha_hasta' => $fechaHastaIn,
        ];

        $validator = Validator::make(
            [
                'q' => $filters['q'],
                'fecha_desde' => $filters['fecha_desde'],
                'fecha_hasta' => $filters['fecha_hasta'],
            ],
            [
                'q' => ['nullable', 'string', 'max:200'],
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date'],
            ],
        );

        $validator->after(function ($v) use ($filters): void {
            $d1 = $filters['fecha_desde'] ?? null;
            $d2 = $filters['fecha_hasta'] ?? null;
            if (is_string($d1) && $d1 !== '' && is_string($d2) && $d2 !== '' && $d1 > $d2) {
                $v->errors()->add('fecha_hasta', 'La fecha hasta debe ser mayor o igual que la fecha desde.');
            }
        });

        $manualRows = $this->filasManualesComoRows($filters);

        if ($validator->fails()) {
            return ['filters' => $filters, 'rows' => $manualRows, 'validator' => $validator];
        }

        if (! $this->sirtLookupEnabled()) {
            return ['filters' => $filters, 'rows' => $manualRows, 'validator' => $validator];
        }

        $connection = (string) config('decomisos.sirt_connection');
        if ($connection === '') {
            return ['filters' => $filters, 'rows' => $manualRows, 'validator' => $validator];
        }

        $sql = <<<'SQL'
SELECT
    i.id_producto,
    i.fecha_registro,
    i.hora_registro,
    d.nombre AS nombre_dictamen,
    e.nombre AS nombre_enfermedad
FROM sai.inspeccion i
JOIN sai.dictamen d ON i.id_dictamen = d.id
LEFT JOIN sai.enfermedad_detectada ed ON i.id = ed.id_inspeccion
LEFT JOIN sai.enfermedad e ON ed.id_enfermedad = e.id
WHERE i.id_parte_producto = ?
  AND i.id_dictamen = ?
SQL;

        $bindings = [$idParte, $idDictamen];

        $q = str_replace(['%', '_'], '', $filters['q']);
        if ($q !== '') {
            $pat = '%'.$q.'%';
            $sql .= ' AND (
    i.id_producto::text ILIKE ?
    OR d.nombre ILIKE ?
    OR COALESCE(e.nombre, \'\') ILIKE ?
)';
            $bindings[] = $pat;
            $bindings[] = $pat;
            $bindings[] = $pat;
        }

        if ($q === '') {
            if (! empty($filters['fecha_desde'])) {
                $sql .= ' AND i.fecha_registro >= ?';
                $bindings[] = $filters['fecha_desde'];
            }

            if (! empty($filters['fecha_hasta'])) {
                $sql .= ' AND i.fecha_registro <= ?';
                $bindings[] = $filters['fecha_hasta'];
            }
        }

        $sql .= ' ORDER BY i.id DESC LIMIT 500';

        try {
            $sirtRows = DB::connection($connection)->select($sql, $bindings);
        } catch (Throwable $e) {
            report($e);

            return ['filters' => $filters, 'rows' => $manualRows, 'validator' => $validator];
        }

        return [
            'filters' => $filters,
            'rows' => $this->fusionarRows($manualRows, $sirtRows),
            'validator' => $validator,
        ];
    }

    /**
     * @param  array{q: string, fecha_desde: ?string, fecha_hasta: ?string}  $filters
     * @return list<object>
     */
    private function filasManualesComoRows(array $filters): array
    {
        $query = DecomisoManual::query()->orderByDesc('id');

        $q = str_replace(['%', '_'], '', $filters['q'] ?? '');
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($w) use ($like): void {
                $w->where('id_producto', 'like', $like)
                    ->orWhere('nombre_dictamen', 'like', $like)
                    ->orWhere('nombre_enfermedad', 'like', $like);
            });
        } else {
            if (! empty($filters['fecha_desde'])) {
                $query->whereDate('fecha_registro', '>=', $filters['fecha_desde']);
            }
            if (! empty($filters['fecha_hasta'])) {
                $query->whereDate('fecha_registro', '<=', $filters['fecha_hasta']);
            }
        }

        return $query->limit(500)->get()->map(static function (DecomisoManual $row): object {
            return (object) [
                'id_producto' => $row->id_producto,
                'fecha_registro' => $row->fecha_registro,
                'hora_registro' => $row->hora_registro,
                'nombre_dictamen' => $row->nombre_dictamen,
                'nombre_enfermedad' => $row->nombre_enfermedad,
                'origen' => 'manual',
            ];
        })->all();
    }

    /**
     * @param  list<object>  $manualRows
     * @param  array<int, object>  $sirtRows
     * @return list<object>
     */
    private function fusionarRows(array $manualRows, array $sirtRows): array
    {
        $taggedSirt = array_map(static function (object $row): object {
            $r = (array) $row;
            $r['origen'] = 'sirt';

            return (object) $r;
        }, $sirtRows);

        return array_values(array_merge($manualRows, $taggedSirt));
    }

    /**
     * Cada id que aparece como decomisado en SIRT se da de baja en la réplica local (mismo criterio que despacho).
     *
     * @param  array<int, object>  $rows
     * @return array{removed: int, error: string|null}
     */
    private function retirarInventarioLocalPorIdsDecomisosSirt(array $rows): array
    {
        if ($rows === []) {
            return ['removed' => 0, 'error' => null];
        }

        $ids = collect($rows)
            ->map(function (object $row): string {
                $r = (array) $row;
                $raw = isset($r['id_producto']) ? (string) $r['id_producto'] : (isset($r['ID_PRODUCTO']) ? (string) $r['ID_PRODUCTO'] : '');

                return DespachoCodigoBarras::normalizarIdProducto(trim($raw));
            })
            ->filter(static fn (string $s): bool => $s !== '')
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return ['removed' => 0, 'error' => null];
        }

        $removed = 0;

        try {
            DB::transaction(function () use ($ids, &$removed): void {
                $realizadoAt = now();
                foreach ($ids as $idProducto) {
                    $row = DespachoInventarioMatch::findAvailableRow($idProducto);
                    if ($row !== null) {
                        $row = IngresoLenguaLocal::query()
                            ->whereKey($row->id)
                            ->lockForUpdate()
                            ->first();
                    }

                    if ($row === null) {
                        continue;
                    }

                    $row->despachado_at = $realizadoAt;
                    $row->despacho_id = null;
                    $row->save();
                    $removed++;
                }
            });
        } catch (Throwable $e) {
            report($e);

            return ['removed' => 0, 'error' => 'No se pudo actualizar el inventario local tras la consulta SIRT.'];
        }

        return ['removed' => $removed, 'error' => null];
    }
}

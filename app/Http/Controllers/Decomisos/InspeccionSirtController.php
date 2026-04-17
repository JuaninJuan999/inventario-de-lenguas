<?php

namespace App\Http\Controllers\Decomisos;

use App\Http\Controllers\Controller;
use App\Models\IngresoLenguaLocal;
use App\Support\DespachoCodigoBarras;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
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

        $hoy = Carbon::now()->toDateString();

        $qRaw = substr($request->string('q')->trim()->toString(), 0, 200);
        $tieneBusquedaLibre = str_replace(['%', '_'], '', $qRaw) !== '';

        $fechaDesdeIn = $request->filled('fecha_desde') ? (string) $request->input('fecha_desde') : null;
        $fechaHastaIn = $request->filled('fecha_hasta') ? (string) $request->input('fecha_hasta') : null;

        if (! $tieneBusquedaLibre && $fechaDesdeIn === null && $fechaHastaIn === null) {
            $fechaDesdeIn = $hoy;
            $fechaHastaIn = $hoy;
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

        $baseView = [
            'filters' => $filters,
            'idParteProducto' => $idParte,
            'idDictamen' => $idDictamen,
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
                'rows' => [],
                'error' => null,
                'invRetiradosDecomiso' => 0,
                'invSyncDecomisoError' => null,
            ]))->withErrors($validator);
        }

        if (! $this->sirtLookupEnabled()) {
            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => 'Configure la conexión SIRT en .env (POSTGRES_HOST, POSTGRES_DB, POSTGRES_USER, POSTGRES_PASSWORD).',
                'invRetiradosDecomiso' => 0,
                'invSyncDecomisoError' => null,
            ]));
        }

        $connection = (string) config('decomisos.sirt_connection');
        if ($connection === '') {
            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => [],
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
            $rows = DB::connection($connection)->select($sql, $bindings);
        } catch (Throwable $e) {
            report($e);

            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => 'No se pudo consultar SIRT. Revise red/VPN, credenciales y que existan los esquemas sai.*.',
                'invRetiradosDecomiso' => 0,
                'invSyncDecomisoError' => null,
            ]));
        }

        $sync = $this->retirarInventarioLocalPorIdsDecomisosSirt($rows);

        return view('decomisos-lenguas', array_merge($baseView, [
            'rows' => $rows,
            'error' => null,
            'invRetiradosDecomiso' => $sync['removed'],
            'invSyncDecomisoError' => $sync['error'],
        ]));
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
                    $row = IngresoLenguaLocal::query()
                        ->sinDespachar()
                        ->where('id_producto', $idProducto)
                        ->orderByDesc('imported_at')
                        ->orderByDesc('id')
                        ->first();

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

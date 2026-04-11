<?php

namespace App\Http\Controllers\Decomisos;

use App\Http\Controllers\Controller;
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

        $filters = [
            'id_producto' => substr($request->string('id_producto')->trim()->toString(), 0, 80),
            'fecha_desde' => $request->filled('fecha_desde') ? $request->input('fecha_desde') : null,
            'fecha_hasta' => $request->filled('fecha_hasta') ? $request->input('fecha_hasta') : null,
            'enfermedad' => substr($request->string('enfermedad')->trim()->toString(), 0, 200),
        ];

        $baseView = [
            'filters' => $filters,
            'idParteProducto' => $idParte,
            'idDictamen' => $idDictamen,
        ];

        $validator = Validator::make(
            [
                'id_producto' => $filters['id_producto'],
                'fecha_desde' => $filters['fecha_desde'],
                'fecha_hasta' => $filters['fecha_hasta'],
                'enfermedad' => $filters['enfermedad'],
            ],
            [
                'id_producto' => ['nullable', 'string', 'max:80'],
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date'],
                'enfermedad' => ['nullable', 'string', 'max:200'],
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
            ]))->withErrors($validator);
        }

        if (! $this->sirtLookupEnabled()) {
            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => 'Configure la conexión SIRT en .env (POSTGRES_HOST, POSTGRES_DB, POSTGRES_USER, POSTGRES_PASSWORD).',
            ]));
        }

        $connection = (string) config('decomisos.sirt_connection');
        if ($connection === '') {
            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => 'Conexión SIRT no configurada (decomisos.sirt_connection).',
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

        $idProducto = str_replace(['%', '_'], '', $filters['id_producto']);
        if ($idProducto !== '') {
            $sql .= ' AND i.id_producto ILIKE ?';
            $bindings[] = '%'.$idProducto.'%';
        }

        if (! empty($filters['fecha_desde'])) {
            $sql .= ' AND i.fecha_registro >= ?';
            $bindings[] = $filters['fecha_desde'];
        }

        if (! empty($filters['fecha_hasta'])) {
            $sql .= ' AND i.fecha_registro <= ?';
            $bindings[] = $filters['fecha_hasta'];
        }

        $enfermedad = str_replace(['%', '_'], '', $filters['enfermedad']);
        if ($enfermedad !== '') {
            $sql .= ' AND COALESCE(e.nombre, \'\') ILIKE ?';
            $bindings[] = '%'.$enfermedad.'%';
        }

        $sql .= ' ORDER BY i.id DESC LIMIT 500';

        try {
            $rows = DB::connection($connection)->select($sql, $bindings);
        } catch (Throwable $e) {
            report($e);

            return view('decomisos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => 'No se pudo consultar SIRT. Revise red/VPN, credenciales y que existan los esquemas sai.*.',
            ]));
        }

        return view('decomisos-lenguas', array_merge($baseView, [
            'rows' => $rows,
            'error' => null,
        ]));
    }
}

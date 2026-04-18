<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class VehiculoAsignadoLookupController extends Controller
{
    /**
     * Indica si debe usarse la conexión PostgreSQL para el buscador de despacho.
     */
    protected function colbeefLookupEnabled(): bool
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
     * Lista vehículos asignados (consulta equivalente a la de trazabilidad).
     *
     * @queryParam focus string Obligatorio: empresa | placa | conductor. La búsqueda solo aplica a esa columna.
     * @queryParam search string Opcional. Filtro ILIKE solo sobre la columna indicada en focus.
     */
    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->colbeefLookupEnabled()) {
            return response()->json([
                'ok' => false,
                'message' => 'Configure PostgreSQL en .env: POSTGRES_HOST, POSTGRES_DB, POSTGRES_USER y POSTGRES_PASSWORD (o active COLBEEF_DB_ENABLED=true con COLBEEF_DB_*).',
                'data' => [],
            ]);
        }

        $connection = config('despacho.colbeef_connection');
        if (! is_string($connection) || $connection === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Conexión Colbeef no configurada.',
                'data' => [],
            ], 500);
        }

        $focus = strtolower(trim((string) $request->get('focus', '')));
        if ($focus === '') {
            $focus = strtolower(trim((string) $request->header('X-Despacho-Lookup-Focus', '')));
        }
        if (! in_array($focus, ['empresa', 'placa', 'conductor'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'No se recibió el tipo de búsqueda (empresa, placa o conductor). Cierre el cuadro y ábralo de nuevo con el botón correspondiente, o recargue la página (Ctrl+F5).',
                'data' => [],
            ], 422);
        }

        $search = $request->string('search')->trim()->toString();
        if (strlen($search) > 120) {
            return response()->json([
                'ok' => false,
                'message' => 'La búsqueda admite como máximo 120 caracteres.',
                'data' => [],
            ], 422);
        }

        $userName = (string) config('despacho.vehiculo_registra_username', 'porteria52');
        if ($userName === '') {
            $userName = 'porteria52';
        }
        if (config('despacho.vehiculo_registra_use_auth_username') === true) {
            $authUser = (string) ($request->user()?->username ?? '');
            if ($authUser !== '') {
                $userName = $authUser;
            }
        }

        // Evita comodines accidentales en ILIKE
        $search = str_replace(['%', '_'], '', $search);

        $sql = <<<'SQL'
SELECT
    va.id,
    va.placa_vehiculo,
    va.user_name AS usuario_registra,
    p.nombres AS nombre_conductor,
    e.nombre AS empresa
FROM trazabilidad_proceso.vehiculo_asignado va
LEFT JOIN a_recursos_humanos.a_persona p ON va.id_conductor = p.id
LEFT JOIN a_organizaciones.a_empresa_persona ep ON p.id = ep.id_persona
LEFT JOIN a_organizaciones.a_empresa e ON ep.id_empresa = e.id
WHERE va.user_name = ?
SQL;

        $bindings = [$userName];

        if ($search !== '') {
            $like = '%'.$search.'%';
            if ($focus === 'empresa') {
                $sql .= ' AND COALESCE(e.nombre, \'\') ILIKE ?';
                $bindings[] = $like;
            } elseif ($focus === 'placa') {
                $sql .= ' AND va.placa_vehiculo ILIKE ?';
                $bindings[] = $like;
            } else {
                $sql .= ' AND COALESCE(p.nombres, \'\') ILIKE ?';
                $bindings[] = $like;
            }
        }

        $sql .= ' ORDER BY va.id DESC LIMIT 100';

        try {
            $rows = DB::connection($connection)->select($sql, $bindings);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo consultar la base de datos. Revise COLBEEF_DB_* y que existan los esquemas/tablas.',
                'data' => [],
            ], 503);
        }

        $data = array_map(static function ($row): array {
            $a = (array) $row;

            return [
                'id' => (int) ($a['id'] ?? 0),
                'placa_vehiculo' => $a['placa_vehiculo'] ?? null,
                'usuario_registra' => $a['usuario_registra'] ?? null,
                'nombre_conductor' => $a['nombre_conductor'] ?? null,
                'empresa' => $a['empresa'] ?? null,
            ];
        }, $rows);

        return response()->json([
            'ok' => true,
            'message' => null,
            'focus' => $focus,
            'filtro_user_name' => $userName,
            'data' => $data,
        ]);
    }
}

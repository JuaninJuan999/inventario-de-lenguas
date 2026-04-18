<?php

namespace App\Services\IngresosLenguas;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Consulta a SIRT/Colbeef equivalente a la vista de Ingresos de Lenguas.
 */
class IngresosLenguasConsultaSirt
{
    /** @var list<string> */
    protected const ALLOWED_FECHA_COLUMNS = [
        'fecha',
        'fecha_plan',
        'fecha_registro',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
        'fecha_turno',
        'fecha_inicio',
        'fecha_fin',
        'dia',
    ];

    public function fechaHoyOperacion(): string
    {
        $tz = trim((string) config('ingresos_lenguas.fecha_operacion_timezone', ''));
        if ($tz === '') {
            return now()->toDateString();
        }

        try {
            return Carbon::now($tz)->toDateString();
        } catch (Throwable $e) {
            report($e);

            return now()->toDateString();
        }
    }

    public function externalPostgresEnabled(): bool
    {
        if (filter_var(env('COLBEEF_DB_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (filter_var(env('POSTGRES_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return Str::of((string) env('POSTGRES_HOST', ''))->trim()->isNotEmpty();
    }

    protected function resolveFechaColumn(string $configKey, string $default = 'fecha'): string
    {
        $c = (string) config($configKey, $default);

        return in_array($c, self::ALLOWED_FECHA_COLUMNS, true) ? $c : 'fecha';
    }

    public function buildTurnoFechaExistsPredicate(bool $conRangoFecha): string
    {
        $bind = strtolower(trim((string) config('ingresos_lenguas.turno_fecha_bind', 'plan_faena')));

        if ($bind === 'plan_faena_turno') {
            $col = $this->resolveFechaColumn('ingresos_lenguas.plan_faena_turno_fecha_column', 'fecha_registro');
            $fechaSql = $conRangoFecha
                ? "\n      AND (pft_f.{$col})::date >= CAST(? AS date)\n      AND (pft_f.{$col})::date <= CAST(? AS date)"
                : '';

            return <<<SQL
EXISTS (
    SELECT 1
    FROM trazabilidad_proceso.plan_faena_producto pfp_f
    INNER JOIN trazabilidad_proceso.plan_faena_turno pft_f
        ON pfp_f.id_plan_faena = pft_f.id_plan_faena
    WHERE pfp_f.id_producto = ins.id_producto{$fechaSql}
)
SQL;
        }

        if ($bind === 'turno') {
            $col = $this->resolveFechaColumn('ingresos_lenguas.turno_tabla_fecha_column', 'fecha');
            $fechaSql = $conRangoFecha
                ? "\n      AND (tur_f.{$col})::date >= CAST(? AS date)\n      AND (tur_f.{$col})::date <= CAST(? AS date)"
                : '';

            return <<<SQL
EXISTS (
    SELECT 1
    FROM trazabilidad_proceso.plan_faena_producto pfp_f
    INNER JOIN trazabilidad_proceso.plan_faena_turno pft_f
        ON pfp_f.id_plan_faena = pft_f.id_plan_faena
    INNER JOIN trazabilidad_proceso.plan_faena pf_f
        ON pf_f.id = pfp_f.id_plan_faena
    INNER JOIN trazabilidad_proceso.turno tur_f
        ON tur_f.id = pf_f.id_turno
    WHERE pfp_f.id_producto = ins.id_producto{$fechaSql}
)
SQL;
        }

        $col = $this->resolveFechaColumn('ingresos_lenguas.plan_faena_fecha_column', 'fecha_plan');
        $fechaSql = $conRangoFecha
            ? "\n      AND (pf_f.{$col})::date >= CAST(? AS date)\n      AND (pf_f.{$col})::date <= CAST(? AS date)"
            : '';

        return <<<SQL
EXISTS (
    SELECT 1
    FROM trazabilidad_proceso.plan_faena_producto pfp_f
    INNER JOIN trazabilidad_proceso.plan_faena_turno pft_f
        ON pfp_f.id_plan_faena = pft_f.id_plan_faena
    INNER JOIN trazabilidad_proceso.plan_faena pf_f
        ON pf_f.id = pfp_f.id_plan_faena
    WHERE pfp_f.id_producto = ins.id_producto{$fechaSql}
)
SQL;
    }

    /**
     * Fecha de turno usada en trazabilidad (mismo origen que el EXISTS de {@see buildTurnoFechaExistsPredicate}),
     * como subconsulta escalar para enlazar cada fila a una fecha Y-m-d coherente con Ingresos.
     *
     * @param  string  $idProductoExpr  expresión SQL del id_producto (p. ej. i.id_producto)
     */
    public function buildTurnoFechaReferenciaScalarSubquery(string $idProductoExpr): string
    {
        $bind = strtolower(trim((string) config('ingresos_lenguas.turno_fecha_bind', 'plan_faena')));

        if ($bind === 'plan_faena_turno') {
            $col = $this->resolveFechaColumn('ingresos_lenguas.plan_faena_turno_fecha_column', 'fecha_registro');

            return <<<SQL
(
    SELECT (pft_ref.{$col})::date
    FROM trazabilidad_proceso.plan_faena_producto pfp_ref
    INNER JOIN trazabilidad_proceso.plan_faena_turno pft_ref
        ON pfp_ref.id_plan_faena = pft_ref.id_plan_faena
    WHERE pfp_ref.id_producto = {$idProductoExpr}
    ORDER BY pfp_ref.id_plan_faena DESC NULLS LAST, (pft_ref.{$col})::date DESC NULLS LAST
    LIMIT 1
)
SQL;
        }

        if ($bind === 'turno') {
            $col = $this->resolveFechaColumn('ingresos_lenguas.turno_tabla_fecha_column', 'fecha');

            return <<<SQL
(
    SELECT (tur_ref.{$col})::date
    FROM trazabilidad_proceso.plan_faena_producto pfp_ref
    INNER JOIN trazabilidad_proceso.plan_faena_turno pft_ref
        ON pfp_ref.id_plan_faena = pft_ref.id_plan_faena
    INNER JOIN trazabilidad_proceso.plan_faena pf_ref_tur
        ON pf_ref_tur.id = pfp_ref.id_plan_faena
    INNER JOIN trazabilidad_proceso.turno tur_ref
        ON tur_ref.id = pf_ref_tur.id_turno
    WHERE pfp_ref.id_producto = {$idProductoExpr}
    ORDER BY pfp_ref.id_plan_faena DESC NULLS LAST, pf_ref_tur.id_turno DESC NULLS LAST
    LIMIT 1
)
SQL;
        }

        $col = $this->resolveFechaColumn('ingresos_lenguas.plan_faena_fecha_column', 'fecha_plan');

        return <<<SQL
(
    SELECT (pf_ref.{$col})::date
    FROM trazabilidad_proceso.plan_faena_producto pfp_ref
    INNER JOIN trazabilidad_proceso.plan_faena_turno pft_ref
        ON pfp_ref.id_plan_faena = pft_ref.id_plan_faena
    INNER JOIN trazabilidad_proceso.plan_faena pf_ref
        ON pf_ref.id = pfp_ref.id_plan_faena
    WHERE pfp_ref.id_producto = {$idProductoExpr}
    ORDER BY pfp_ref.id_plan_faena DESC NULLS LAST, pf_ref.id_turno DESC NULLS LAST
    LIMIT 1
)
SQL;
    }

    /**
     * @param  array{id_producto: string, propietario: string}  $filters
     * @return array{rows: array<int, object>, exception: Throwable|null}
     */
    public function consultar(
        string $connection,
        string $hoy,
        bool $primeraCarga,
        string $fechaDesdeTrim,
        string $fechaHastaTrim,
        array $filters,
        bool $incluirIdInsensibilizacion = false,
    ): array {
        $idParteProducto = (int) config('ingresos_lenguas.id_parte_producto', 4);
        $insLimit = max(100, min(10000, (int) config('ingresos_lenguas.consulta_insensibilizacion_limit', 2000)));

        $conRangoFechaEnExists = $primeraCarga
            || ($fechaDesdeTrim !== '' && $fechaHastaTrim !== '');
        $fechaSqlDesde = $primeraCarga ? $hoy : $fechaDesdeTrim;
        $fechaSqlHasta = $primeraCarga ? $hoy : $fechaHastaTrim;

        $existBindings = [];
        if ($conRangoFechaEnExists) {
            $existBindings[] = $fechaSqlDesde;
            $existBindings[] = $fechaSqlHasta;
        }

        $turnoExists = $this->buildTurnoFechaExistsPredicate($conRangoFechaEnExists);

        $cteExtraWhere = '';
        $cteExtraBindings = [];

        $idProductoBusqueda = trim((string) ($filters['id_producto'] ?? ''));
        if ($idProductoBusqueda !== '') {
            $cteExtraWhere .= ' AND ins.id_producto::text ILIKE ?';
            $cteExtraBindings[] = '%'.$idProductoBusqueda.'%';
        }

        $propietarioBusqueda = trim((string) ($filters['propietario'] ?? ''));
        if ($propietarioBusqueda !== '') {
            $cteExtraWhere .= <<<'SQL'

    AND EXISTS (
        SELECT 1
        FROM trazabilidad_proceso.producto_empresa pe_pf
        INNER JOIN organizaciones.empresa e_pf ON e_pf.id = pe_pf.id_empresa
        WHERE pe_pf.id_producto = ins.id_producto
          AND COALESCE(e_pf.nombre, '') ILIKE ?
    )
SQL;
            $cteExtraBindings[] = '%'.$propietarioBusqueda.'%';
        }

        $colIdIns = $incluirIdInsensibilizacion ? "    i.id AS insensibilizacion_id,\n" : '';
        $colFechaTurnoRef = '';
        if ($incluirIdInsensibilizacion) {
            $fechaTurnoSql = $this->buildTurnoFechaReferenciaScalarSubquery('i.id_producto');
            $colFechaTurnoRef = "    {$fechaTurnoSql} AS fecha_turno_referencia,\n";
        }

        $sql = <<<SQL
WITH ins_filtrada AS (
    SELECT ins.id, ins.id_producto, ins.fecha_registro, ins.hora_registro
    FROM trazabilidad_proceso.insensibilizacion ins
    WHERE {$turnoExists}{$cteExtraWhere}
    ORDER BY ins.id DESC
    LIMIT {$insLimit}
)
SELECT
{$colIdIns}{$colFechaTurnoRef}    i.id_producto,
    i.fecha_registro,
    i.hora_registro,
    prop.nombre AS propietario,
    concat_ws(
        ' / ',
        dest.nombre_destino,
        dest.nombre_empresa,
        dest.nombre_sucursal,
        dest.direccion_sucursal
    ) AS destino,
    pp_peso.peso AS peso
FROM ins_filtrada i
LEFT JOIN LATERAL (
    SELECT e.nombre
    FROM trazabilidad_proceso.producto_empresa pe
    INNER JOIN organizaciones.empresa e ON pe.id_empresa = e.id
    WHERE pe.id_producto = i.id_producto
    ORDER BY pe.fecha_registro DESC NULLS LAST, pe.hora_registro DESC NULLS LAST
    LIMIT 1
) prop ON true
LEFT JOIN LATERAL (
    SELECT
        t5.nombre AS nombre_destino,
        t3.nombre AS nombre_empresa,
        t4.nombre AS nombre_sucursal,
        t4.direccion AS direccion_sucursal
    FROM trazabilidad_proceso.parte_producto_empresa t1
    INNER JOIN trazabilidad_proceso.parte_producto_empresa_local t2
        ON t1.id = t2.id_parte_producto_empresa
    INNER JOIN organizaciones.empresa t3
        ON t1.id_empresa = t3.id
    INNER JOIN organizaciones.sucursal t4
        ON t2.id_local = t4.id
    INNER JOIN trazabilidad_proceso.destino t5
        ON t4.id_destino = t5.id
    WHERE t1.id_parte_producto = ?
        AND t1.id_producto = i.id_producto
    ORDER BY t1.id DESC
    LIMIT 1
) dest ON true
LEFT JOIN LATERAL (
    SELECT ppp.peso
    FROM trazabilidad_proceso.proceso_parte_producto_peso ppp
    WHERE ppp.id_parte_producto = ?
        AND ppp.id_producto = i.id_producto
    ORDER BY ppp.id_proceso DESC, ppp.id_parte_producto DESC, ppp.id_producto DESC
    LIMIT 1
) pp_peso ON true
ORDER BY i.id DESC
SQL;

        try {
            $rows = DB::connection($connection)->select($sql, array_merge(
                $existBindings,
                $cteExtraBindings,
                [
                    $idParteProducto,
                    $idParteProducto,
                ],
            ));

            return ['rows' => $rows, 'exception' => null];
        } catch (Throwable $e) {
            report($e);

            return ['rows' => [], 'exception' => $e];
        }
    }
}

<?php

namespace App\Services\Despacho;

use App\Models\DespachoOperadorPlaca;
use App\Support\DespachoInventarioMatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Lecturas sobre trazabilidad_proceso.parte_producto_vehiculo (Colbeef / PostgreSQL externo).
 *
 * Consulta de referencia (exploración pgAdmin):
 *   SELECT ppv.*, va.placa_vehiculo
 *   FROM trazabilidad_proceso.parte_producto_vehiculo ppv
 *   JOIN trazabilidad_proceso.vehiculo_asignado va ON ppv.id_vehiculo_asignado = va.id
 *   WHERE ppv.id_parte_producto = 4 ORDER BY ppv.id DESC LIMIT 1000;
 *
 * Esta aplicación usa la misma base (tablas + JOIN), pero para el checklist aplica siempre:
 *   - ppv.producto_entregado = config despacho.ppv_producto_entregado_valor (env DESPACHO_PPV_PRODUCTO_ENTREGADO, por defecto true),
 *   - opcional DATE(ppv.fecha_registro) = hoy (APP_TIMEZONE),
 *   - opcional filtro user_name en va.user_name o ppv.user_name (config),
 *   - LEFT JOIN persona/empresa solo para mostrar operador/conductor en pantalla,
 *   - sin LIMIT en SQL (el día filtra volumen).
 * La pantalla “Seleccionar placa” agrupa por vehículo: una fila por placa; “Pendientes” = cantidad de id_producto distintos
 * (por vehículo se conserva la fila ppv con id más alto cuando Colbeef duplica el mismo código).
 * El checklist muestra una fila por id_producto (misma regla de deduplicación).
 */
class DespachoParteProductoVehiculoConsulta
{
    protected function colbeefEnabled(): bool
    {
        if (filter_var(env('COLBEEF_DB_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (filter_var(env('POSTGRES_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return Str::of((string) env('POSTGRES_HOST', ''))->trim()->isNotEmpty();
    }

    protected function connectionName(): ?string
    {
        $connection = config('despacho.colbeef_connection');

        return is_string($connection) && $connection !== '' ? $connection : null;
    }

    protected function usernameFiltroVehiculo(): string
    {
        $userName = (string) config('despacho.vehiculo_registra_username', 'porteria52');

        return $userName === '' ? 'porteria52' : $userName;
    }

    /** id_parte_producto para despacho checklist (habitualmente 4). */
    public function idParteProducto(): int
    {
        return max(1, (int) config('despacho.id_parte_producto_vehiculo', 4));
    }

    /** Valor booleano enlazado a SQL: `AND ppv.producto_entregado = ?`. */
    protected function valorProductoEntregadoSql(): bool
    {
        return (bool) config('despacho.ppv_producto_entregado_valor', true);
    }

    /**
     * Colbeef puede devolver varias filas ppv por el mismo id_producto y vehículo; se conserva la de ppv.id mayor.
     *
     * @param  list<object>  $rows  Filas con id_vehiculo_asignado, id_producto, ppv_id
     * @return list<object>
     */
    protected function deduplicarFilasColbeefPorVehiculoYProducto(array $rows): array
    {
        $mejor = [];
        foreach ($rows as $row) {
            $a = (array) $row;
            $vid = (int) ($a['id_vehiculo_asignado'] ?? 0);
            $prod = trim((string) ($a['id_producto'] ?? ''));
            $ppvId = (int) ($a['ppv_id'] ?? 0);
            if ($vid < 1 || $prod === '') {
                continue;
            }
            $k = $vid."\t".$prod;
            if (! isset($mejor[$k])) {
                $mejor[$k] = $row;

                continue;
            }
            $prev = (array) $mejor[$k];
            if ($ppvId > (int) ($prev['ppv_id'] ?? 0)) {
                $mejor[$k] = $row;
            }
        }

        return array_values($mejor);
    }

    /**
     * Una línea de checklist por id_producto (ppv.id más alto gana).
     *
     * @param  list<object{ppv_id: int, id_producto: string}>  $lineas
     * @return list<object{ppv_id: int, id_producto: string}>
     */
    protected function deduplicarLineasPpvPorIdProductoMaxPpv(array $lineas): array
    {
        $mejor = [];
        foreach ($lineas as $line) {
            $pid = trim($line->id_producto);
            if ($pid === '') {
                continue;
            }
            if (! isset($mejor[$pid]) || $line->ppv_id > $mejor[$pid]->ppv_id) {
                $mejor[$pid] = $line;
            }
        }
        $out = array_values($mejor);
        usort($out, static function (object $a, object $b): int {
            return strcmp($a->id_producto, $b->id_producto);
        });

        return $out;
    }

    /** Fecha calendario “hoy” según timezone de la aplicación (Y-m-d). */
    protected function fechaRegistroReferenciaHoy(): string
    {
        return now()->timezone(config('app.timezone'))->format('Y-m-d');
    }

    /** Solo SQL base Colbeef (sin fecha, sin user_name en SQL, sin filtros PHP placas/inventario). */
    protected function modoSoloConsultaColbeef(): bool
    {
        return (bool) config('despacho.ppv_modo_solo_consulta_colbeef', false);
    }

    protected function debeAplicarFiltroUserNameVehiculo(): bool
    {
        if ($this->modoSoloConsultaColbeef()) {
            return false;
        }

        return (bool) config('despacho.filtrar_ppv_por_user_name_vehiculo', false);
    }

    protected function debeFiltrarFechaRegistroHoy(): bool
    {
        if ($this->modoSoloConsultaColbeef()) {
            return false;
        }

        return (bool) config('despacho.ppv_filtrar_fecha_registro_hoy', true);
    }

    protected function debeFiltrarSoloInventarioLocal(): bool
    {
        if ($this->modoSoloConsultaColbeef()) {
            return false;
        }

        return (bool) config('despacho.ppv_solo_con_inventario_local_disponible', true);
    }

    protected function debeFiltrarPorCatalogoLocalPlacas(): bool
    {
        if ($this->modoSoloConsultaColbeef()) {
            return false;
        }

        return (bool) config('despacho.ppv_filtrar_placa_catalogo_local', true);
    }

    /** Normaliza placa para comparar Colbeef vs catálogo local (mayúsculas, sin espacios). */
    public static function normalizarPlacaVehiculo(?string $placa): string
    {
        $t = strtoupper(trim((string) $placa));

        return preg_replace('/\s+/u', '', $t) ?? '';
    }

    /**
     * Catálogo de placas autorizadas en BD local (tabla despacho_operador_placas).
     *
     * @return array<string, true>|null null si no se aplica filtro (opción off o tabla vacía).
     */
    protected function catalogoPlacasLocalNormalizado(): ?array
    {
        if (! $this->debeFiltrarPorCatalogoLocalPlacas()) {
            return null;
        }

        if (! DespachoOperadorPlaca::query()->exists()) {
            return null;
        }

        $keys = [];
        foreach (DespachoOperadorPlaca::query()->pluck('placa') as $placa) {
            $k = self::normalizarPlacaVehiculo((string) $placa);
            if ($k !== '') {
                $keys[$k] = true;
            }
        }

        return $keys !== [] ? $keys : null;
    }

    /**
     * Restringe ppv al día actual usando fecha_registro en Colbeef.
     *
     * @param  list<mixed>  $bindings
     */
    protected function anexarSqlFechaRegistroHoy(string &$sql, array &$bindings): void
    {
        if ($this->modoSoloConsultaColbeef()) {
            return;
        }

        if (! $this->debeFiltrarFechaRegistroHoy()) {
            return;
        }

        $sql .= <<<'SQL'
 AND ppv.fecha_registro IS NOT NULL AND DATE(ppv.fecha_registro) = CAST(? AS DATE)
SQL;
        $bindings[] = $this->fechaRegistroReferenciaHoy();
    }

    /** vehiculo → va.user_name; parte_producto → ppv.user_name */
    protected function usernameColbeefFiltraPorParteProducto(): bool
    {
        $v = strtolower(trim((string) config('despacho.ppv_filtrar_username_columna', 'vehiculo')));

        return in_array($v, ['parte_producto', 'ppv', 'parte'], true);
    }

    /**
     * @param  list<mixed>  $bindings
     */
    protected function anexarSqlFiltroUsuarioAsignacion(string &$sql, array &$bindings, ?string $usernameOverride = null): void
    {
        if (! $this->debeAplicarFiltroUserNameVehiculo()) {
            return;
        }

        $userName = $usernameOverride ?? $this->usernameFiltroVehiculo();

        if ($this->usernameColbeefFiltraPorParteProducto()) {
            $sql .= ' AND ppv.user_name = ?';
        } else {
            $sql .= ' AND va.user_name = ?';
        }

        $bindings[] = $userName;
    }

    /**
     * Deja solo líneas con fila disponible en inventario local (sin despachar).
     *
     * @param  list<object{ppv_id: int, id_producto: string}>  $lineas
     * @return list<object{ppv_id: int, id_producto: string}>
     */
    protected function filtrarLineasConInventarioLocal(array $lineas, bool $forzar = false): array
    {
        if (! $forzar && ! $this->debeFiltrarSoloInventarioLocal()) {
            return $lineas;
        }

        return array_values(array_filter($lineas, static function ($line): bool {
            return DespachoInventarioMatch::findAvailableRow($line->id_producto) !== null;
        }));
    }

    /**
     * Filas ppv que cumplen la consulta SQL (parte, usuario, fecha hoy); antes del filtro PHP inventario/catálogo.
     */
    public function conteoFilasPendientesSeleccionSql(?string $usernameOverride = null): ?int
    {
        $conn = $this->connectionName();
        if (! $this->colbeefEnabled() || $conn === null) {
            return null;
        }

        $idParte = $this->idParteProducto();

        $inner = <<<'SQL'
SELECT DISTINCT va.id, ppv.id_producto
FROM trazabilidad_proceso.parte_producto_vehiculo ppv
INNER JOIN trazabilidad_proceso.vehiculo_asignado va ON ppv.id_vehiculo_asignado = va.id
WHERE ppv.id_parte_producto = ?
  AND ppv.producto_entregado = ?
SQL;

        $bindings = [$idParte, $this->valorProductoEntregadoSql()];

        $this->anexarSqlFiltroUsuarioAsignacion($inner, $bindings, $usernameOverride);

        $this->anexarSqlFechaRegistroHoy($inner, $bindings);

        $sql = 'SELECT COUNT(*)::int AS c FROM ('.$inner.') AS sub';

        try {
            $row = DB::connection($conn)->selectOne($sql, $bindings);
        } catch (Throwable $e) {
            report($e);

            throw $e;
        }

        if ($row === null) {
            return 0;
        }

        $arr = (array) $row;

        return (int) ($arr['c'] ?? $arr['C'] ?? reset($arr) ?? 0);
    }

    /**
     * Datos para pantalla de diagnóstico cuando APP_DEBUG=true.
     *
     * @return array<string, mixed>
     */
    public function diagnosticoSeleccionPlacas(?string $usernameOverride = null): array
    {
        $filtraCatalogoCfg = (bool) config('despacho.ppv_filtrar_placa_catalogo_local', true);
        $catalogoTablaCount = DespachoOperadorPlaca::query()->count();
        $catalogoMap = $this->catalogoPlacasLocalNormalizado();

        $sqlError = null;
        try {
            $filasSql = $this->conteoFilasPendientesSeleccionSql($usernameOverride);
        } catch (Throwable $e) {
            $filasSql = null;
            $sqlError = $e->getMessage();
        }

        return [
            'modo_solo_consulta_colbeef' => $this->modoSoloConsultaColbeef(),
            'app_timezone' => (string) config('app.timezone'),
            'fecha_registro_colbeef_como_hoy' => $this->fechaRegistroReferenciaHoy(),
            'id_parte_producto' => $this->idParteProducto(),
            'filtra_va_user_name_config' => (bool) config('despacho.filtrar_ppv_por_user_name_vehiculo', false),
            'filtra_va_user_name_aplicado_sql' => $this->debeAplicarFiltroUserNameVehiculo(),
            'filtro_username_sql_columna' => $this->usernameColbeefFiltraPorParteProducto() ? 'ppv.user_name' : 'va.user_name',
            'va_user_name_efectivo' => $usernameOverride ?? $this->usernameFiltroVehiculo(),
            'solo_fecha_registro_hoy' => $this->debeFiltrarFechaRegistroHoy(),
            'ppv_producto_entregado_valor' => $this->valorProductoEntregadoSql(),
            'solo_inventario_local' => $this->debeFiltrarSoloInventarioLocal(),
            'filtra_catalogo_placas_config' => $filtraCatalogoCfg,
            'catalogo_placas_filas_en_bd' => $catalogoTablaCount,
            'catalogo_placas_filtra_efectivo' => $catalogoMap !== null,
            'filas_pp_colbeef_tras_sql' => $filasSql,
            'error_consulta_sql' => $sqlError,
        ];
    }

    /**
     * Placas con al menos una lengua pendiente de validación física.
     *
     * @return list<object{
     *     id_vehiculo_asignado: int,
     *     placa_vehiculo: string|null,
     *     empresa: string|null,
     *     conductor: string|null,
     *     pendientes: int
     * }>
     */
    public function placasConPendientes(?string $usernameOverride = null): array
    {
        $conn = $this->connectionName();
        if (! $this->colbeefEnabled() || $conn === null) {
            return [];
        }

        $idParte = $this->idParteProducto();

        $sql = <<<'SQL'
SELECT
    ppv.id AS ppv_id,
    va.id AS id_vehiculo_asignado,
    va.placa_vehiculo,
    COALESCE(e.nombre, '') AS empresa,
    COALESCE(p.nombres, '') AS conductor,
    ppv.id_producto AS id_producto
FROM trazabilidad_proceso.parte_producto_vehiculo ppv
INNER JOIN trazabilidad_proceso.vehiculo_asignado va ON ppv.id_vehiculo_asignado = va.id
LEFT JOIN a_recursos_humanos.a_persona p ON va.id_conductor = p.id
LEFT JOIN a_organizaciones.a_empresa_persona ep ON p.id = ep.id_persona
LEFT JOIN a_organizaciones.a_empresa e ON ep.id_empresa = e.id
WHERE ppv.id_parte_producto = ?
  AND ppv.producto_entregado = ?
SQL;

        $bindings = [$idParte, $this->valorProductoEntregadoSql()];

        $this->anexarSqlFiltroUsuarioAsignacion($sql, $bindings, $usernameOverride);

        $this->anexarSqlFechaRegistroHoy($sql, $bindings);

        $sql .= ' ORDER BY va.placa_vehiculo ASC, ppv.id ASC';

        try {
            $rows = DB::connection($conn)->select($sql, $bindings);
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }

        $rows = $this->deduplicarFilasColbeefPorVehiculoYProducto($rows);

        $soloInv = $this->debeFiltrarSoloInventarioLocal();
        $catalogoPlacas = $this->catalogoPlacasLocalNormalizado();
        $acumulado = [];

        foreach ($rows as $row) {
            $a = (array) $row;
            $producto = trim((string) ($a['id_producto'] ?? ''));
            if ($producto === '') {
                continue;
            }
            if ($catalogoPlacas !== null) {
                $placaNorm = self::normalizarPlacaVehiculo(isset($a['placa_vehiculo']) ? (string) $a['placa_vehiculo'] : '');
                if ($placaNorm === '' || ! isset($catalogoPlacas[$placaNorm])) {
                    continue;
                }
            }
            if ($soloInv && DespachoInventarioMatch::findAvailableRow($producto) === null) {
                continue;
            }

            $vid = (int) ($a['id_vehiculo_asignado'] ?? 0);
            if ($vid < 1) {
                continue;
            }

            $empresaRaw = isset($a['empresa']) ? trim((string) $a['empresa']) : '';
            $conductorRaw = isset($a['conductor']) ? trim((string) $a['conductor']) : '';

            if (! isset($acumulado[$vid])) {
                $acumulado[$vid] = (object) [
                    'id_vehiculo_asignado' => $vid,
                    'placa_vehiculo' => isset($a['placa_vehiculo']) ? (string) $a['placa_vehiculo'] : null,
                    'empresa' => $empresaRaw !== '' ? $empresaRaw : null,
                    'conductor' => $conductorRaw !== '' ? $conductorRaw : null,
                    'pendientes' => 0,
                ];
            }

            $acumulado[$vid]->pendientes++;

            if ($empresaRaw !== '' && $acumulado[$vid]->empresa === null) {
                $acumulado[$vid]->empresa = $empresaRaw;
            }
            if ($conductorRaw !== '' && $acumulado[$vid]->conductor === null) {
                $acumulado[$vid]->conductor = $conductorRaw;
            }
        }

        $lista = array_values($acumulado);
        usort($lista, static function (object $x, object $y): int {
            $pa = strtolower(trim((string) ($x->placa_vehiculo ?? '')));
            $pb = strtolower(trim((string) ($y->placa_vehiculo ?? '')));

            return $pa <=> $pb;
        });

        return $lista;
    }

    /**
     * Filas pendientes para un vehículo asignado + datos del vehículo (primera fila).
     *
     * @return array{
     *     vehiculo: object{id_vehiculo_asignado: int, placa_vehiculo: ?string, empresa: ?string, conductor: ?string}|null,
     *     lineas: list<object{ppv_id: int, id_producto: string}>
     * }
     */
    /**
     * @param  bool  $forzarSoloInventarioLocal  true = checklist/cierre: solo códigos con fila disponible en inventario local (ignora DESPACHO_PPV_SOLO_INVENTARIO_LOCAL=false del listado informativo).
     */
    public function pendientesPorVehiculo(int $idVehiculoAsignado, ?string $usernameOverride = null, bool $forzarSoloInventarioLocal = false): array
    {
        $conn = $this->connectionName();
        if (! $this->colbeefEnabled() || $conn === null) {
            return ['vehiculo' => null, 'lineas' => []];
        }

        $idParte = $this->idParteProducto();

        $sql = <<<'SQL'
SELECT
    ppv.id AS ppv_id,
    ppv.id_producto AS id_producto,
    va.id AS id_vehiculo_asignado,
    va.placa_vehiculo,
    COALESCE(e.nombre, '') AS empresa,
    COALESCE(p.nombres, '') AS conductor
FROM trazabilidad_proceso.parte_producto_vehiculo ppv
INNER JOIN trazabilidad_proceso.vehiculo_asignado va ON ppv.id_vehiculo_asignado = va.id
LEFT JOIN a_recursos_humanos.a_persona p ON va.id_conductor = p.id
LEFT JOIN a_organizaciones.a_empresa_persona ep ON p.id = ep.id_persona
LEFT JOIN a_organizaciones.a_empresa e ON ep.id_empresa = e.id
WHERE ppv.id_parte_producto = ?
  AND ppv.producto_entregado = ?
  AND ppv.id_vehiculo_asignado = ?
SQL;

        $bindings = [$idParte, $this->valorProductoEntregadoSql(), $idVehiculoAsignado];

        $this->anexarSqlFiltroUsuarioAsignacion($sql, $bindings, $usernameOverride);

        $this->anexarSqlFechaRegistroHoy($sql, $bindings);

        $sql .= ' ORDER BY ppv.id ASC';

        try {
            $rows = DB::connection($conn)->select($sql, $bindings);
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }

        if ($rows === []) {
            return ['vehiculo' => null, 'lineas' => []];
        }

        $first = (array) $rows[0];
        $catalogoPlacas = $this->catalogoPlacasLocalNormalizado();
        if ($catalogoPlacas !== null) {
            $placaNorm = self::normalizarPlacaVehiculo((string) ($first['placa_vehiculo'] ?? ''));
            if ($placaNorm === '' || ! isset($catalogoPlacas[$placaNorm])) {
                return ['vehiculo' => null, 'lineas' => []];
            }
        }

        $vehiculo = (object) [
            'id_vehiculo_asignado' => (int) ($first['id_vehiculo_asignado'] ?? $idVehiculoAsignado),
            'placa_vehiculo' => $first['placa_vehiculo'] ?? null,
            'empresa' => $first['empresa'] !== '' ? $first['empresa'] : null,
            'conductor' => $first['conductor'] !== '' ? $first['conductor'] : null,
        ];

        $lineas = [];
        foreach ($rows as $row) {
            $a = (array) $row;
            $lineas[] = (object) [
                'ppv_id' => (int) ($a['ppv_id'] ?? 0),
                'id_producto' => trim((string) ($a['id_producto'] ?? '')),
            ];
        }

        $lineas = $this->filtrarLineasConInventarioLocal($lineas, $forzarSoloInventarioLocal);

        if ($lineas === []) {
            return ['vehiculo' => null, 'lineas' => []];
        }

        $lineas = $this->deduplicarLineasPpvPorIdProductoMaxPpv($lineas);

        return ['vehiculo' => $vehiculo, 'lineas' => $lineas];
    }

    /**
     * Lista ordenada de id_producto esperados en checklist (una entrada por producto, ppv más reciente).
     *
     * @return list<string>
     */
    public function idsProductoPendientes(int $idVehiculoAsignado, ?string $usernameOverride = null, bool $forzarSoloInventarioLocal = false): array
    {
        $bundle = $this->pendientesPorVehiculo($idVehiculoAsignado, $usernameOverride, $forzarSoloInventarioLocal);

        return array_values(array_map(static fn ($l): string => $l->id_producto, $bundle['lineas']));
    }

    /** Comparación multiset de códigos normalizados (trims). */
    public function coincideMultisetCanonico(array $idsEsperadosColbeef, array $codigosEnviados): bool
    {
        $norm = static function (string $s): string {
            return trim($s);
        };

        $a = array_map($norm, $idsEsperadosColbeef);
        $b = array_map($norm, $codigosEnviados);
        sort($a);
        sort($b);

        return $a === $b;
    }
}

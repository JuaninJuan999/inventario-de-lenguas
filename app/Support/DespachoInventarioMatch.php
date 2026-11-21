<?php

namespace App\Support;

use App\Models\DecomisoManual;
use App\Models\IngresoLenguaLocal;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Coincidencias entre código escaneado / Colbeef y filas disponibles del inventario local. */
final class DespachoInventarioMatch
{
    /** Guiones Unicode habituales en lectores / Excel → '-' ASCII (alinear con JS del checklist). */
    public static function normalizarGuionesIdProducto(string $idProducto): string
    {
        $t = trim($idProducto);

        return preg_replace('/[\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}\x{FF0D}]/u', '-', $t) ?? $t;
    }

    /**
     * Variantes de texto para comparar en el escáner (JS debe usar la misma idea).
     *
     * @return list<string>
     */
    public static function matchKeysForProductId(string $idProducto): array
    {
        $t = self::normalizarGuionesIdProducto($idProducto);
        $keys = [];
        if ($t !== '') {
            $keys[] = $t;
        }

        $norm = DespachoCodigoBarras::normalizarIdProducto($t);
        if ($norm !== '' && ! in_array($norm, $keys, true)) {
            $keys[] = $norm;
        }

        $compact = preg_replace('/[\s\-\/_.]+/', '', $t) ?? '';
        if ($compact !== '' && ! in_array($compact, $keys, true)) {
            $keys[] = $compact;
        }

        $digits = preg_replace('/\D/', '', $t) ?? '';
        if ($digits !== '' && strlen($digits) >= 3 && ! in_array($digits, $keys, true)) {
            $keys[] = $digits;
        }

        return array_values(array_unique(array_filter($keys, static fn (string $s): bool => $s !== '')));
    }

    public static function findAvailableRow(string $codigoLeido): ?IngresoLenguaLocal
    {
        foreach (self::matchKeysForProductId($codigoLeido) as $key) {
            $row = IngresoLenguaLocal::query()
                ->sinDespachar()
                ->where('id_producto', $key)
                ->orderByDesc('imported_at')
                ->orderByDesc('id')
                ->first();

            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Mapa de claves de id_producto disponibles (sin despachar) para chequeos en lote
     * (alistamiento), evitando N+1 de findAvailableRow.
     *
     * @return array<string, true>
     */
    public static function mapaClavesDisponibles(): array
    {
        $mapa = [];
        foreach (IngresoLenguaLocal::query()->sinDespachar()->pluck('id_producto') as $id) {
            $id = trim((string) $id);
            if ($id === '') {
                continue;
            }
            foreach (self::matchKeysForProductId($id) as $key) {
                $mapa[$key] = true;
            }
        }

        return $mapa;
    }

    /**
     * @param  array<string, true>  $mapa  Resultado de mapaClavesDisponibles().
     */
    public static function codigoEnMapaDisponibles(string $codigoLeido, array $mapa): bool
    {
        if ($mapa === []) {
            return false;
        }
        foreach (self::matchKeysForProductId($codigoLeido) as $key) {
            if (isset($mapa[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indica si el código corresponde a un producto ya decomisado (manual, baja local por SIRT o consulta SIRT).
     *
     * @return array{decomisado: bool, id_producto: string, nombre_dictamen: ?string, nombre_enfermedad: ?string, origen: ?string}|null
     */
    public static function infoDecomiso(string $codigoLeido): ?array
    {
        $keys = self::matchKeysForProductId($codigoLeido);
        if ($keys === []) {
            return null;
        }

        $manual = DecomisoManual::query()
            ->whereIn('id_producto', $keys)
            ->orderByDesc('id')
            ->first();

        if ($manual !== null) {
            return [
                'decomisado' => true,
                'id_producto' => (string) $manual->id_producto,
                'nombre_dictamen' => $manual->nombre_dictamen !== null && $manual->nombre_dictamen !== ''
                    ? (string) $manual->nombre_dictamen
                    : null,
                'nombre_enfermedad' => $manual->nombre_enfermedad !== null && $manual->nombre_enfermedad !== ''
                    ? (string) $manual->nombre_enfermedad
                    : null,
                'origen' => 'manual',
            ];
        }

        $retiradoLocal = IngresoLenguaLocal::query()
            ->whereIn('id_producto', $keys)
            ->whereNotNull('despachado_at')
            ->whereNull('despacho_id')
            ->whereNull('desposte_id')
            ->orderByDesc('despachado_at')
            ->orderByDesc('id')
            ->first();

        if ($retiradoLocal !== null) {
            return [
                'decomisado' => true,
                'id_producto' => (string) $retiradoLocal->id_producto,
                'nombre_dictamen' => null,
                'nombre_enfermedad' => null,
                'origen' => 'local',
            ];
        }

        $sirt = self::infoDecomisoSirt($keys);
        if ($sirt !== null) {
            return $sirt;
        }

        return null;
    }

    /**
     * @param  list<string>  $keys
     * @return array{decomisado: bool, id_producto: string, nombre_dictamen: ?string, nombre_enfermedad: ?string, origen: ?string}|null
     */
    private static function infoDecomisoSirt(array $keys): ?array
    {
        $connection = (string) config('decomisos.sirt_connection', '');
        if ($connection === '') {
            return null;
        }

        $idParte = (int) config('decomisos.id_parte_producto', 24);
        $idDictamen = (int) config('decomisos.id_dictamen', 3);

        try {
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $bindings = array_merge([$idParte, $idDictamen], $keys);
            $rows = DB::connection($connection)->select(
                <<<SQL
SELECT
    i.id_producto::text AS id_producto,
    d.nombre AS nombre_dictamen,
    e.nombre AS nombre_enfermedad
FROM sai.inspeccion i
JOIN sai.dictamen d ON i.id_dictamen = d.id
LEFT JOIN sai.enfermedad_detectada ed ON i.id = ed.id_inspeccion
LEFT JOIN sai.enfermedad e ON ed.id_enfermedad = e.id
WHERE i.id_parte_producto = ?
  AND i.id_dictamen = ?
  AND i.id_producto::text IN ($placeholders)
ORDER BY i.id DESC
LIMIT 1
SQL,
                $bindings,
            );
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        if ($rows === []) {
            return null;
        }

        $row = (array) $rows[0];

        return [
            'decomisado' => true,
            'id_producto' => (string) ($row['id_producto'] ?? $keys[0]),
            'nombre_dictamen' => isset($row['nombre_dictamen']) && $row['nombre_dictamen'] !== ''
                ? (string) $row['nombre_dictamen']
                : null,
            'nombre_enfermedad' => isset($row['nombre_enfermedad']) && $row['nombre_enfermedad'] !== ''
                ? (string) $row['nombre_enfermedad']
                : null,
            'origen' => 'sirt',
        ];
    }

    /**
     * @return array{en_inventario: bool, canonical_id_producto: string, propietario: ?string, destino: ?string, es_adicional: bool, match_keys: list<string>}
     */
    public static function resumenParaChecklist(string $idProductoColbeef): array
    {
        $inv = self::findAvailableRow($idProductoColbeef);
        $trimColbeef = self::normalizarGuionesIdProducto($idProductoColbeef);
        $canonical = $inv !== null ? self::normalizarGuionesIdProducto((string) $inv->id_producto) : $trimColbeef;

        $keys = self::matchKeysForProductId($trimColbeef);
        if ($inv !== null && $canonical !== $trimColbeef) {
            $keys = array_values(array_unique(array_merge($keys, self::matchKeysForProductId($canonical))));
        }

        return [
            'en_inventario' => $inv !== null,
            'canonical_id_producto' => $canonical,
            'propietario' => $inv?->propietario,
            'destino' => $inv?->destino,
            'es_adicional' => (bool) ($inv?->es_adicional ?? false),
            'match_keys' => $keys,
        ];
    }

    /**
     * Simula consumos greedy sobre inventario disponible (mismo criterio que findAvailableRow por fila).
     * Sirve para validar multiset esperado vs enviado sin efectos secundarios.
     *
     * @param  list<string>  $codigosLeidos
     * @return list<string>|false Lista ordenada de id_producto consumidos o false si falta inventario en algún paso.
     */
    public static function multisetCanonicoGreedy(array $codigosLeidos): array|false
    {
        $pool = IngresoLenguaLocal::query()
            ->sinDespachar()
            ->orderByDesc('imported_at')
            ->orderByDesc('id')
            ->get(['id', 'id_producto']);

        $poolRows = [];
        foreach ($pool as $row) {
            $poolRows[] = ['id' => (int) $row->id, 'id_producto' => (string) $row->id_producto];
        }

        $resultado = [];

        foreach ($codigosLeidos as $raw) {
            $raw = trim((string) $raw);
            if ($raw === '') {
                continue;
            }

            $keys = self::matchKeysForProductId($raw);
            $elegidoIdx = null;

            foreach ($poolRows as $i => $entry) {
                $pid = $entry['id_producto'];
                foreach ($keys as $k) {
                    if ($pid === $k) {
                        $elegidoIdx = $i;

                        break 2;
                    }
                }
            }

            if ($elegidoIdx === null) {
                return false;
            }

            $resultado[] = $poolRows[$elegidoIdx]['id_producto'];
            array_splice($poolRows, $elegidoIdx, 1);
        }

        sort($resultado);

        return $resultado;
    }
}

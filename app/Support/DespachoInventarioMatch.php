<?php

namespace App\Support;

use App\Models\IngresoLenguaLocal;

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
     * @return array{en_inventario: bool, canonical_id_producto: string, propietario: ?string, destino: ?string, match_keys: list<string>}
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

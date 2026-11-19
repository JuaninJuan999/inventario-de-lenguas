<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/** Lectura mínima de la primera hoja de un .xlsx (shared strings + celdas). */
final class SimpleXlsxReader
{
    /**
     * @return list<array<string, string>> Filas indexadas por columna (A, B, …)
     */
    public static function sheetRows(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException('No existe el archivo: '.$absolutePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            throw new RuntimeException('No se pudo abrir el Excel: '.$absolutePath);
        }

        $ssXml = $zip->getFromName('xl/sharedStrings.xml') ?: '';
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml') ?: '';
        $zip->close();

        if ($sheetXml === '') {
            throw new RuntimeException('El Excel no tiene hoja sheet1 legible.');
        }

        $strings = self::sharedStrings($ssXml);
        $sheet = self::xml($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row ?? [] as $row) {
            $r = [];
            foreach ($row->c ?? [] as $c) {
                $ref = (string) ($c['r'] ?? '');
                $col = preg_replace('/\d+/', '', $ref) ?? '';
                if ($col === '') {
                    continue;
                }
                $t = (string) ($c['t'] ?? '');
                $v = isset($c->v) ? (string) $c->v : '';
                if ($t === 's' && $v !== '' && isset($strings[(int) $v])) {
                    $v = $strings[(int) $v];
                } elseif ($t === 'inlineStr') {
                    $v = (string) ($c->is->t ?? '');
                }
                $r[$col] = trim($v);
            }
            if ($r !== []) {
                $rows[] = $r;
            }
        }

        return $rows;
    }

    /**
     * Lee la columna cuyo encabezado coincide (case-insensitive) con $headerName.
     *
     * @return list<string>
     */
    public static function columnByHeader(string $absolutePath, string $headerName): array
    {
        $rows = self::sheetRows($absolutePath);
        if ($rows === []) {
            return [];
        }

        $header = $rows[0];
        $target = mb_strtolower(trim($headerName));
        $colKey = null;
        foreach ($header as $col => $label) {
            if (mb_strtolower(trim((string) $label)) === $target) {
                $colKey = $col;
                break;
            }
        }

        if ($colKey === null) {
            throw new RuntimeException(
                'No se encontró la columna «'.$headerName.'» en el encabezado del Excel.'
            );
        }

        $out = [];
        for ($i = 1, $n = count($rows); $i < $n; $i++) {
            $val = trim((string) ($rows[$i][$colKey] ?? ''));
            if ($val !== '') {
                $out[] = $val;
            }
        }

        return $out;
    }

    /** @return list<string> */
    private static function sharedStrings(string $ssXml): array
    {
        if ($ssXml === '') {
            return [];
        }

        $ss = self::xml($ssXml);
        $strings = [];
        foreach ($ss->si ?? [] as $si) {
            $buf = '';
            if (isset($si->t)) {
                $buf .= (string) $si->t;
            }
            foreach ($si->r ?? [] as $r) {
                $buf .= (string) ($r->t ?? '');
            }
            $strings[] = $buf;
        }

        return $strings;
    }

    private static function xml(string $xml): SimpleXMLElement
    {
        $clean = preg_replace('/xmlns(:\w+)?="[^"]*"/', '', $xml) ?? $xml;
        $prev = libxml_use_internal_errors(true);
        $el = simplexml_load_string($clean, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if ($el === false) {
            throw new RuntimeException('XML de Excel ilegible.');
        }

        return $el;
    }
}

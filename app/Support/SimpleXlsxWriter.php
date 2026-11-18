<?php

namespace App\Support;

use ZipArchive;

/** Genera un .xlsx mínimo (Office Open XML) sin dependencias externas. */
final class SimpleXlsxWriter
{
    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    public static function build(array $headers, array $rows, string $sheetName = 'Inventario'): string
    {
        $sheetName = self::safeSheetName($sheetName);
        $sheetXml = self::sheetXml($headers, $rows);
        $shared = self::sharedStringsXml($headers, $rows);

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        if ($tmp === false) {
            throw new \RuntimeException('No se pudo crear archivo temporal para Excel.');
        }

        $zip = new ZipArchive;
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            throw new \RuntimeException('No se pudo abrir el ZIP del Excel.');
        }

        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.self::xml($sheetName).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>');

        $zip->addFromString('xl/styles.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>
  <fills count="1"><fill><patternFill patternType="none"/></fill></fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf/></cellStyleXfs>
  <cellXfs count="1"><xf/></cellXfs>
</styleSheet>
XML);

        $zip->addFromString('xl/sharedStrings.xml', $shared);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $binary = file_get_contents($tmp);
        @unlink($tmp);

        if ($binary === false) {
            throw new \RuntimeException('No se pudo leer el Excel generado.');
        }

        return $binary;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private static function sharedStringsXml(array $headers, array $rows): string
    {
        $strings = [];
        $indexOf = static function (string $value) use (&$strings): int {
            if (array_key_exists($value, $strings)) {
                return $strings[$value];
            }
            $idx = count($strings);
            $strings[$value] = $idx;

            return $idx;
        };

        // Pre-index for deterministic order (headers first, then rows left-to-right).
        foreach ($headers as $h) {
            $indexOf((string) $h);
        }
        foreach ($rows as $row) {
            foreach ($row as $cell) {
                if (! is_int($cell) && ! is_float($cell)) {
                    $indexOf((string) ($cell ?? ''));
                }
            }
        }

        // Rebuild ordered list by index.
        $ordered = array_fill(0, count($strings), '');
        foreach ($strings as $value => $idx) {
            $ordered[$idx] = $value;
        }

        $parts = [];
        foreach ($ordered as $value) {
            $parts[] = '<si><t>'.self::xml($value).'</t></si>';
        }

        $count = count($ordered);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.$count.'" uniqueCount="'.$count.'">'
            .implode('', $parts)
            .'</sst>';
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private static function sheetXml(array $headers, array $rows): string
    {
        $map = [];
        $next = 0;
        $sid = static function (string $value) use (&$map, &$next): int {
            if (! array_key_exists($value, $map)) {
                $map[$value] = $next++;
            }

            return $map[$value];
        };

        foreach ($headers as $h) {
            $sid((string) $h);
        }
        foreach ($rows as $row) {
            foreach ($row as $cell) {
                if (! is_int($cell) && ! is_float($cell)) {
                    $sid((string) ($cell ?? ''));
                }
            }
        }

        $xmlRows = [];
        $xmlRows[] = self::rowXml(1, $headers, $map, true);

        $r = 2;
        foreach ($rows as $row) {
            $xmlRows[] = self::rowXml($r, $row, $map, false);
            $r++;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.implode('', $xmlRows).'</sheetData>'
            .'</worksheet>';
    }

    /**
     * @param  list<string|int|float|null>  $cells
     * @param  array<string, int>  $map
     */
    private static function rowXml(int $rowNum, array $cells, array $map, bool $forceShared): string
    {
        $out = '<row r="'.$rowNum.'">';
        $col = 0;
        foreach ($cells as $cell) {
            $ref = self::colName($col).$rowNum;
            if (! $forceShared && (is_int($cell) || is_float($cell))) {
                $out .= '<c r="'.$ref.'"><v>'.$cell.'</v></c>';
            } else {
                $text = (string) ($cell ?? '');
                $idx = $map[$text] ?? 0;
                $out .= '<c r="'.$ref.'" t="s"><v>'.$idx.'</v></c>';
            }
            $col++;
        }
        $out .= '</row>';

        return $out;
    }

    private static function colName(int $index): string
    {
        $name = '';
        $n = $index;
        do {
            $name = chr(65 + ($n % 26)).$name;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);

        return $name;
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function safeSheetName(string $name): string
    {
        $name = preg_replace('/[\\\\\/\\?\\*\\[\\]:]/', '', $name) ?? 'Hoja';
        $name = trim($name);
        if ($name === '') {
            $name = 'Hoja';
        }

        return mb_substr($name, 0, 31);
    }
}

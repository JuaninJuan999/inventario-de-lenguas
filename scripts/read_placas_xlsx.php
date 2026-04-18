<?php

$f = $argv[1] ?? 'C:/Users/tecno/Downloads/PLACASS COLBEEF.xlsx';
if (! is_readable($f)) {
    fwrite(STDERR, "No se puede leer: {$f}\n");
    exit(1);
}

$z = new ZipArchive;
if ($z->open($f) !== true) {
    fwrite(STDERR, "Zip fallo\n");
    exit(1);
}

$shared = [];
if ($z->locateName('xl/sharedStrings.xml') !== false) {
    $xml = $z->getFromName('xl/sharedStrings.xml');
    $sx = @simplexml_load_string($xml);
    if ($sx !== false) {
        foreach ($sx->si as $si) {
            $t = '';
            if (isset($si->t)) {
                $t = (string) $si->t;
            } elseif (isset($si->r)) {
                foreach ($si->r as $r) {
                    $t .= (string) $r->t;
                }
            }
            $shared[] = $t;
        }
    }
}

$sheet = $z->getFromName('xl/worksheets/sheet1.xml');
$z->close();
if ($sheet === false) {
    fwrite(STDERR, "Sin sheet1\n");
    exit(1);
}

$sheet = preg_replace('/\sxmlns="[^"]+"/', '', $sheet);
$sx = @simplexml_load_string($sheet);
if ($sx === false) {
    fwrite(STDERR, "XML invalido\n");
    exit(1);
}

$rows = $sx->xpath('//sheetData/row');
$max = count($rows);
for ($ri = 0; $ri < $max; $ri++) {
    $row = $rows[$ri];
    $cells = $row->xpath('c');
    $line = [];
    foreach ($cells as $c) {
        $r = (string) $c['r'];
        $t = (string) $c['t'];
        $v = isset($c->v) ? (string) $c->v : '';
        if ($t === 's' && $v !== '' && ctype_digit($v)) {
            $idx = (int) $v;
            $v = $shared[$idx] ?? $v;
        }
        $line[] = $v;
    }
    echo implode("\t", $line)."\n";
}

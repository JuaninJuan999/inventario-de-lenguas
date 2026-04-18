<?php

/**
 * Lee PLACASS COLBEEF.xlsx (o ruta en argv[1]) y:
 * 1) Regenera config/despacho_operadores_placas.php
 * 2) Si existe bootstrap de Laravel, hace upsert en despacho_operador_placas
 *
 * Uso: php scripts/sync_placas_from_xlsx.php [ruta.xlsx]
 */

declare(strict_types=1);

$f = $argv[1] ?? 'C:/Users/tecno/Downloads/PLACASS COLBEEF.xlsx';
if (! is_readable($f)) {
    fwrite(STDERR, "No se puede leer: {$f}\n");
    exit(1);
}

$z = new ZipArchive;
if ($z->open($f) !== true) {
    fwrite(STDERR, "No se pudo abrir el zip xlsx.\n");
    exit(1);
}

$shared = [];
if ($z->locateName('xl/sharedStrings.xml') !== false) {
    $xml = $z->getFromName('xl/sharedStrings.xml');
    $sx = @simplexml_load_string((string) $xml);
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
    fwrite(STDERR, "No se encontró xl/worksheets/sheet1.xml\n");
    exit(1);
}

$sheet = preg_replace('/\sxmlns="[^"]+"/', '', (string) $sheet);
$sx = @simplexml_load_string($sheet);
if ($sx === false) {
    fwrite(STDERR, "XML de hoja inválido.\n");
    exit(1);
}

$rows = $sx->xpath('//sheetData/row');
if (! is_array($rows)) {
    fwrite(STDERR, "Sin filas.\n");
    exit(1);
}

/** @var list<list<string>> $matrix */
$matrix = [];
foreach ($rows as $row) {
    $cells = $row->xpath('c');
    if (! is_array($cells)) {
        continue;
    }
    $byCol = [];
    foreach ($cells as $c) {
        $ref = (string) $c['r'];
        if ($ref === '' || ! preg_match('/^([A-Z]+)(\d+)$/i', $ref, $m)) {
            continue;
        }
        $colLetters = strtoupper($m[1]);
        $colIndex = 0;
        for ($i = 0, $len = strlen($colLetters); $i < $len; $i++) {
            $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - 64);
        }
        $colIndex--;
        $t = (string) $c['t'];
        $v = isset($c->v) ? (string) $c->v : '';
        if ($t === 's' && $v !== '' && ctype_digit($v)) {
            $idx = (int) $v;
            $v = $shared[$idx] ?? $v;
        }
        $byCol[$colIndex] = $v;
    }
    if ($byCol === []) {
        continue;
    }
    $maxCol = max(array_keys($byCol));
    $line = [];
    for ($j = 0; $j <= $maxCol; $j++) {
        $line[] = $byCol[$j] ?? '';
    }
    $matrix[] = $line;
}

if ($matrix === []) {
    fwrite(STDERR, "Matriz vacía.\n");
    exit(1);
}

$header = array_map(static fn (string $s): string => mb_strtoupper(trim($s)), $matrix[0]);
$idxPlaca = null;
$idxOperador = null;
foreach ($header as $i => $h) {
    if ($h === 'PLACA') {
        $idxPlaca = $i;
    }
    if (str_contains($h, 'OPERADOR') && str_contains($h, 'LOG')) {
        $idxOperador = $i;
    }
}
if ($idxPlaca === null || $idxOperador === null) {
    fwrite(STDERR, 'Cabeceras esperadas: PLACA y OPERADOR LOGISTICO. Encontrado: '.implode(' | ', $header)."\n");
    exit(1);
}

/** @var list<array{placa: string, operador: string}> $filas */
$filas = [];
$vistos = [];
for ($r = 1, $n = count($matrix); $r < $n; $r++) {
    $line = $matrix[$r];
    $placa = trim((string) ($line[$idxPlaca] ?? ''));
    $op = trim((string) ($line[$idxOperador] ?? ''));
    if ($placa === '' || $op === '') {
        continue;
    }
    $clave = mb_strtoupper($placa)."\t".mb_strtoupper($op);
    if (isset($vistos[$clave])) {
        continue;
    }
    $vistos[$clave] = true;
    $filas[] = ['placa' => $placa, 'operador' => $op];
}

$root = dirname(__DIR__);
$configPath = $root.'/config/despacho_operadores_placas.php';

$export = static function (array $filas): string {
    $lines = ["<?php\n", "declare(strict_types=1);\n", "\n", "/**\n", " * Pares placa / operador logístico (COLBEEF).\n", " * Generado con: php scripts/sync_placas_from_xlsx.php\n", " * Listado principal: tabla PostgreSQL `despacho_operador_placas`.\n", " */\n", "return [\n", "    'filas' => [\n"];
    foreach ($filas as $row) {
        $p = addslashes($row['placa']);
        $o = addslashes($row['operador']);
        $lines[] = "        ['placa' => '{$p}', 'operador' => '{$o}'],\n";
    }
    $lines[] = "    ],\n";
    $lines[] = "];\n";

    return implode('', $lines);
};

file_put_contents($configPath, $export($filas));
echo "Config actualizado: {$configPath} (".count($filas)." filas únicas).\n";

$autoload = $root.'/vendor/autoload.php';
if (is_readable($autoload)) {
    require $autoload;
    $app = require $root.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $now = now();
    $rows = [];
    foreach ($filas as $row) {
        $rows[] = [
            'placa' => $row['placa'],
            'operador_logistico' => $row['operador'],
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    if ($rows !== []) {
        // TRUNCATE reinicia la secuencia del id; DELETE no (los nuevos ids seguirían desde el contador anterior).
        \Illuminate\Support\Facades\DB::table('despacho_operador_placas')->truncate();
        foreach (array_chunk($rows, 100) as $chunk) {
            \App\Models\DespachoOperadorPlaca::query()->insert($chunk);
        }
        echo 'Base de datos: tabla vaciada y cargadas '.count($rows)." filas (id reiniciado desde 1).\n";
    }
} else {
    echo "Sin vendor/autoload: ejecute luego: php artisan db:seed --class=DespachoOperadorPlacaSeeder\n";
}

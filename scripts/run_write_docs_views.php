<?php

declare(strict_types=1);

$base = dirname(__DIR__) . '/resources/views/frontend/docs';
if (! is_dir($base)) {
    mkdir($base, 0755, true);
}

$py = file_get_contents(__DIR__ . '/_write_docs_views.py');
if ($py === false) {
    fwrite(STDERR, "Missing _write_docs_views.py\n");
    exit(1);
}

function extractPythonRawTriple(string $py, string $varName): string
{
    $needle = $varName . ' = r"""';
    $start = strpos($py, $needle);
    if ($start === false) {
        throw new RuntimeException("Cannot find {$varName} in py file");
    }
    $start += strlen($needle);
    $end = strpos($py, '"""', $start);
    if ($end === false) {
        throw new RuntimeException("Unclosed triple string for {$varName}");
    }

    return substr($py, $start, $end - $start);
}

$layout = extractPythonRawTriple($py, 'layout');
$index = extractPythonRawTriple($py, 'index');
$show = extractPythonRawTriple($py, 'show');

file_put_contents($base . '/layout.blade.php', $layout);
file_put_contents($base . '/index.blade.php', $index);
file_put_contents($base . '/show.blade.php', $show);

echo "Wrote blades to {$base}\n";
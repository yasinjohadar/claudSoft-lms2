<?php

$cfg = require __DIR__.'/../config/country_codes.php';
$out = [];

foreach (($cfg['list_text_only'] ?? $cfg['list'] ?? []) as $code => $label) {
    $out[] = ['code' => $code, 'label' => $label];
}

$payload = [
    'default_country_code' => $cfg['default'] ?? '+963',
    'country_codes' => $out,
];

$target = __DIR__.'/../../claudsoft-desktop/src/data/countryCodes.json';
if (! is_dir(dirname($target))) {
    mkdir(dirname($target), 0777, true);
}

file_put_contents(
    $target,
    json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

echo count($out)." country codes exported to {$target}\n";

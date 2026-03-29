<?php
$s = file_get_contents(__DIR__ . '/resources/views/admin/docs/pages/create.blade.php');
if (str_starts_with($s, "\xEF\xBB\xBF")) {
    $s = substr($s, 3);
}
$fix = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
echo substr($fix, 0, 220);

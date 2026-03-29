<?php
$s = file_get_contents(__DIR__ . '/resources/views/admin/docs/pages/create.blade.php');
$a = mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
$b = mb_convert_encoding($a, 'UTF-8', 'ISO-8859-1');
echo substr($b, 0, 220);

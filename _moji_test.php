<?php
$c = file_get_contents(__DIR__ . '/resources/views/admin/docs/pages/create.blade.php');
$f = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $c);
echo substr($f, 0, 180);

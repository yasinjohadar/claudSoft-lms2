<?php
function fixDoubleUtf8(string $string): string
{
    return preg_replace_callback(
        '/[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2}/x',
        static function (array $matches): string {
            return utf8_decode($matches[0]);
        },
        $string
    );
}
$s = file_get_contents(__DIR__ . '/resources/views/admin/docs/pages/create.blade.php');
if (str_starts_with($s, "\xEF\xBB\xBF")) {
    $s = substr($s, 3);
}
$fix = fixDoubleUtf8($s);
echo substr($fix, 0, 220);

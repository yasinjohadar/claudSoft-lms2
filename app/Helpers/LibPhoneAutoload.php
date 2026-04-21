<?php

/**
 * Fallback autoload for giggsey/libphonenumber-for-php when Composer's generated
 * autoload omits it (e.g. corrupted vendor/composer state). Safe no-op if classes already load.
 */
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'libphonenumber\\')) {
        $base = dirname(__DIR__, 2).'/vendor/giggsey/libphonenumber-for-php/src/';
        $relative = substr($class, strlen('libphonenumber\\'));
        $file = $base.str_replace('\\', '/', $relative).'.php';
        if (is_file($file)) {
            require_once $file;
        }

        return;
    }

    if (str_starts_with($class, 'Giggsey\\Locale\\')) {
        $base = dirname(__DIR__, 2).'/vendor/giggsey/locale/src/';
        $relative = substr($class, strlen('Giggsey\\Locale\\'));
        $file = $base.str_replace('\\', '/', $relative).'.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}, true, true);

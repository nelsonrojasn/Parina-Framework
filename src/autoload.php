<?php

/**
 * KISS Autoloader for Parina Framework.
 * Implements PSR-4 class loading standard.
 */
spl_autoload_register(static function (string $class) {
    if (str_starts_with($class, 'Parina\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 7)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    } elseif (str_starts_with($class, 'Tests\\')) {
        $file = dirname(__DIR__) . '/tests/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

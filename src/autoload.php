<?php

/**
 * KISS Autoloader for Parina Framework.
 * Implements PSR-4 class loading standard.
 */
spl_autoload_register(static function (string $class) {
    // Map namespace prefixes to base directories
    $prefixes = [
        'Parina\\' => __DIR__ . '/',
        'Tests\\' => dirname(__DIR__) . '/tests/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Get relative class name and convert to path
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

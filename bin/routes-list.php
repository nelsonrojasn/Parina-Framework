<?php

/**
 * CLI tool to list registered routes in a beautifully formatted text table.
 * 
 * Usage: php bin/routes-list.php
 */

require_once dirname(__DIR__) . '/src/autoload.php';

$routesFile = dirname(__DIR__) . '/config/routes.php';

if (!file_exists($routesFile)) {
    echo "\033[1;31mError: routes config file not found at $routesFile\033[0m\n";
    exit(1);
}

$routes = require $routesFile;

// 1. Initialize headers and baseline widths
$headers = ['Method', 'Path', 'Feature', 'HandlerName', 'Middlewares', 'Description'];
$widths = array_combine($headers, array_map('strlen', $headers));
$rows = [];

// 2. Process routes and calculate dynamic widths
foreach ($routes as $route) {
    $method = $route['method'] ?? 'GET';
    $path = $route['path'] ?? '/';
    $handlerClass = $route['handler'] ?? '';
    $middlewareList = $route['middleware'] ?? [];

    // Extract Feature and HandlerName from handler FQCN
    $feature = '-';
    $handlerName = '';
    if (!empty($handlerClass)) {
        $parts = explode('\\', ltrim($handlerClass, '\\'));
        if (count($parts) >= 3 && $parts[0] === 'Parina' && $parts[1] === 'Features') {
            $feature = $parts[2];
        }
        $handlerName = end($parts);
    }

    // Extract Middlewares short names
    $shortMiddlewares = [];
    foreach ($middlewareList as $mw) {
        $mwParts = explode('\\', ltrim($mw, '\\'));
        $shortMiddlewares[] = end($mwParts);
    }
    $middlewares = implode(', ', $shortMiddlewares);

    // Extract Description using reflection
    $description = '';
    if (!empty($handlerClass) && class_exists($handlerClass)) {
        try {
            $reflector = new ReflectionClass($handlerClass);
            $docComment = $reflector->getDocComment();
            if ($docComment !== false) {
                if (preg_match('/Description:\s*(.*)/i', $docComment, $matches)) {
                    $description = trim($matches[1]);
                }
            }
        } catch (Throwable $e) {
            // ignore
        }
    }

    $row = [$method, $path, $feature, $handlerName, $middlewares, $description];
    $rows[] = $row;

    // Dynamically compute the maximum column width
    foreach ($row as $index => $value) {
        $colName = $headers[$index];
        $widths[$colName] = max($widths[$colName], strlen($value));
    }
}

// 3. Render Table
// Generate horizontal separator line
$border = "+";
foreach ($headers as $header) {
    $border .= str_repeat('-', $widths[$header] + 2) . "+";
}

// Generate header row line
$headerLine = "|";
foreach ($headers as $header) {
    $headerLine .= " \033[1;36m" . str_pad($header, $widths[$header]) . "\033[0m |";
}

// Print header
echo "\n" . $border . "\n";
echo $headerLine . "\n";
echo $border . "\n";

// Print data rows
foreach ($rows as $row) {
    $line = "|";
    foreach ($row as $index => $value) {
        $headerName = $headers[$index];
        
        // Color method verbs for enhanced terminal readability
        if ($index === 0) {
            $color = match (strtoupper($value)) {
                'GET'    => "\033[1;32m", // Green
                'POST'   => "\033[1;33m", // Yellow
                'PUT'    => "\033[1;34m", // Blue
                'DELETE' => "\033[1;31m", // Red
                default  => ""
            };
            $paddedVal = $color . str_pad($value, $widths[$headerName]) . "\033[0m";
        } else {
            $paddedVal = str_pad($value, $widths[$headerName]);
        }
        
        $line .= " " . $paddedVal . " |";
    }
    echo $line . "\n";
}

echo $border . "\n\n";

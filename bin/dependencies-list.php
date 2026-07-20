<?php

/**
 * CLI tool to list registered container dependencies in beautifully formatted text tables.
 * 
 * Usage: php bin/dependencies-list.php
 */

require_once dirname(__DIR__) . '/src/autoload.php';

$depsFile = dirname(__DIR__) . '/config/dependencies.php';

if (!file_exists($depsFile)) {
    echo "\033[1;31mError: dependencies config file not found at $depsFile\033[0m\n";
    exit(1);
}

$deps = require $depsFile;

$bindings = $deps['bindings'] ?? [];
$singletons = $deps['singletons'] ?? [];

function renderDependencyTable(string $title, string $titleColor, array $items): void
{
    echo "\n" . $titleColor . $title . "\033[0m\n";

    if (empty($items)) {
        echo "\033[1;30m(No hay dependencias registradas en esta categoría)\033[0m\n\n";
        return;
    }

    $headers = ['Abstract / Service ID', 'Implementation / Target', 'Type'];
    $widths = array_combine($headers, array_map('strlen', $headers));
    $rows = [];

    foreach ($items as $abstract => $concrete) {
        $abstractStr = (string)$abstract;

        if (is_string($concrete)) {
            $concreteStr = $concrete;
            $typeStr = 'Class';
        } elseif ($concrete instanceof \Closure) {
            $concreteStr = 'Closure (Factory)';
            $typeStr = 'Closure';
        } else {
            $concreteStr = is_object($concrete) ? get_class($concrete) : gettype($concrete);
            $typeStr = 'Mixed';
        }

        $row = [$abstractStr, $concreteStr, $typeStr];
        $rows[] = $row;

        foreach ($row as $index => $value) {
            $colName = $headers[$index];
            $widths[$colName] = max($widths[$colName], strlen($value));
        }
    }

    // Generate border line
    $border = "+";
    foreach ($headers as $header) {
        $border .= str_repeat('-', $widths[$header] + 2) . "+";
    }

    // Generate header row line
    $headerLine = "|";
    foreach ($headers as $header) {
        $headerLine .= " \033[1;36m" . str_pad($header, $widths[$header]) . "\033[0m |";
    }

    echo $border . "\n";
    echo $headerLine . "\n";
    echo $border . "\n";

    // Print data rows
    foreach ($rows as $row) {
        $line = "|";
        foreach ($row as $index => $value) {
            $headerName = $headers[$index];
            
            if ($index === 2) {
                // Colorize Type column
                $color = match ($value) {
                    'Class'   => "\033[1;32m", // Green
                    'Closure' => "\033[1;33m", // Yellow
                    default   => "\033[1;34m"  // Blue
                };
                $paddedVal = $color . str_pad($value, $widths[$headerName]) . "\033[0m";
            } else {
                $paddedVal = str_pad($value, $widths[$headerName]);
            }
            
            $line .= " " . $paddedVal . " |";
        }
        echo $line . "\n";
    }

    echo $border . "\n";
}

renderDependencyTable(
    "== SINGLETONS (Carga Única / Compartidos) ==",
    "\033[1;35m",
    $singletons
);

renderDependencyTable(
    "== TRANSIENTS / BINDINGS (Carga por Petición / Instancia Nueva) ==",
    "\033[1;33m",
    $bindings
);

echo "\n";

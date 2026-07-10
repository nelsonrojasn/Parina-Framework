<?php

/**
 * CLI tool to list registered routes in a beautifully formatted text table.
 * 
 * Usage: php bin/routes-list.php
 */

$csvFile = dirname(__DIR__) . '/routes.csv';

if (!file_exists($csvFile)) {
    echo "\033[1;31mError: routes.csv not found at $csvFile\033[0m\n";
    exit(1);
}

// 1. Initialize headers and baseline widths
$headers = ['Method', 'Path', 'Feature', 'HandlerName', 'Middlewares', 'Description'];
$widths = array_combine($headers, array_map('strlen', $headers));
$rows = [];

// 2. Read CSV and calculate dynamic widths
if (($handle = fopen($csvFile, "r")) !== false) {
    // Read and discard header row
    $headerRow = fgetcsv($handle, 1000, ",");
    
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        // Skip empty or malformed rows
        if (empty($data) || count($data) < 2) {
            continue;
        }

        // Align data index to headers
        if (count($data) < 6) {
            $data = array_pad($data, 6, '');
        }
        $row = array_slice($data, 0, 6);
        $rows[] = $row;

        // Dynamically compute the maximum column width
        foreach ($row as $index => $value) {
            $colName = $headers[$index];
            $widths[$colName] = max($widths[$colName], strlen($value));
        }
    }
    fclose($handle);
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

#!/usr/bin/env php
<?php

/**
 * FPS Rules Engine CLI Tester
 * 
 * Compiles and executes a custom FPS Rules script with optional input parameters.
 */

// Load Parina's PSR-4 autoloader.
require_once __DIR__ . '/../src/autoload.php';

use Parina\Shared\Services\Fps\FpsEngine;

// ANSI Colors.
define('C_RESET', "\033[0m");
define('C_RED', "\033[1;31m");
define('C_GREEN', "\033[1;32m");
define('C_YELLOW', "\033[1;33m");
define('C_BLUE', "\033[1;34m");
define('C_WHITE', "\033[1;37m");

if ($argc < 2) {
    echo C_YELLOW . "Usage: php bin/fps-tester.php <rules_file.fps> [parameters.json]" . C_RESET . "\n";
    exit(1);
}

$rulesFile = $argv[1];
$paramsFile = $argv[2] ?? null;

if (!file_exists($rulesFile)) {
    echo C_RED . "Error: Rules file '{$rulesFile}' not found." . C_RESET . "\n";
    exit(1);
}

$source = file_get_contents($rulesFile);
$params = [];

if ($paramsFile) {
    if (!file_exists($paramsFile)) {
        echo C_RED . "Error: Parameters file '{$paramsFile}' not found." . C_RESET . "\n";
        exit(1);
    }
    $paramsContent = file_get_contents($paramsFile);
    $params = json_decode($paramsContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo C_RED . "Error: Failed to decode JSON parameters - " . json_last_error_msg() . C_RESET . "\n";
        exit(1);
    }
}

try {
    $engine = new FpsEngine();
    
    echo C_BLUE . "Compiling ruleset: " . C_YELLOW . $rulesFile . C_RESET . "\n";
    $t0 = microtime(true);
    $bytecode = $engine->compile($source);
    $compTime = (microtime(true) - $t0) * 1000;
    
    echo C_BLUE . "Executing sandbox VM..." . C_RESET . "\n";
    $t1 = microtime(true);
    $results = $engine->execute($bytecode, $params);
    $execTime = (microtime(true) - $t1) * 1000;
    
    echo C_GREEN . "Execution completed successfully!" . C_RESET . "\n\n";
    
    echo C_WHITE . "=== Results ===" . C_RESET . "\n";
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    echo C_WHITE . "=== Performance Metrics ===" . C_RESET . "\n";
    echo "Compilation Time: " . number_format($compTime, 4) . " ms\n";
    echo "Execution Time:   " . number_format($execTime, 4) . " ms\n";
    echo "Total Time:       " . number_format($compTime + $execTime, 4) . " ms\n";
    
} catch (\Throwable $e) {
    echo C_RED . "Fatal Execution Error: " . $e->getMessage() . C_RESET . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

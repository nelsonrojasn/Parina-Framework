<?php

/**
 * CLI Orchestrator Tool to build/rebuild the application from scratch.
 * Performs cleanup, scaffolding, database migration, and batch CQS repository generation.
 * 
 * Usage: php bin/orchestrator.php [routes_csv] [cqs_csv]
 */

$projectRoot = dirname(__DIR__);

$routesCsv = $argv[1] ?? 'routes.csv';
$cqsCsv = $argv[2] ?? 'cqs.csv';

$routesCsvPath = (file_exists($routesCsv)) ? $routesCsv : $projectRoot . '/' . $routesCsv;
$cqsCsvPath = (file_exists($cqsCsv)) ? $cqsCsv : $projectRoot . '/' . $cqsCsv;

if (!file_exists($routesCsvPath)) {
    echo "\033[1;31mError: Routes CSV file not found at '$routesCsvPath'\033[0m\n";
    exit(1);
}

// Backup routes CSV content in memory to prevent it being lost when cleanup resets routes.csv
$routesBackup = null;
if (file_exists($routesCsvPath)) {
    $routesBackup = file_get_contents($routesCsvPath);
}

echo "========================================================\n";
echo "🚀 PHASE 1: CLEANING UP PREVIOUS BUILD\n";
echo "========================================================\n";
passthru("php " . escapeshellarg($projectRoot . '/bin/cleanup.php') . " --force");

// Restore the backup after cleanup
if ($routesBackup !== null) {
    file_put_contents($routesCsvPath, $routesBackup);
}

echo "\n========================================================\n";
echo "🚀 PHASE 2: SCAFFOLDING ARCHITECTURE & ROUTES\n";
echo "========================================================\n";
passthru("php " . escapeshellarg($projectRoot . '/bin/scaffold.php') . " " . escapeshellarg($routesCsvPath));

echo "\n========================================================\n";
echo "🚀 PHASE 3: SETTING UP DATABASE SCHEMA\n";
echo "========================================================\n";
require_once $projectRoot . '/src/autoload.php';

use Parina\Core\Container;
use Parina\Shared\Services\DatabaseSetupServiceInterface;

try {
    $container = new Container();
    $dependenciesConfig = require $projectRoot . '/config/dependencies.php';
    $container->load($dependenciesConfig);

    $setupService = $container->get(DatabaseSetupServiceInterface::class);
    $setupService->setupDatabase();
    echo "  [Database] Schema successfully loaded!\n";
} catch (Throwable $e) {
    echo "  [Database] Error during database setup: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n========================================================\n";
echo "🚀 PHASE 4: BATCH GENERATING CQS REPOSITORIES\n";
echo "========================================================\n";

if (!file_exists($cqsCsvPath)) {
    echo "  [CQS] Warning: CQS file '$cqsCsvPath' not found. Skipping Phase 4.\n";
} else {
    if (($handle = fopen($cqsCsvPath, "r")) !== false) {
        $headers = fgetcsv($handle, 1000, ",");
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if (count($headers) !== count($data)) {
                continue; // Skip malformed rows
            }
            $row = array_combine($headers, $data);

            $feature = trim($row['feature'] ?? '');
            $name = trim($row['name'] ?? '');
            $table = trim($row['table'] ?? '');
            $type = strtolower(trim($row['type'] ?? 'both'));

            if (empty($feature) || empty($name) || empty($table)) {
                continue;
            }

            echo "  [CQS] Processing for Feature '{$feature}', Name '{$name}'...\n";

            if ($type === 'command' || $type === 'both') {
                passthru("php " . escapeshellarg($projectRoot . '/bin/generate-command.php') . " " . escapeshellarg($feature) . " " . escapeshellarg($name) . " " . escapeshellarg($table));
            }
            if ($type === 'query' || $type === 'both') {
                passthru("php " . escapeshellarg($projectRoot . '/bin/generate-query.php') . " " . escapeshellarg($feature) . " " . escapeshellarg($name) . " " . escapeshellarg($table));
            }
        }
        fclose($handle);
    }
}

echo "\n========================================================\n";
echo "✨ ORCHESTRATION COMPLETE! BUILD SUCCESSFUL! ✨\n";
echo "========================================================\n";

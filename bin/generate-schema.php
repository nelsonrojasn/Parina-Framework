#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
     * CLI Database Schema Generator from CSV
     * Usage: php bin/generate-schema.php <path_to_csv_file>
     */

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/src/autoload.php';

use Parina\Shared\Services\SchemaGenerator;
use Parina\Core\Container;
use Parina\Shared\Services\DatabaseSetupServiceInterface;

if ($argc < 2) {
    echo "\033[1;31mError: Missing CSV file argument.\033[0m\n";
    echo "Usage: php bin/generate-schema.php <path_to_csv_file> [--no-interaction]\n";
    exit(1);
}

$csvFile = $argv[1];
$noInteraction = in_array('--no-interaction', $argv);

if (!file_exists($csvFile)) {
    echo "\033[1;31mError: File '$csvFile' not found.\033[0m\n";
    exit(1);
}

$csvContent = file_get_contents($csvFile);
if ($csvContent === false) {
    echo "\033[1;31mError: Could not read file '$csvFile'.\033[0m\n";
    exit(1);
}

$generator = new SchemaGenerator();

echo "========================================================\n";
echo "📊 COMPILING DATABASE SCHEMAS FROM CSV\n";
echo "========================================================\n";

try {
    $schemas = $generator->generateSchemas($csvContent);

    $sqlitePath = $projectRoot . '/database/schema.sqlite.sql';
    $mysqlPath = $projectRoot . '/database/schema.mysql.sql';
    $pgsqlPath = $projectRoot . '/database/schema.pgsql.sql';

    // Ensure database folder exists
    if (!is_dir(dirname($sqlitePath))) {
        mkdir(dirname($sqlitePath), 0755, true);
    }

    file_put_contents($sqlitePath, $schemas['sqlite']);
    echo "\033[1;32m  [SQLite Schema] Generated at: database/schema.sqlite.sql\033[0m\n";

    file_put_contents($mysqlPath, $schemas['mysql']);
    echo "\033[1;32m  [MySQL Schema] Generated at: database/schema.mysql.sql\033[0m\n";

    file_put_contents($pgsqlPath, $schemas['pgsql']);
    echo "\033[1;32m  [PostgreSQL Schema] Generated at: database/schema.pgsql.sql\033[0m\n";

} catch (Throwable $e) {
    echo "\033[1;31mCompilation Error: " . $e->getMessage() . "\033[0m\n";
    exit(1);
}

echo "\n========================================================\n";
echo "⚙️ DATABASE INITIALIZATION\n";
echo "========================================================\n";

$shouldInit = false;
if ($noInteraction) {
    $shouldInit = true;
} else {
    // Check if we are running in a terminal / interactive shell
    if (function_exists('posix_isatty') && posix_isatty(STDIN)) {
        echo "Would you like to initialize/migrate the local SQLite database now? [y/N]: ";
        $input = trim(fgets(STDIN));
        if (strtolower($input) === 'y') {
            $shouldInit = true;
        }
    } else {
        // Safe default for non-interactive runners (e.g. tests/automation) is not to run automatically
        $shouldInit = false;
    }
}

if ($shouldInit) {
    try {
        $container = new Container();
        $dependenciesConfig = require $projectRoot . '/config/dependencies.php';
        $container->load($dependenciesConfig);

        $setupService = $container->get(DatabaseSetupServiceInterface::class);
        $setupService->setupDatabase();
        echo "\033[1;32m  [Database] SQLite database successfully initialized and migrated!\033[0m\n";
    } catch (Throwable $e) {
        echo "\033[1;31m  [Database] Error during database setup: " . $e->getMessage() . "\033[0m\n";
        exit(1);
    }
} else {
    echo "Skipped SQLite database initialization. You can do it manually by running:\n";
    echo "  php bin/orchestrator.php\n";
}

echo "\n✨ Schema generation complete!\n";

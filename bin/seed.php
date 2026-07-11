#!/usr/bin/env php
<?php

use Parina\Core\Config;
use Parina\Core\Container;
use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\SqlGeneratorInterface;
use Parina\Shared\Services\CsvSeeder;

require_once dirname(__DIR__) . '/src/autoload.php';

if ($argc < 3) {
    echo "Usage: php bin/seed.php <table_name> <csv_file> [delimiter]\n";
    echo "Example: php bin/seed.php users data/users.csv\n";
    exit(1);
}

$table = $argv[1];
$csvFile = $argv[2];
$delimiter = $argv[3] ?? ',';

if (!file_exists($csvFile)) {
    fwrite(STDERR, "CSV file not found: {$csvFile}\n");
    exit(1);
}

$container = new Container();
$container->load(require dirname(__DIR__) . '/config/dependencies.php');

$db = $container->get(DatabaseAdapter::class);
$sqlGenerator = $container->get(SqlGeneratorInterface::class);
$seeder = new CsvSeeder($db, $sqlGenerator);
$inserted = $seeder->seedFromCsv($table, $csvFile, ['delimiter' => $delimiter]);

echo "Inserted {$inserted} row(s) into {$table} from {$csvFile}.\n";

#!/usr/bin/env php
<?php

use Parina\Core\Config;
use Parina\Shared\Infrastructure\Db;
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

Db::setConfig(Config::getDbConfig());
Db::init();

$seeder = new CsvSeeder();
$inserted = $seeder->seedFromCsv($table, $csvFile, ['delimiter' => $delimiter]);

echo "Inserted {$inserted} row(s) into {$table} from {$csvFile}.\n";

<?php

namespace Parina\Shared\Services;

use Parina\Shared\Models\BaseModel;

class CsvSeeder
{
    public function seedFromCsv(string $table, string $csvFile, array $options = []): int
    {
        if (!file_exists($csvFile)) {
            throw new \InvalidArgumentException("CSV file not found: {$csvFile}");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV file: {$csvFile}");
        }

        $headers = fgetcsv($handle, 0, $options['delimiter'] ?? ',');
        if ($headers === false) {
            fclose($handle);
            throw new \RuntimeException("CSV file is empty: {$csvFile}");
        }

        $headers = array_map(static fn($header) => trim((string) $header), $headers);
        $inserted = 0;

        while (($row = fgetcsv($handle, 0, $options['delimiter'] ?? ',')) !== false) {
            if ($row === [null] || $row === []) {
                continue;
            }

            $data = [];
            foreach ($headers as $index => $column) {
                $value = $row[$index] ?? null;
                if ($column === '') {
                    continue;
                }
                $data[$column] = $value;
            }

            if ($data === []) {
                continue;
            }

            $columns = array_keys($data);
            $placeholders = implode(', ', array_map(static fn($column) => ':' . $column, $columns));
            $columnList = implode(', ', $columns);

            BaseModel::createIntoTable($table, $data);
            $inserted++;
        }

        fclose($handle);

        return $inserted;
    }
}

<?php
declare(strict_types=1);

namespace Parina\Shared\Services;

use RuntimeException;
use Parina\Shared\Services\SchemaCompilers\SqliteCompiler;
use Parina\Shared\Services\SchemaCompilers\MysqlCompiler;
use Parina\Shared\Services\SchemaCompilers\PgsqlCompiler;

class SchemaGenerator
{
    private static array $reservedKeywords = [
        'select', 'insert', 'update', 'delete', 'create', 'table', 'drop',
        'alter', 'where', 'from', 'join', 'index', 'key', 'foreign', 'primary',
        'default', 'unique', 'null', 'and', 'or', 'not', 'in', 'like', 'is',
        'as', 'on', 'into', 'values', 'set', 'group', 'by', 'having', 'order',
        'limit', 'offset', 'having', 'trigger', 'view', 'procedure', 'function'
    ];

    /**
     * Generate SQL schemas for sqlite, mysql, and pgsql from CSV content.
     *
     * @param string $csvContent
     * @return array Array with keys 'sqlite', 'mysql', 'pgsql'
     * @throws RuntimeException
     */
    public function generateSchemas(string $csvContent): array
    {
        $rows = $this->parseCsv($csvContent);
        $rows = $this->ensureDefaultUsuario($rows, $hasUsuario);
        $this->validateRows($rows);

        $tables = [];
        $dependencies = [];
        $foreignKeys = [];

        foreach ($rows as $row) {
            $this->processRow($row, $tables, $dependencies, $foreignKeys);
        }

        $creationOrder = $this->topologicalSort($tables, $dependencies);
        $dropOrder = array_reverse($creationOrder);

        return [
            'sqlite' => (new SqliteCompiler($creationOrder, $dropOrder, $tables, $foreignKeys, !$hasUsuario))->compile(),
            'mysql'  => (new MysqlCompiler($creationOrder, $dropOrder, $tables, $foreignKeys, !$hasUsuario))->compile(),
            'pgsql'  => (new PgsqlCompiler($creationOrder, $dropOrder, $tables, $foreignKeys, !$hasUsuario))->compile(),
        ];
    }

    private function parseCsv(string $content): array
    {
        $lines = explode("\n", str_replace("\r", "", trim($content)));
        if (empty($lines)) {
            throw new RuntimeException("CSV is empty");
        }

        $headers = str_getcsv(array_shift($lines), ",");
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $this->validateHeaders($headers);

        $hasDefault = in_array('default', $headers);
        $hasReferences = in_array('references', $headers);

        $rows = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $data = str_getcsv($line, ",");
            if (count($data) < 6) { // 6 required headers: table, attribute, type, pk, null, unique
                continue; // Skip malformed rows
            }

            $rows[] = $this->buildCsvRow($headers, $data, $hasDefault, $hasReferences);
        }

        return $rows;
    }

    private function validateHeaders(array $headers): void
    {
        $required = ['table', 'attribute', 'type', 'pk', 'null', 'unique'];
        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                throw new RuntimeException("Missing required CSV header: '{$req}'");
            }
        }
    }

    private function buildCsvRow(array $headers, array $data, bool $hasDefault, bool $hasReferences): array
    {
        $row = [];
        foreach ($headers as $idx => $header) {
            $row[$header] = isset($data[$idx]) ? trim($data[$idx]) : '';
        }

        if (!$hasDefault) {
            $row['default'] = '';
        }
        if (!$hasReferences) {
            $row['references'] = '';
        }

        return $row;
    }

    private function ensureDefaultUsuario(array $rows, &$hasUsuario): array
    {
        $hasUsuario = false;
        foreach ($rows as $row) {
            if (strtolower($row['table']) === 'usuario') {
                $hasUsuario = true;
                break;
            }
        }

        if (!$hasUsuario) {
            $rows = array_merge($rows, $this->getDefaultUsuarioRows());
        }

        return $rows;
    }

    private function processRow(array $row, array &$tables, array &$dependencies, array &$foreignKeys): void
    {
        $table = strtolower($row['table']);
        $attribute = strtolower($row['attribute']);
        
        if (!isset($tables[$table])) {
            $tables[$table] = [];
            $dependencies[$table] = [];
            $foreignKeys[$table] = [];
        }

        $tables[$table][$attribute] = [
            'type' => $row['type'],
            'pk' => $this->parseBool($row['pk']),
            'null' => $this->parseBool($row['null']),
            'unique' => $this->parseBool($row['unique']),
            'default' => $row['default'] !== '' ? $row['default'] : null,
        ];

        if (!empty($row['references'])) {
            $this->parseReferences($table, $attribute, $row['references'], $dependencies, $foreignKeys);
        }
    }

    private function parseReferences(string $table, string $attribute, string $ref, array &$dependencies, array &$foreignKeys): void
    {
        $ref = trim($ref);
        if (preg_match('/^([a-zA-Z0-9_\-]+)(?:\(([a-zA-Z0-9_\-]+)\))?$/', $ref, $matches)) {
            $targetTable = strtolower($matches[1]);
            $targetCol = strtolower($matches[2] ?? 'id');
            
            $dependencies[$table][] = $targetTable;
            $foreignKeys[$table][] = [
                'local' => $attribute,
                'foreign_table' => $targetTable,
                'foreign_column' => $targetCol
            ];
        } else {
            throw new RuntimeException("Invalid reference format: '{$ref}' in table '{$table}' column '{$attribute}'");
        }
    }

    private function validateRows(array $rows): void
    {
        foreach ($rows as $row) {
            $table = strtolower($row['table']);
            $attribute = strtolower($row['attribute']);
            
            if (in_array($table, self::$reservedKeywords)) {
                throw new RuntimeException("Table name '{$table}' is a reserved SQL keyword");
            }
            if (in_array($attribute, self::$reservedKeywords)) {
                throw new RuntimeException("Column name '{$attribute}' in table '{$table}' is a reserved SQL keyword");
            }

            $pk = $this->parseBool($row['pk']);
            $null = $this->parseBool($row['null']);

            if ($pk && $null) {
                throw new RuntimeException("Primary key column '{$attribute}' in table '{$table}' cannot be nullable");
            }
        }
    }

    private function parseBool($val): bool
    {
        if (is_bool($val)) {
            return $val;
        }
        $val = strtolower(trim((string)$val));
        return in_array($val, ['1', 'true', 'yes', 'y', 't']);
    }

    private function topologicalSort(array $tables, array $dependencies): array
    {
        $visited = [];
        $tempMark = [];
        $order = [];

        $visit = function ($node) use (&$visit, &$visited, &$tempMark, &$order, $dependencies, $tables) {
            if (isset($tempMark[$node])) {
                throw new RuntimeException("Circular dependency detected involving table '{$node}'");
            }
            if (!isset($visited[$node])) {
                $tempMark[$node] = true;
                if (isset($dependencies[$node])) {
                    foreach (array_unique($dependencies[$node]) as $dep) {
                        // Only resolve if dependency is part of our tables list
                        if (isset($tables[$dep])) {
                            $visit($dep);
                        }
                    }
                }
                unset($tempMark[$node]);
                $visited[$node] = true;
                $order[] = $node;
            }
        };

        foreach (array_keys($tables) as $table) {
            if (!isset($visited[$table])) {
                $visit($table);
            }
        }

        return $order;
    }

    private function getDefaultUsuarioRows(): array
    {
        return [
            ['table' => 'usuario', 'attribute' => 'id', 'type' => 'INTEGER', 'pk' => '1', 'null' => '0', 'unique' => '0', 'default' => '', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'company_id', 'type' => 'INTEGER', 'pk' => '0', 'null' => '1', 'unique' => '0', 'default' => 'NULL', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'username', 'type' => 'TEXT', 'pk' => '0', 'null' => '0', 'unique' => '1', 'default' => '', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'password', 'type' => 'TEXT', 'pk' => '0', 'null' => '0', 'unique' => '0', 'default' => '', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'email', 'type' => 'TEXT', 'pk' => '0', 'null' => '0', 'unique' => '0', 'default' => '', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'is_active', 'type' => 'INTEGER', 'pk' => '0', 'null' => '0', 'unique' => '0', 'default' => '1', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'deleted', 'type' => 'INTEGER', 'pk' => '0', 'null' => '0', 'unique' => '0', 'default' => '0', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'created_at', 'type' => 'TEXT', 'pk' => '0', 'null' => '0', 'unique' => '0', 'default' => 'CURRENT_TIMESTAMP', 'references' => ''],
            ['table' => 'usuario', 'attribute' => 'updated_at', 'type' => 'TEXT', 'pk' => '0', 'null' => '0', 'unique' => '0', 'default' => 'CURRENT_TIMESTAMP', 'references' => ''],
        ];
    }
}

<?php
declare(strict_types=1);

namespace Parina\Shared\Services;

use RuntimeException;

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
        
        // Ensure default usuario table if not present in CSV
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

        // Validate table and column names
        $this->validateRows($rows);

        // Group rows by table
        $tables = [];
        $dependencies = [];
        $foreignKeys = [];

        foreach ($rows as $row) {
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

            // Parse references / foreign keys
            if (!empty($row['references'])) {
                $ref = trim($row['references']);
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
        }

        // Topological sorting
        $creationOrder = $this->topologicalSort($tables, $dependencies);
        $dropOrder = array_reverse($creationOrder);

        // Compile schemas
        return [
            'sqlite' => $this->compileSqlite($creationOrder, $dropOrder, $tables, $foreignKeys, !$hasUsuario),
            'mysql'  => $this->compileMysql($creationOrder, $dropOrder, $tables, $foreignKeys, !$hasUsuario),
            'pgsql'  => $this->compilePgsql($creationOrder, $dropOrder, $tables, $foreignKeys, !$hasUsuario),
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

        $required = ['table', 'attribute', 'type', 'pk', 'null', 'unique'];
        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                throw new RuntimeException("Missing required CSV header: '{$req}'");
            }
        }

        // Optional headers
        $hasDefault = in_array('default', $headers);
        $hasReferences = in_array('references', $headers);

        $rows = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $data = str_getcsv($line, ",");
            if (count($data) < count($required)) {
                continue; // Skip malformed rows
            }

            $row = [];
            foreach ($headers as $idx => $header) {
                $row[$header] = isset($data[$idx]) ? trim($data[$idx]) : '';
            }

            // Fill default values for optional columns if missing
            if (!$hasDefault) {
                $row['default'] = '';
            }
            if (!$hasReferences) {
                $row['references'] = '';
            }

            $rows[] = $row;
        }

        return $rows;
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

    private function formatDefaultValue(?string $val, string $type): ?string
    {
        if ($val === null) {
            return null;
        }

        $upper = strtoupper($val);
        if ($upper === 'NULL' || $upper === 'CURRENT_TIMESTAMP') {
            return $upper;
        }

        // If numeric type and numeric default, keep as is
        $typeLower = strtolower($type);
        $baseType = preg_match('/^([a-z]+)/', $typeLower, $m) ? $m[1] : $typeLower;
        if (in_array($baseType, ['int', 'integer', 'tinyint', 'smallint', 'bigint', 'decimal', 'numeric', 'float', 'real']) && is_numeric($val)) {
            return $val;
        }

        // If already quoted, keep as is
        if ((str_starts_with($val, "'") && str_ends_with($val, "'")) || (str_starts_with($val, '"') && str_ends_with($val, '"'))) {
            return $val;
        }

        // Wrap string literals in single quotes
        return "'" . str_replace("'", "''", $val) . "'";
    }

    private function compileSqlite(array $creationOrder, array $dropOrder, array $tables, array $foreignKeys, bool $injectUserSeed): string
    {
        $sql = "PRAGMA foreign_keys = ON;\n\n";

        foreach ($dropOrder as $table) {
            $sql .= "DROP TABLE IF EXISTS {$table};\n";
        }
        $sql .= "\n";

        foreach ($creationOrder as $table) {
            $sql .= "CREATE TABLE IF NOT EXISTS {$table} (\n";
            $colDefs = [];

            foreach ($tables[$table] as $col => $info) {
                $typeLower = strtolower($info['type']);
                
                // Specific SQLite type translation
                if ($info['pk'] && in_array($typeLower, ['int', 'integer', 'serial', 'bigint'])) {
                    $dbType = 'INTEGER';
                } elseif ($typeLower === 'string') {
                    $dbType = 'TEXT';
                } elseif ($col === 'created_at' || $col === 'updated_at') {
                    $dbType = 'TEXT';
                } else {
                    $dbType = strtoupper($info['type']);
                }

                $parts = [$col, $dbType];

                if ($info['pk']) {
                    if ($dbType === 'INTEGER') {
                        $parts[] = 'PRIMARY KEY AUTOINCREMENT';
                    } else {
                        $parts[] = 'PRIMARY KEY';
                    }
                } else {
                    if (!$info['null']) {
                        $parts[] = 'NOT NULL';
                    }
                    if ($info['unique']) {
                        $parts[] = 'UNIQUE';
                    }
                    
                    $defVal = $this->formatDefaultValue($info['default'], $dbType);
                    if ($defVal !== null) {
                        $parts[] = "DEFAULT " . $defVal;
                    } elseif ($info['null']) {
                        $parts[] = "DEFAULT NULL";
                    }
                }

                $colDefs[] = "    " . implode(' ', $parts);
            }

            // Add Foreign Keys at the table level
            if (!empty($foreignKeys[$table])) {
                foreach ($foreignKeys[$table] as $fk) {
                    $colDefs[] = "    FOREIGN KEY ({$fk['local']}) REFERENCES {$fk['foreign_table']}({$fk['foreign_column']})";
                }
            }

            $sql .= implode(",\n", $colDefs) . "\n);\n\n";
        }

        if ($injectUserSeed) {
            $sql .= "INSERT OR IGNORE INTO usuario (username, password, email, is_active) VALUES ('admin', '\$2y\$10\$QCHG.PX4JEiR1E1VN/2Freu8QiphVmHFWK8G89SifuNrmvML2F5mu', 'admin@example.com', 1);\n";
        }

        return $sql;
    }

    private function compileMysql(array $creationOrder, array $dropOrder, array $tables, array $foreignKeys, bool $injectUserSeed): string
    {
        $sql = "SET FOREIGN_KEY_CHECKS = 0;\n";
        foreach ($dropOrder as $table) {
            $sql .= "DROP TABLE IF EXISTS {$table};\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

        foreach ($creationOrder as $table) {
            $sql .= "CREATE TABLE IF NOT EXISTS {$table} (\n";
            $colDefs = [];

            foreach ($tables[$table] as $col => $info) {
                $typeLower = strtolower($info['type']);
                
                // Specific MySQL type translation and timestamp auto update
                if ($col === 'created_at') {
                    $dbType = 'TIMESTAMP';
                    $defaultString = 'DEFAULT CURRENT_TIMESTAMP';
                } elseif ($col === 'updated_at') {
                    $dbType = 'TIMESTAMP';
                    $defaultString = 'DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
                } else {
                    if (in_array($typeLower, ['int', 'integer'])) {
                        $dbType = 'INT';
                    } elseif ($typeLower === 'serial') {
                        $dbType = 'INT';
                    } elseif ($typeLower === 'bigint') {
                        $dbType = 'BIGINT';
                    } elseif ($typeLower === 'tinyint') {
                        $dbType = 'TINYINT';
                    } elseif ($typeLower === 'smallint') {
                        $dbType = 'SMALLINT';
                    } elseif (in_array($typeLower, ['text', 'string'])) {
                        $dbType = ($info['pk'] || $info['unique']) ? 'VARCHAR(255)' : 'TEXT';
                    } else {
                        $dbType = strtoupper($info['type']);
                    }
                    
                    $defaultString = '';
                    $defVal = $this->formatDefaultValue($info['default'], $dbType);
                    if ($defVal !== null) {
                        $defaultString = "DEFAULT " . $defVal;
                    } elseif ($info['null'] && !$info['pk']) {
                        $defaultString = "DEFAULT NULL";
                    }
                }

                $parts = [$col, $dbType];

                if ($info['pk']) {
                    if (in_array($typeLower, ['int', 'integer', 'serial', 'bigint'])) {
                        $parts[] = 'AUTO_INCREMENT PRIMARY KEY';
                    } else {
                        $parts[] = 'PRIMARY KEY';
                    }
                } else {
                    if (!$info['null']) {
                        $parts[] = 'NOT NULL';
                    }
                    if ($info['unique']) {
                        $parts[] = 'UNIQUE';
                    }
                    if (!empty($defaultString)) {
                        $parts[] = $defaultString;
                    }
                }

                $colDefs[] = "    " . implode(' ', $parts);
            }

            // Add Foreign Keys at the table level
            if (!empty($foreignKeys[$table])) {
                foreach ($foreignKeys[$table] as $fk) {
                    $colDefs[] = "    FOREIGN KEY ({$fk['local']}) REFERENCES {$fk['foreign_table']}({$fk['foreign_column']})";
                }
            }

            $sql .= implode(",\n", $colDefs) . "\n);\n\n";
        }

        if ($injectUserSeed) {
            $sql .= "INSERT IGNORE INTO usuario (username, password, email, is_active) VALUES ('admin', '\$2y\$10\$QCHG.PX4JEiR1E1VN/2Freu8QiphVmHFWK8G89SifuNrmvML2F5mu', 'admin@example.com', 1);\n";
        }

        return $sql;
    }

    private function compilePgsql(array $creationOrder, array $dropOrder, array $tables, array $foreignKeys, bool $injectUserSeed): string
    {
        $sql = "";
        foreach ($dropOrder as $table) {
            $sql .= "DROP TABLE IF EXISTS {$table} CASCADE;\n";
        }
        $sql .= "\n";

        foreach ($creationOrder as $table) {
            $sql .= "CREATE TABLE IF NOT EXISTS {$table} (\n";
            $colDefs = [];

            foreach ($tables[$table] as $col => $info) {
                $typeLower = strtolower($info['type']);
                
                // Specific PostgreSQL type translation
                if ($col === 'created_at' || $col === 'updated_at') {
                    $dbType = 'TIMESTAMP';
                    $defaultString = 'DEFAULT CURRENT_TIMESTAMP';
                } else {
                    if ($info['pk'] && in_array($typeLower, ['int', 'integer', 'serial'])) {
                        $dbType = 'SERIAL';
                    } elseif ($info['pk'] && $typeLower === 'bigint') {
                        $dbType = 'BIGSERIAL';
                    } elseif (in_array($typeLower, ['int', 'integer'])) {
                        $dbType = 'INT';
                    } elseif ($typeLower === 'bigint') {
                        $dbType = 'BIGINT';
                    } elseif ($typeLower === 'tinyint' || $typeLower === 'smallint') {
                        $dbType = 'SMALLINT';
                    } elseif (in_array($typeLower, ['text', 'string'])) {
                        $dbType = ($info['pk'] || $info['unique']) ? 'VARCHAR(255)' : 'TEXT';
                    } else {
                        $dbType = strtoupper($info['type']);
                    }

                    $defaultString = '';
                    $defVal = $this->formatDefaultValue($info['default'], $dbType);
                    if ($defVal !== null) {
                        $defaultString = "DEFAULT " . $defVal;
                    } elseif ($info['null'] && !$info['pk'] && !str_contains($dbType, 'SERIAL')) {
                        $defaultString = "DEFAULT NULL";
                    }
                }

                $parts = [$col, $dbType];

                if ($info['pk']) {
                    $parts[] = 'PRIMARY KEY';
                } else {
                    if (!$info['null']) {
                        $parts[] = 'NOT NULL';
                    }
                    if ($info['unique']) {
                        $parts[] = 'UNIQUE';
                    }
                    if (!empty($defaultString)) {
                        $parts[] = $defaultString;
                    }
                }

                $colDefs[] = "    " . implode(' ', $parts);
            }

            // Add Foreign Keys at the table level
            if (!empty($foreignKeys[$table])) {
                foreach ($foreignKeys[$table] as $fk) {
                    $colDefs[] = "    FOREIGN KEY ({$fk['local']}) REFERENCES {$fk['foreign_table']}({$fk['foreign_column']})";
                }
            }

            $sql .= implode(",\n", $colDefs) . "\n);\n\n";
        }

        if ($injectUserSeed) {
            $sql .= "INSERT INTO usuario (username, password, email, is_active) VALUES ('admin', '\$2y\$10\$QCHG.PX4JEiR1E1VN/2Freu8QiphVmHFWK8G89SifuNrmvML2F5mu', 'admin@example.com', 1) ON CONFLICT (username) DO NOTHING;\n";
        }

        return $sql;
    }
}

<?php
declare(strict_types=1);

namespace Parina\Shared\Services\SchemaCompilers;

class SqliteCompiler extends AbstractSchemaCompiler
{
    public function compile(): string
    {
        $sql = "PRAGMA foreign_keys = ON;\n\n";

        foreach ($this->dropOrder as $table) {
            $sql .= "DROP TABLE IF EXISTS {$table};\n";
        }
        $sql .= "\n";

        foreach ($this->creationOrder as $table) {
            $sql .= "CREATE TABLE IF NOT EXISTS {$table} (\n";
            $colDefs = [];

            foreach ($this->tables[$table] as $col => $info) {
                $dbType = $this->resolveDbType($col, $info);
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

            if (!empty($this->foreignKeys[$table])) {
                foreach ($this->foreignKeys[$table] as $fk) {
                    $colDefs[] = "    FOREIGN KEY ({$fk['local']}) REFERENCES {$fk['foreign_table']}({$fk['foreign_column']})";
                }
            }

            $sql .= implode(",\n", $colDefs) . "\n);\n\n";
        }

        if ($this->injectUserSeed) {
            $sql .= "INSERT OR IGNORE INTO usuario (username, password, email, is_active) VALUES ('admin', '\$2y\$10\$QCHG.PX4JEiR1E1VN/2Freu8QiphVmHFWK8G89SifuNrmvML2F5mu', 'admin@example.com', 1);\n";
        }

        return $sql;
    }

    private function resolveDbType(string $col, array $info): string
    {
        $typeLower = strtolower($info['type']);
        if ($info['pk'] && in_array($typeLower, ['int', 'integer', 'serial', 'bigint'])) {
            return 'INTEGER';
        }
        if ($typeLower === 'string') {
            return 'TEXT';
        }
        if ($col === 'created_at' || $col === 'updated_at') {
            return 'TEXT';
        }
        return strtoupper($info['type']);
    }
}

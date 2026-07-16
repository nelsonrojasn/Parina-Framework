<?php
declare(strict_types=1);

namespace Parina\Shared\Services\SchemaCompilers;

class MysqlCompiler extends AbstractSchemaCompiler
{
    public function compile(): string
    {
        $sql = "SET FOREIGN_KEY_CHECKS = 0;\n";
        foreach ($this->dropOrder as $table) {
            $sql .= "DROP TABLE IF EXISTS {$table};\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

        foreach ($this->creationOrder as $table) {
            $sql .= "CREATE TABLE IF NOT EXISTS {$table} (\n";
            $colDefs = [];

            foreach ($this->tables[$table] as $col => $info) {
                $dbType = $this->resolveDbType($col, $info);
                $defaultString = $this->resolveDefaultString($col, $info, $dbType);

                $parts = [$col, $dbType];

                if ($info['pk']) {
                    $typeLower = strtolower($info['type']);
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
                    if ($defaultString !== '') {
                        $parts[] = $defaultString;
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
            $sql .= "INSERT IGNORE INTO usuario (username, password, email, is_active) VALUES ('admin', '\$2y\$10\$QCHG.PX4JEiR1E1VN/2Freu8QiphVmHFWK8G89SifuNrmvML2F5mu', 'admin@example.com', 1);\n";
        }

        return $sql;
    }

    private function resolveDbType(string $col, array $info): string
    {
        if ($col === 'created_at' || $col === 'updated_at') {
            return 'TIMESTAMP';
        }

        $typeLower = strtolower($info['type']);
        if (in_array($typeLower, ['int', 'integer', 'serial'])) {
            return 'INT';
        }
        if ($typeLower === 'bigint') {
            return 'BIGINT';
        }
        if ($typeLower === 'tinyint') {
            return 'TINYINT';
        }
        if ($typeLower === 'smallint') {
            return 'SMALLINT';
        }
        if (in_array($typeLower, ['text', 'string'])) {
            return ($info['pk'] || $info['unique']) ? 'VARCHAR(255)' : 'TEXT';
        }
        return strtoupper($info['type']);
    }

    private function resolveDefaultString(string $col, array $info, string $dbType): string
    {
        if ($col === 'created_at') {
            return 'DEFAULT CURRENT_TIMESTAMP';
        }
        if ($col === 'updated_at') {
            return 'DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
        }

        $defVal = $this->formatDefaultValue($info['default'], $dbType);
        if ($defVal !== null) {
            return "DEFAULT " . $defVal;
        }
        if ($info['null'] && !$info['pk']) {
            return "DEFAULT NULL";
        }
        return '';
    }
}

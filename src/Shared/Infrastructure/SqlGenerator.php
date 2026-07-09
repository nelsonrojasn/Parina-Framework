<?php

namespace Parina\Shared\Infrastructure;

class SqlGenerator implements SqlGeneratorInterface
{
    private function formatColumns(array|string $columns): string
    {
        if (is_array($columns)) {
            return implode(', ', $columns);
        }
        return $columns;
    }

    public function selectAll(string $table, array|string $columns): string
    {
        $cols = $this->formatColumns($columns);
        return "SELECT {$cols} FROM {$table}";
    }

    public function selectById(string $table, array|string $columns, string $primaryKey = 'id'): string
    {
        $cols = $this->formatColumns($columns);
        return "SELECT {$cols} FROM {$table} WHERE {$primaryKey} = :id";
    }

    public function selectFirst(string $table, string $condition, array|string $columns): string
    {
        $cols = $this->formatColumns($columns);
        return "SELECT {$cols} FROM {$table} WHERE {$condition}";
    }

    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        return "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    }

    public function update(string $table, array $data, string $primaryKey = 'id'): string
    {
        $fields = array_map(fn($key) => "{$key} = :{$key}", array_keys($data));
        $setClause = implode(', ', $fields);
        return "UPDATE {$table} SET {$setClause} WHERE {$primaryKey} = :_id_where";
    }

    public function delete(string $table, string $primaryKey = 'id'): string
    {
        return "DELETE FROM {$table} WHERE {$primaryKey} = :id";
    }
}

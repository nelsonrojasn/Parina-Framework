<?php

namespace Parina\Shared\Models;

use Parina\Shared\Infrastructure\Db;

abstract class BaseModel
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function all(): array
    {
        $sql = "SELECT * FROM " . static::$table;
        return Db::query($sql)->fetchAll();
    }

    public static function find(mixed $id): ?array
    {
        $sql = "SELECT * FROM " . static::$table . 
               " WHERE " . static::$primaryKey . 
               " = :id" . Db::limit(1);
        $stmt = Db::query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function delete(mixed $id): bool
    {
        $sql = "DELETE FROM " . static::$table . 
               " WHERE " . static::$primaryKey . 
               " = :id";
        $stmt = Db::query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Create new record
     */
    public static function create(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO " . static::$table . 
               " ($columns) VALUES ($placeholders)";
        return (bool)Db::query($sql, $data);
    }

    public static function createIntoTable(string $table, array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ($columns) VALUES ($placeholders)";
        return (bool) Db::query($sql, $data);
    }

    /**
     * Update a record based on his primary key
     */
    public static function update(mixed $id, array $data): bool
    {
        if (empty($data)) return false;

        $fields = array_map(fn($key) => "$key = :$key", array_keys($data));
        $setClause = implode(', ', $fields);

        $sql = "UPDATE " . static::$table . 
               " SET $setClause WHERE " . 
               static::$primaryKey . " = :_id_where";

        $params = $data;
        $params['_id_where'] = $id;

        $stmt = Db::query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Allow children classes to use Db::limit() from the Adapter
     */
    protected static function paginate(int $limit, int $offset = 0): string
    {
        return Db::limit($limit, $offset);
    }
}
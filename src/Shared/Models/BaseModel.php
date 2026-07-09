<?php

namespace Parina\Shared\Models;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\SqlGenerator;
use Parina\Shared\Infrastructure\SqlGeneratorInterface;

abstract class BaseModel
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static ?SqlGeneratorInterface $sqlGenerator = null;
    protected static ?DatabaseAdapter $db = null;

    public static function setDatabaseAdapter(DatabaseAdapter $db): void
    {
        self::$db = $db;
    }

    public static function getDb(): DatabaseAdapter
    {
        if (self::$db === null) {
            throw new \RuntimeException("Database adapter not set on BaseModel.");
        }
        return self::$db;
    }

    public static function setSqlGenerator(SqlGeneratorInterface $generator): void
    {
        self::$sqlGenerator = $generator;
    }

    protected static function getSqlGenerator(): SqlGeneratorInterface
    {
        if (self::$sqlGenerator === null) {
            self::$sqlGenerator = new SqlGenerator();
        }
        return self::$sqlGenerator;
    }

    public static function all(): array
    {
        $sql = self::getSqlGenerator()->selectAll(static::$table, '*');
        return self::getDb()->query($sql)->fetchAll();
    }

    public static function find(mixed $id): ?array
    {
        $sql = self::getSqlGenerator()->selectFirst(static::$table, static::$primaryKey . " = :id", '*') . self::getDb()->getLimitSql(1);
        $stmt = self::getDb()->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function delete(mixed $id): bool
    {
        $sql = self::getSqlGenerator()->delete(static::$table, static::$primaryKey);
        $stmt = self::getDb()->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Create new record
     */
    public static function create(array $data): bool
    {
        $sql = self::getSqlGenerator()->insert(static::$table, $data);
        return (bool)self::getDb()->query($sql, $data);
    }

    public static function createIntoTable(string $table, array $data): bool
    {
        $sql = self::getSqlGenerator()->insert($table, $data);
        return (bool)self::getDb()->query($sql, $data);
    }

    /**
     * Update a record based on his primary key
     */
    public static function update(mixed $id, array $data): bool
    {
        if (empty($data)) return false;

        $sql = self::getSqlGenerator()->update(static::$table, $data, static::$primaryKey);

        $params = $data;
        $params['_id_where'] = $id;

        $stmt = self::getDb()->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Allow child classes to get the limit SQL from the Adapter
     */
    protected static function paginate(int $limit, int $offset = 0): string
    {
        return self::getDb()->getLimitSql($limit, $offset);
    }
}
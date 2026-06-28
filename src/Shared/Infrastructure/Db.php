<?php

namespace Parina\Shared\Infrastructure;

class Db
{
    private static ?DatabaseAdapter $adapter = null;
    private static ?array $config = null;

    public static function init(?DatabaseAdapter $adapter = null): void
    {
        if ($adapter !== null) {
            self::$adapter = $adapter;
        } else {
            self::getAdapter();
        }
    }

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    private static function getAdapter(): DatabaseAdapter
    {
        if (self::$adapter === null) {
            if (self::$config === null) {
                throw new \RuntimeException("Database adapter not initialized and config not set.");
            }
            $driver = self::$config['driver'] ?? 'sqlite';
            self::$adapter = match ($driver) {
                'mysql' => new \Parina\Shared\Infrastructure\Adapters\MySqlAdapter(self::$config),
                'pgsql', 'postgres', 'postgresql' => new \Parina\Shared\Infrastructure\Adapters\PostgreSqlAdapter(self::$config),
                'sqlite', 'default' => new \Parina\Shared\Infrastructure\Adapters\SqliteAdapter(self::$config),
                default => throw new \InvalidArgumentException("Database driver not supported: {$driver}")
            };
        }
        return self::$adapter;
    }

    public static function query(string $sql, $params = [])
    {
        return self::getAdapter()->query($sql, $params);
    }

    public static function exec(string $sql): int
    {
        return self::getAdapter()->exec($sql);
    }

    /**
     * Delegamos la directiva específica al motor actual
     */
    public static function limit(int $limit, int $offset = 0): string
    {
        return self::getAdapter()->getLimitSql($limit, $offset);
    }
}

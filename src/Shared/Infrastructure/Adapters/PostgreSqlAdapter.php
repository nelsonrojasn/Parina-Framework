<?php

namespace Parina\Shared\Infrastructure\Adapters;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use PDO;

class PostgreSqlAdapter implements DatabaseAdapter
{
    private ?PDO $pdo = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function getPdo(): PDO
    {
        if ($this->pdo === null) {
            // En Postgres, el DSN suele ser 'pgsql:host=...;dbname=...;port=5432'
            $this->pdo = new PDO($this->config['dsn'], $this->config['user'], $this->config['pass']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function exec(string $sql): int
    {
        return $this->getPdo()->exec($sql);
    }

    /**
     * Aquí está el cambio sutil de dialecto SQL
     */
    public function getLimitSql(int $limit, int $offset = 0): string
    {
        // MySQL acepta "LIMIT offset, limit", pero el estándar ANSI SQL 
        // (que usa Postgres) exige "LIMIT limit OFFSET offset"
        return " LIMIT $limit OFFSET $offset";
    }
}
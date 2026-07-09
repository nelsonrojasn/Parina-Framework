<?php

namespace Parina\Shared\Infrastructure\Adapters;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use PDO;

class SqlServerAdapter implements DatabaseAdapter
{
    private ?PDO $pdo = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function getPdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $pdo = new PDO($this->config['dsn'], $this->config['user'] ?? null, $this->config['pass'] ?? null);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo = $pdo;
        return $pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        // If OFFSET is present but ORDER BY is missing, inject ORDER BY (SELECT NULL) before OFFSET
        if (stripos($sql, 'OFFSET') !== false && !preg_match('/ORDER\s+BY/i', $sql)) {
            $sql = preg_replace('/(\bOFFSET\b)/i', 'ORDER BY (SELECT NULL) $1', $sql, 1);
        }

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function exec(string $sql): int
    {
        return $this->getPdo()->exec($sql);
    }

    public function getLimitSql(int $limit, int $offset = 0): string
    {
        return " OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";
    }
}

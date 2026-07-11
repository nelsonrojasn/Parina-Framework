<?php
declare(strict_types=1);
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
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        // En Postgres, el DSN suele ser 'pgsql:host=...;dbname=...;port=5432'
        $pdo = new PDO($this->config['dsn'], $this->config['user'], $this->config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo = $pdo;
        return $pdo;
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
<?php
declare(strict_types=1);
namespace Parina\Shared\Infrastructure\Adapters;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use PDO;

class SqliteAdapter implements DatabaseAdapter
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

        // En SQLite, el DSN suele ser "sqlite:/ruta/al/archivo.db" o "sqlite::memory:"
        $pdo = new PDO($this->config['dsn'], null, null, $this->config['params'] ?? []);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Performance optimizations for SQLite3
        $pdo->exec('PRAGMA journal_mode = WAL');           // Write-Ahead Logging
        $pdo->exec('PRAGMA synchronous = NORMAL');         // Balanced speed/safety
        $pdo->exec('PRAGMA cache_size = -64000');          // 64MB cache
        $pdo->exec('PRAGMA temp_store = MEMORY');          // Use RAM for temp
        $pdo->exec('PRAGMA mmap_size = 30000000');         // Memory-mapped I/O
        $pdo->exec('PRAGMA page_size = 4096');             // Page size
        $pdo->exec('PRAGMA busy_timeout = 5000');         // 5s timeout  
        $pdo->exec('PRAGMA foreign_keys = ON');           // Enable foreign key support   

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

    public function getLimitSql(int $limit, int $offset = 0): string
    {
        // SQLite soporta la sintaxis LIMIT offset, limit 
        // o la más estándar LIMIT limit OFFSET offset
        return " LIMIT $limit OFFSET $offset";
    }
}
<?php

namespace Parina\Shared\Infrastructure\Adapters;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use PDO;

class SqliteAdapter implements DatabaseAdapter
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        // En SQLite, el DSN suele ser "sqlite:/ruta/al/archivo.db" o "sqlite::memory:"
        $this->pdo = new PDO($config['dsn'], null, null, $config['params'] ?? []);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Performance optimizations for SQLite3
        $this->pdo->exec('PRAGMA journal_mode = WAL');           // Write-Ahead Logging
        $this->pdo->exec('PRAGMA synchronous = NORMAL');         // Balanced speed/safety
        $this->pdo->exec('PRAGMA cache_size = -64000');          // 64MB cache
        $this->pdo->exec('PRAGMA temp_store = MEMORY');          // Use RAM for temp
        $this->pdo->exec('PRAGMA mmap_size = 30000000');         // Memory-mapped I/O
        $this->pdo->exec('PRAGMA page_size = 4096');             // Page size
        $this->pdo->exec('PRAGMA busy_timeout = 5000');         // 5s timeout  
        $this->pdo->exec('PRAGMA foreign_keys = ON');           // Enable foreign key support   
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function exec(string $sql): int
    {
        return $this->pdo->exec($sql);
    }

    public function getLimitSql(int $limit, int $offset = 0): string
    {
        // SQLite soporta la sintaxis LIMIT offset, limit 
        // o la más estándar LIMIT limit OFFSET offset
        return " LIMIT $limit OFFSET $offset";
    }
}
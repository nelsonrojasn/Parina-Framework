<?php

require_once dirname(__DIR__) . '/src/autoload.php';

// Define constants needed by the views/handlers during CLI tests
if (!defined('PIN_START_TIME')) {
    define('PIN_START_TIME', microtime(true));
}
if (!defined('PIN_START_MEM')) {
    define('PIN_START_MEM', memory_get_usage());
}

// Start PHP session if not active for testing
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Initialize database connection for tests
$dbConfig = \Parina\Core\Config::getDbConfig();
$driver = $dbConfig['driver'] ?? 'sqlite';
$adapter = match ($driver) {
    'mysql' => new \Parina\Shared\Infrastructure\Adapters\MySqlAdapter($dbConfig),
    'pgsql', 'postgres', 'postgresql' => new \Parina\Shared\Infrastructure\Adapters\PostgreSqlAdapter($dbConfig),
    'sqlite', 'default' => new \Parina\Shared\Infrastructure\Adapters\SqliteAdapter($dbConfig),
    default => throw new \InvalidArgumentException("Database driver not supported: {$driver}")
};
\Parina\Shared\Infrastructure\Db::init($adapter);

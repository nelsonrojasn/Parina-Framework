<?php

define('PIN_START_TIME', microtime(true));
define('PIN_START_MEM', memory_get_usage());

//start php session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

use Parina\Core\Router;
use Parina\Core\Kernel;
use Parina\Core\Config;
use Parina\Shared\Infrastructure\Db;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;
use Parina\Shared\Infrastructure\Adapters\MySqlAdapter;
use Parina\Shared\Infrastructure\Adapters\PostgreSqlAdapter;

require_once __DIR__ . '/../src/autoload.php';

//database connection
$dbConfig = Config::getDbConfig();
$driver = $dbConfig['driver'] ?? 'sqlite';
$adapter = match ($driver) {
    'mysql' => new MySqlAdapter($dbConfig),
    'pgsql', 'postgres', 'postgresql' => new PostgreSqlAdapter($dbConfig),
    'sqlite', 'default' => new SqliteAdapter($dbConfig),
    default => throw new \InvalidArgumentException("Database driver not supported: {$driver}")
};
Db::init($adapter);

$router = new Router();

//routes definition
$routes = require '../config/routes.php';
foreach ($routes as $route) {
    $router->add(
        $route['method'],
        $route['path'],
        $route['handler'],
        $route['middleware'] ?? []
    );
}


//Kernel dispatcher
$kernel = new Kernel($router);
$kernel->run();

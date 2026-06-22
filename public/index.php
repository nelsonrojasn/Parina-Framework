<?php

define('PIN_START_TIME', microtime(true));
define('PIN_START_MEM', memory_get_usage());

// Tiempo mínimo entre peticiones en milisegundos (0 para desactivar)
define('RATE_LIMIT_MS', 500);

//cargar la sesion de php
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

require_once '../vendor/autoload.php';

// Initialize Database connection globally
$dbConfig = Config::getDbConfig();
$driver = $dbConfig['driver'] ?? 'sqlite';
$adapter = match ($driver) {
    'mysql' => new MySqlAdapter($dbConfig),
    'pgsql', 'postgres', 'postgresql' => new PostgreSqlAdapter($dbConfig),
    'sqlite', 'default' => new SqliteAdapter($dbConfig)
};
Db::init($adapter);

$router = new Router();

// Public Routes (Loaded dynamically from config/routes.php)
$publicRoutes = require '../config/routes.php';
foreach ($publicRoutes as $route) {
    $router->add(
        $route['method'],
        $route['path'],
        $route['handler'],
        $route['middleware'] ?? []
    );
}

// Encrypted routes resolver based on (/do?=...)
$hashResolver = new Parina\Core\HashResolver([
    'admin/home' => Parina\Modules\Admin\AdminHandler::class,
    'admin/users' => Parina\Modules\Admin\UsersListHandler::class,
    'logout' => Parina\Modules\Private\LogoutHandler::class
]);

// Private Routes
$router->add('GET', '/do', $hashResolver, [
    Parina\Shared\Middlewares\RateLimit::class,
    Parina\Shared\Middlewares\RequestSize::class, 
    Parina\Shared\Middlewares\SameOrigin::class,
    Parina\Shared\Middlewares\Csrf::class,
    Parina\Shared\Middlewares\Auth::class,
    Parina\Shared\Middlewares\Acl::class,
]);


//Kernel dispatcher
$kernel = new Kernel($router);
$kernel->run();

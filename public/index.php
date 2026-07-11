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
use Parina\Core\Container;
use Parina\Shared\Infrastructure\DatabaseAdapter;

require_once __DIR__ . '/../src/autoload.php';

// Instantiate DI container & load dependencies
$container = new Container();
if (file_exists(__DIR__ . '/../config/dependencies.php')) {
    $container->load(require __DIR__ . '/../config/dependencies.php');
}

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
$request = \Parina\Core\Request::capture();
$kernel = new Kernel($router, $container);
$response = $kernel->handle($request);

$emitter = new \Parina\Core\ResponseEmitter();
$emitter->emit($response);

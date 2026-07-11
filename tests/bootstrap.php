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



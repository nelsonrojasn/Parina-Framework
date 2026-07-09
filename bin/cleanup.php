<?php

/**
 * CLI Tool to clean up all demo files (Handlers, Views, Tests)
 * and reset config/routes.php and the SQLite database.
 * 
 * Usage: php bin/cleanup.php [--force]
 */

$force = in_array('--force', $argv);

if (!$force) {
    echo "========================================================\n";
    echo "⚠️  WARNING: PARINA FRAMEWORK CLEANUP COMMAND\n";
    echo "========================================================\n";
    echo "This script will permanently delete all demo files:\n";
    echo "- src/Modules/Admin/ (recursively)\n";
    echo "- src/Modules/Private/ (recursively)\n";
    echo "- src/Modules/Public/AboutHandler.php, LoginFormHandler.php, LoginCheckHandler.php, AutoPurchaseHandler.php\n";
    echo "- src/Modules/Public/Views/about.php, login.php\n";
    echo "- tests/Handlers/ (Demo handler test files)\n";
    echo "- src/Db/app.sqlite (if exists)\n";
    echo "And reset config/routes.php and routes.csv to a pristine state.\n\n";
    echo "Are you sure you want to proceed? (yes/no) [no]: ";

    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    if (trim(strtolower($line)) !== 'yes') {
        echo "Aborted.\n";
        exit(0);
    }
}

echo "Cleaning up demo files...\n";

// Helper function to delete directory recursively
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

$projectRoot = dirname(__DIR__);

// 1. Delete modules directories
$adminDir = $projectRoot . '/src/Modules/Admin';
if (is_dir($adminDir)) {
    deleteDirectory($adminDir);
    echo "Deleted: src/Modules/Admin/\n";
}

$privateDir = $projectRoot . '/src/Modules/Private';
if (is_dir($privateDir)) {
    deleteDirectory($privateDir);
    echo "Deleted: src/Modules/Private/\n";
}

// 2. Delete public demo handlers
$publicHandlers = [
    '/src/Modules/Public/AboutHandler.php',
    '/src/Modules/Public/LoginFormHandler.php',
    '/src/Modules/Public/LoginCheckHandler.php',
    '/src/Modules/Public/AutoPurchaseHandler.php',
];
foreach ($publicHandlers as $file) {
    $filePath = $projectRoot . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "Deleted: " . substr($file, 1) . "\n";
    }
}

// 3. Delete public demo views
$publicViews = [
    '/src/Modules/Public/Views/about.php',
    '/src/Modules/Public/Views/login.php',
];
foreach ($publicViews as $file) {
    $filePath = $projectRoot . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "Deleted: " . substr($file, 1) . "\n";
    }
}

// 4. Delete demo tests
$demoTests = [
    '/tests/Handlers/AboutHandlerTest.php',
    '/tests/Handlers/AdminHandlerTest.php',
    '/tests/Handlers/AutoPurchaseHandlerTest.php',
    '/tests/Handlers/LoginCheckHandlerTest.php',
    '/tests/Handlers/LoginFormHandlerTest.php',
    '/tests/Handlers/LogoutHandlerTest.php',
    '/tests/Handlers/UsersListHandlerTest.php',
];
foreach ($demoTests as $file) {
    $filePath = $projectRoot . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "Deleted: " . substr($file, 1) . "\n";
    }
}

// 5. Delete DB
$dbFile = $projectRoot . '/src/Db/app.sqlite';
if (file_exists($dbFile)) {
    unlink($dbFile);
    echo "Deleted: src/Db/app.sqlite\n";
}

// 6. Reset config/routes.php
$routesFile = $projectRoot . '/config/routes.php';
$pristineRoutes = <<<'PHP'
<?php

// Dynamically generated routes configuration via CLI Scaffolding tool.

return [
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => \Parina\Modules\Public\HomeHandler::class,
        'middleware' => []
    ]
];
PHP;

file_put_contents($routesFile, $pristineRoutes);
echo "Reset: config/routes.php\n";

// 7. Reset routes.csv
$routesCsvFile = $projectRoot . '/routes.csv';
if (file_exists($routesCsvFile)) {
    file_put_contents($routesCsvFile, "Method,Path,HandlerClass,Middlewares,Description\n");
    echo "Reset: routes.csv\n";
}

echo "\n✨ Cleanup complete! Parina Framework is now a fresh, empty canvas.\n";

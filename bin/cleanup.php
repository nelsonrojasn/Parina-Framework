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
    echo "- src/Features/Dashboard/ (recursively)\n";
    echo "- src/Features/UserManagement/ (recursively)\n";
    echo "- src/Features/Authentication/ (recursively)\n";
    echo "- src/Features/AutoPurchase/ (recursively)\n";
    echo "- src/Features/Marketing/Handlers/AboutHandler.php\n";
    echo "- src/Features/Marketing/Views/about.php\n";
    echo "- tests/Features/ (Demo handler test files)\n";
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

// 1. Delete feature directories
$featuresToDelete = [
    '/src/Features/Dashboard',
    '/src/Features/UserManagement',
    '/src/Features/Authentication',
    '/src/Features/AutoPurchase',
];
foreach ($featuresToDelete as $feature) {
    $dirPath = $projectRoot . $feature;
    if (is_dir($dirPath)) {
        deleteDirectory($dirPath);
        echo "Deleted: " . substr($feature, 1) . "/\n";
    }
}

// 2. Delete Marketing demo handler and view
$marketingDemoFiles = [
    '/src/Features/Marketing/Handlers/AboutHandler.php',
    '/src/Features/Marketing/Views/about.php',
];
foreach ($marketingDemoFiles as $file) {
    $filePath = $projectRoot . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "Deleted: " . substr($file, 1) . "\n";
    }
}

// 3. Delete demo tests from tests/Features/
$demoTests = [
    '/tests/Features/Marketing/AboutHandlerTest.php',
    '/tests/Features/Authentication/LoginFormHandlerTest.php',
    '/tests/Features/Authentication/LoginCheckHandlerTest.php',
    '/tests/Features/Authentication/LogoutHandlerTest.php',
    '/tests/Features/Dashboard/AdminHandlerTest.php',
    '/tests/Features/UserManagement/UsersListHandlerTest.php',
    '/tests/Features/AutoPurchase/AutoPurchaseHandlerTest.php',
];
foreach ($demoTests as $file) {
    $filePath = $projectRoot . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "Deleted: " . substr($file, 1) . "\n";
    }
}

// Also delete empty feature test subdirectories if they exist
$featureTestDirs = [
    '/tests/Features/Marketing',
    '/tests/Features/Authentication',
    '/tests/Features/Dashboard',
    '/tests/Features/UserManagement',
    '/tests/Features/AutoPurchase',
];
foreach ($featureTestDirs as $dir) {
    $dirPath = $projectRoot . $dir;
    if (is_dir($dirPath)) {
        $files = array_diff(scandir($dirPath), ['.', '..']);
        if (count($files) === 0) {
            rmdir($dirPath);
        }
    }
}

// 4. Delete DB
$dbFile = $projectRoot . '/src/Db/app.sqlite';
if (file_exists($dbFile)) {
    unlink($dbFile);
    echo "Deleted: src/Db/app.sqlite\n";
}

// 5. Reset config/routes.php
$routesFile = $projectRoot . '/config/routes.php';
$pristineRoutes = <<<'PHP'
<?php

// Dynamically generated routes configuration via CLI Scaffolding tool.

return [
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => \Parina\Features\Marketing\Handlers\HomeHandler::class,
        'middleware' => []
    ]
];
PHP;

file_put_contents($routesFile, $pristineRoutes);
echo "Reset: config/routes.php\n";

// 6. Reset routes.csv
$routesCsvFile = $projectRoot . '/routes.csv';
if (file_exists($routesCsvFile)) {
    file_put_contents($routesCsvFile, "Method,Path,Feature,HandlerName,Middlewares,Description\n");
    echo "Reset: routes.csv\n";
}

echo "\n✨ Cleanup complete! Parina Framework is now a fresh, empty canvas.\n";

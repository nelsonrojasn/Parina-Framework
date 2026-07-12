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
    echo "- src/Features/Database/ (recursively)\n";
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
    '/src/Features/Database',
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

// 3. Delete demo tests from tests/Features/ (recursively deleting entire folders)
$demoTestDirs = [
    '/tests/Features/Authentication',
    '/tests/Features/Dashboard',
    '/tests/Features/UserManagement',
    '/tests/Features/AutoPurchase',
    '/tests/Features/Database',
];
foreach ($demoTestDirs as $dir) {
    $dirPath = $projectRoot . $dir;
    if (is_dir($dirPath)) {
        deleteDirectory($dirPath);
        echo "Deleted: " . substr($dir, 1) . "/\n";
    }
}

// Also clean up specifically the Marketing demo test
$marketingDemoTest = $projectRoot . '/tests/Features/Marketing/AboutHandlerTest.php';
if (file_exists($marketingDemoTest)) {
    unlink($marketingDemoTest);
    echo "Deleted: tests/Features/Marketing/AboutHandlerTest.php\n";
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

// 7. Reset config/dependencies.php
$dependenciesFile = $projectRoot . '/config/dependencies.php';
$pristineDependencies = <<<'PHP'
<?php

// config/dependencies.php

use Parina\Core\Interfaces\ConfigInterface;
use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\Adapters\MySqlAdapter;
use Parina\Shared\Infrastructure\Adapters\PostgreSqlAdapter;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;

return [
    // Bindings (Transient: new instance resolved every time)
    'bindings' => [
        \Parina\Shared\Services\DatabaseSetupServiceInterface::class => \Parina\Shared\Services\DatabaseSetupService::class,
    ],

    // Singletons (Shared: resolved once and cached)
    'singletons' => [
        // Config interface resolves to the AppConfig implementation
        ConfigInterface::class => \Parina\Core\AppConfig::class,

        // Security / Auth / Log Services
        \Parina\Shared\Services\Fsp\FspEngineInterface::class => \Parina\Shared\Services\Fsp\FspEngine::class,
        \Parina\Shared\Services\AclInterface::class => \Parina\Shared\Services\Acl::class,
        \Parina\Shared\Services\AuthInterface::class => \Parina\Shared\Services\SessionAuth::class,
        \Parina\Core\Interfaces\Logger::class => \Parina\Core\FileLogger::class,
        \Parina\Shared\Services\TokenServiceInterface::class => \Parina\Shared\Services\JwtTokenService::class,
        \Parina\Shared\Security\CipherInterface::class => \Parina\Shared\Security\AesCipherService::class,
        \Parina\Shared\Infrastructure\SqlGeneratorInterface::class => \Parina\Shared\Infrastructure\SqlGenerator::class,

        // Repositories (CQS)
        \Parina\Shared\Services\UserQueryRepositoryInterface::class => \Parina\Shared\Services\DbUserQueryRepository::class,
        \Parina\Shared\Services\UserCommandRepositoryInterface::class => \Parina\Shared\Services\DbUserCommandRepository::class,

        // Database drivers registered dynamically
        'db.driver.mysql'  => fn($c) => new MySqlAdapter($c->get(ConfigInterface::class)->getDbConfig()),
        'db.driver.pgsql'  => fn($c) => new PostgreSqlAdapter($c->get(ConfigInterface::class)->getDbConfig()),
        'db.driver.sqlite' => fn($c) => new SqliteAdapter($c->get(ConfigInterface::class)->getDbConfig()),

        // DatabaseAdapter resolves dynamically via factory closure (OCP compliant)
        DatabaseAdapter::class => function (\Parina\Core\Container $container) {
            $config = $container->get(ConfigInterface::class);
            $dbConfig = $config->getDbConfig();
            $driver = $dbConfig['driver'] ?? 'sqlite';

            $driverMap = [
                'postgres'   => 'pgsql',
                'postgresql' => 'pgsql',
                'default'    => 'sqlite',
            ];
            $driver = $driverMap[$driver] ?? $driver;

            $serviceId = "db.driver.{$driver}";
            if (!$container->has($serviceId)) {
                throw new \InvalidArgumentException("Database driver not supported: {$driver}");
            }

            return $container->get($serviceId);
        }
    ],
];
PHP;

file_put_contents($dependenciesFile, $pristineDependencies);
echo "Reset: config/dependencies.php\n";

echo "\n✨ Cleanup complete! Parina Framework is now a fresh, empty canvas.\n";

<?php

/**
 * CLI Scaffolding tool to generate handlers, tests, and config/routes.php from a CSV file.
 * Usage: php bin/scaffold.php <path_to_csv_file>
 */

if ($argc < 2) {
    echo "Error: Missing CSV file argument.\n";
    echo "Usage: php bin/scaffold.php <path_to_csv_file>\n";
    exit(1);
}

$csvFile = $argv[1];

if (!file_exists($csvFile)) {
    echo "Error: File '$csvFile' not found.\n";
    exit(1);
}

// Parse CSV
$rows = [];
if (($handle = fopen($csvFile, "r")) !== false) {
    $headers = fgetcsv($handle, 1000, ",");
    
    // Normalize headers to lowercase
    $headers = array_map(function($header) {
        return strtolower(trim($header));
    }, $headers);

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if (count($headers) !== count($data)) {
            continue; // Skip malformed rows
        }
        $rows[] = array_combine($headers, $data);
    }
    fclose($handle);
}

echo "Parsed " . count($rows) . " routes from CSV.\n";

$routesArray = [];

foreach ($rows as $row) {
    $method = strtoupper(trim($row['method'] ?? 'GET'));
    $path = trim($row['path'] ?? '/');
    $handlerClass = trim($row['handlerclass'] ?? $row['handler'] ?? '');
    $middlewaresRaw = trim($row['middlewares'] ?? $row['middleware'] ?? '');
    $description = trim($row['description'] ?? '');

    if (empty($handlerClass)) {
        echo "Warning: Skipped route '$path' because 'handler' is empty.\n";
        continue;
    }

    // Ensure leading backslash for handler in registration
    $formattedHandlerClass = '\\' . ltrim($handlerClass, '\\');

    // Parse Middlewares
    $middlewares = [];
    if (!empty($middlewaresRaw)) {
        $parts = explode(',', $middlewaresRaw);
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            if (!str_contains($part, '\\')) {
                // Default to Parina\Shared\Middlewares namespace
                $middlewares[] = '\\Parina\\Shared\\Middlewares\\' . $part;
            } else {
                $middlewares[] = '\\' . ltrim($part, '\\');
            }
        }
    }

    // Add to routes configuration array
    $routesArray[] = [
        'method' => $method,
        'path' => $path,
        'handler' => $formattedHandlerClass,
        'middleware' => $middlewares
    ];

    // Scaffold Handler File
    scaffoldHandler($handlerClass, $description);

    // Scaffold Test File
    scaffoldTest($handlerClass);
}

// Generate config/routes.php
writeRoutesConfig($routesArray);

echo "\nScaffolding complete! Run your tests to verify:\n";
echo "./vendor/bin/phpunit tests\n";

/**
 * Convert PSR-4 class name (Parina\...) to actual src/ path
 */
function classToPath(string $className): string
{
    $className = ltrim($className, '\\');
    if (strpos($className, 'Parina\\') === 0) {
        $className = substr($className, 7); // Remove 'Parina\' prefix
        return 'src/' . str_replace('\\', '/', $className) . '.php';
    }
    return 'src/' . str_replace('\\', '/', $className) . '.php';
}

/**
 * Scaffold the handler if it does not exist
 */
function scaffoldHandler(string $handlerClass, string $description): void
{
    $filePath = classToPath($handlerClass);
    
    if (file_exists($filePath)) {
        echo "  [Handler] Already exists: $filePath\n";
        return;
    }

    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Split namespace and class name
    $lastBackslash = strrpos($handlerClass, '\\');
    if ($lastBackslash !== false) {
        $namespace = ltrim(substr($handlerClass, 0, $lastBackslash), '\\');
        $className = substr($handlerClass, $lastBackslash + 1);
    } else {
        $namespace = 'Parina';
        $className = $handlerClass;
    }

    $stub = <<<PHP
<?php

namespace $namespace;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Request;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\HtmlResponse;

/**
 * Description: $description
 */
class $className implements Handler
{
    public function handle(Request \$request): Response
    {
        return new HtmlResponse("<h1>$description</h1>");
    }
}
PHP;

    file_put_contents($filePath, $stub);
    echo "  [Handler] Created: $filePath\n";
}

/**
 * Scaffold unit test for the handler if it does not exist
 */
function scaffoldTest(string $handlerClass): void
{
    // Extract short class name
    $lastBackslash = strrpos($handlerClass, '\\');
    $className = ($lastBackslash !== false) ? substr($handlerClass, $lastBackslash + 1) : $handlerClass;
    
    $testFilePath = "tests/Handlers/{$className}Test.php";
    $testNamespace = "Tests\\Handlers";

    // Auto-detect Features namespace to place tests symmetrically
    $parts = explode('\\', ltrim($handlerClass, '\\'));
    if (count($parts) >= 3 && $parts[0] === 'Parina' && $parts[1] === 'Features') {
        $featureName = $parts[2];
        $testFilePath = "tests/Features/{$featureName}/{$className}Test.php";
        $testNamespace = "Tests\\Features\\{$featureName}";
    }

    if (file_exists($testFilePath)) {
        echo "  [Test] Already exists: $testFilePath\n";
        return;
    }

    $dir = dirname($testFilePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $formattedFqn = '\\' . ltrim($handlerClass, '\\');

    $stub = <<<PHP
<?php

namespace $testNamespace;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use $formattedFqn;

class {$className}Test extends TestCase
{
    public function test_handler_returns_valid_response()
    {
        \$handler = new $className();
        \$request = new Request([], [], [], [], []);
        
        \$response = \$handler->handle(\$request);
        
        \$this->assertNotNull(\$response);
        \$this->assertGreaterThanOrEqual(200, \$response->getStatus());
        \$this->assertLessThan(400, \$response->getStatus());
    }
}
PHP;

    file_put_contents($testFilePath, $stub);
    echo "  [Test] Created: $testFilePath\n";
}

/**
 * Write config/routes.php file
 */
function writeRoutesConfig(array $routesArray): void
{
    $configPath = 'config/routes.php';
    $dir = dirname($configPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $content = "<?php\n\n";
    $content .= "// Dynamically generated routes configuration via CLI Scaffolding tool.\n\n";
    $content .= "return [\n";

    foreach ($routesArray as $route) {
        $content .= "    [\n";
        $content .= "        'method' => '{$route['method']}',\n";
        $content .= "        'path' => '{$route['path']}',\n";
        $content .= "        'handler' => {$route['handler']}::class,\n";

        if (empty($route['middleware'])) {
            $content .= "        'middleware' => []\n";
        } else {
            $content .= "        'middleware' => [\n";
            $mwStrings = [];
            foreach ($route['middleware'] as $mw) {
                $mwStrings[] = "            {$mw}::class";
            }
            $content .= implode(",\n", $mwStrings) . "\n";
            $content .= "        ]\n";
        }

        $content .= "    ],\n";
    }

    // Trim last comma
    if (!empty($routesArray)) {
        $content = rtrim($content, ",\n") . "\n";
    }

    $content .= "];\n";

    file_put_contents($configPath, $content);
    echo "  [Config] Updated: $configPath\n";
}

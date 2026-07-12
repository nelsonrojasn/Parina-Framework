<?php

/**
 * CLI Linter for Parina Framework.
 * Verifies PHP syntax, DAG stability of the DI graph, and CQS repository isolation.
 * 
 * Usage: php bin/linter.php
 */

require_once dirname(__DIR__) . '/src/autoload.php';


// Helper for output formatting with ANSI colors
function out(string $text, string $color = null) {
    $colors = [
        'green'  => "\033[1;32m",
        'red'    => "\033[1;31m",
        'yellow' => "\033[1;33m",
        'blue'   => "\033[1;34m",
        'purple' => "\033[1;35m",
        'cyan'   => "\033[1;36m",
        'bold'   => "\033[1m",
        'reset'  => "\033[0m"
    ];
    if ($color && isset($colors[$color])) {
        echo $colors[$color] . $text . $colors['reset'] . "\n";
    } else {
        echo $text . "\n";
    }
}

out("========================================================", 'purple');
out("🔍  PARINA FRAMEWORK ARCHITECTURAL LINTER  🔍", 'bold');
out("========================================================", 'purple');

$hasErrors = false;
$warningsCount = 0;
$errorsCount = 0;

// ========================================================
// STEP 1: PHP Syntax Linting (php -l)
// ========================================================
out("\n[1/3] Running PHP Syntax Check...", 'blue');

function findPhpFiles(string $dir): array {
    $files = [];
    if (!is_dir($dir)) {
        return [];
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getRealPath();
        }
    }
    return $files;
}

$dirsToLint = [
    dirname(__DIR__) . '/src',
    dirname(__DIR__) . '/bin',
    dirname(__DIR__) . '/config',
    dirname(__DIR__) . '/tests'
];

$phpFiles = [];
foreach ($dirsToLint as $dir) {
    $phpFiles = array_merge($phpFiles, findPhpFiles($dir));
}
$phpFiles = array_unique($phpFiles);

$syntaxErrors = [];
foreach ($phpFiles as $file) {
    $output = [];
    $retval = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $retval);
    if ($retval !== 0) {
        $syntaxErrors[] = [
            'file' => $file,
            'error' => implode("\n", $output)
        ];
    }
}

if (count($syntaxErrors) > 0) {
    out("❌ Syntax check failed! Found " . count($syntaxErrors) . " file(s) with errors.", 'red');
    foreach ($syntaxErrors as $err) {
        out("  - File: " . $err['file'], 'red');
        out("    Error: " . trim($err['error']), 'yellow');
    }
    $hasErrors = true;
    $errorsCount += count($syntaxErrors);
} else {
    out("✅ Syntax check passed. Checked " . count($phpFiles) . " PHP files.", 'green');
}

// ========================================================
// STEP 2: DI Dependency Graph (DAG) Stability Check
// ========================================================
out("\n[2/3] Checking DI Graph DAG Stability...", 'blue');

$dependenciesFile = dirname(__DIR__) . '/config/dependencies.php';
if (!file_exists($dependenciesFile)) {
    out("⚠️  Dependencies config file not found.", 'yellow');
    $warningsCount++;
} else {
    $config = require $dependenciesFile;
    
    // Extract registered classes
    $classes = array_merge(
        array_keys($config['bindings'] ?? []),
        array_values($config['bindings'] ?? []),
        array_keys($config['singletons'] ?? []),
        array_values($config['singletons'] ?? [])
    );

    // Resolve interface/abstract to concrete binding where possible
    $resolvedMap = [];
    foreach ($config['bindings'] ?? [] as $key => $val) {
        if (is_string($key) && is_string($val)) {
            $resolvedMap[$key] = $val;
        }
    }
    foreach ($config['singletons'] ?? [] as $key => $val) {
        if (is_string($key) && is_string($val)) {
            $resolvedMap[$key] = $val;
        }
    }

    $nodes = [];
    foreach ($classes as $class) {
        if (is_string($class) && (class_exists($class) || interface_exists($class))) {
            $nodes[] = $class;
        }
    }
    $nodes = array_unique($nodes);

    // Helper to get constructor dependencies
    $getConstructorDeps = function(string $className) use ($resolvedMap) {
        if (interface_exists($className)) {
            return [];
        }

        try {
            $reflector = new ReflectionClass($className);
            $constructor = $reflector->getConstructor();
            if (is_null($constructor)) {
                return [];
            }

            $dependencies = [];
            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $depName = $type->getName();
                    // Resolve interface dependency to its concrete class
                    if (isset($resolvedMap[$depName])) {
                        $dependencies[] = $resolvedMap[$depName];
                    } else {
                        $dependencies[] = $depName;
                    }
                }
            }
            return $dependencies;
        } catch (Throwable $e) {
            return [];
        }
    };

    // Build adjacency list
    $graph = [];
    foreach ($nodes as $node) {
        $graph[$node] = $getConstructorDeps($node);
    }

    // Cycle detection using DFS with coloring
    // 0 = unvisited, 1 = visiting, 2 = visited
    $visited = [];
    foreach ($nodes as $node) {
        $visited[$node] = 0;
    }

    $hasCycle = false;
    $cyclePath = [];

    // Local function for DFS cycle detection with stack capture
    $hasCycleDfs = function (string $node, array $graph, array &$visited, array &$path, array &$cyclePath) use (&$hasCycleDfs): bool {
        $visited[$node] = 1; // Visiting
        $path[] = $node;

        $neighbors = $graph[$node] ?? [];
        foreach ($neighbors as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $visited[$neighbor] = 0;
            }

            if ($visited[$neighbor] === 1) {
                // Cycle detected
                $idx = array_search($neighbor, $path);
                if ($idx !== false) {
                    $cyclePath = array_slice($path, (int) $idx);
                    $cyclePath[] = $neighbor;
                } else {
                    $cyclePath = [$node, $neighbor];
                }
                return true;
            }

            if ($visited[$neighbor] === 0) {
                if ($hasCycleDfs($neighbor, $graph, $visited, $path, $cyclePath)) {
                    return true;
                }
            }
        }

        array_pop($path);
        $visited[$node] = 2; // Visited
        return false;
    };

    foreach ($nodes as $node) {
        if ($visited[$node] === 0) {
            $path = [];
            if ($hasCycleDfs($node, $graph, $visited, $path, $cyclePath)) {
                $hasCycle = true;
                break;
            }
        }
    }

    if ($hasCycle) {
        out("❌ DI Graph Cycle detected! Stability check failed.", 'red');
        out("  Cycle path: " . implode(" -> ", $cyclePath), 'red');
        $hasErrors = true;
        $errorsCount++;
    } else {
        out("✅ DI Graph verified as a Directed Acyclic Graph (DAG). Stability OK.", 'green');
    }
}

// ========================================================
// STEP 3: CQS Repository Isolation Check
// ========================================================
out("\n[3/3] Checking CQS Repository Isolation...", 'blue');

$srcPhpFiles = findPhpFiles(dirname(__DIR__) . '/src');
$cqsViolations = [];
$cqsWarnings = [];

foreach ($srcPhpFiles as $file) {
    $content = file_get_contents($file);
    
    // Extract namespace and class/interface name
    $namespace = '';
    if (preg_match('/namespace\s+([^;]+);/', $content, $nsMatch)) {
        $namespace = trim($nsMatch[1]);
    }
    
    if (preg_match('/(class|interface)\s+(\w+)/', $content, $classMatch)) {
        $type = $classMatch[1];
        $name = $classMatch[2];
        $fqn = $namespace ? $namespace . '\\' . $name : $name;
    } else {
        continue;
    }

    // Check if it's a Repository or RepositoryInterface
    $isRepository = (
        str_contains($name, 'Repository') || 
        ((class_exists($fqn) || interface_exists($fqn)) && (
            is_subclass_of($fqn, 'Parina\Shared\Services\UserQueryRepositoryInterface') ||
            is_subclass_of($fqn, 'Parina\Shared\Services\UserCommandRepositoryInterface')
        ))
    );

    if ($isRepository) {
        // Enforce naming convention: must contain Query or Command
        $isQuery = str_contains($name, 'Query');
        $isCommand = str_contains($name, 'Command');

        if (!$isQuery && !$isCommand) {
            $cqsViolations[] = [
                'file' => $file,
                'class' => $fqn,
                'rule' => "Nombre del repositorio no especifica Command o Query. Debe seguir el patrón CQS."
            ];
            continue;
        }

        try {
            $reflector = new ReflectionClass($fqn);
            $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                // Skip constructor, destructor, and magic methods
                if ($method->isConstructor() || $method->isDestructor() || str_starts_with($method->getName(), '__')) {
                    continue;
                }

                $methodName = $method->getName();

                if ($isQuery) {
                    // Query Repository checks
                    // 1. Query methods must NOT return void
                    if ($method->hasReturnType()) {
                        $returnType = $method->getReturnType();
                        if ($returnType instanceof ReflectionNamedType && $returnType->getName() === 'void') {
                            $cqsViolations[] = [
                                'file' => $file,
                                'class' => $fqn,
                                'rule' => "El método Query '{$methodName}' retorna void. Los métodos de consulta deben devolver datos."
                            ];
                        }
                    }

                    // 2. Concrete class implementations should not contain modifying SQL keywords or call mutating SqlGenerator methods
                    if (!$reflector->isInterface()) {
                        $startLine = $method->getStartLine();
                        $endLine = $method->getEndLine();
                        if ($startLine > 0 && $endLine > 0) {
                            $fileLines = file($file);
                            $body = implode("", array_slice($fileLines, $startLine - 1, $endLine - $startLine + 1));

                            // Check mutating SqlGenerator calls
                            if (preg_match('/->(insert|update|delete)\s*\(/i', $body, $matches)) {
                                $cqsViolations[] = [
                                    'file' => $file,
                                    'class' => $fqn,
                                    'rule' => "El método Query '{$methodName}' llama al método de mutación '{$matches[1]}' de SqlGenerator."
                                ];
                            }

                            // Check raw modifying SQL statements
                            if (preg_match('/(\bINSERT\s+INTO\b|\bUPDATE\s+\w+|\bDELETE\s+FROM\b)/i', $body, $matches)) {
                                $cqsViolations[] = [
                                    'file' => $file,
                                    'class' => $fqn,
                                    'rule' => "El método Query '{$methodName}' contiene una sentencia SQL de modificación ('{$matches[1]}')."
                                ];
                            }
                        }
                    }
                }

                if ($isCommand) {
                    // Command Repository checks
                    // 1. Command methods must only return void, bool, or int
                    if ($method->hasReturnType()) {
                        $returnType = $method->getReturnType();
                        $allowedTypes = ['void', 'bool', 'int', 'null'];
                        
                        if ($returnType instanceof ReflectionNamedType) {
                            $typeName = $returnType->getName();
                            if (!in_array($typeName, $allowedTypes)) {
                                $cqsViolations[] = [
                                    'file' => $file,
                                    'class' => $fqn,
                                    'rule' => "El método Command '{$methodName}' declara un tipo de retorno no permitido '{$typeName}'. Solo se permite void, bool o int."
                                ];
                            }
                        } elseif (class_exists('ReflectionUnionType') && $returnType instanceof ReflectionUnionType) {
                            foreach ($returnType->getTypes() as $subType) {
                                if ($subType instanceof ReflectionNamedType) {
                                    $typeName = $subType->getName();
                                    if (!in_array($typeName, $allowedTypes)) {
                                        $cqsViolations[] = [
                                            'file' => $file,
                                            'class' => $fqn,
                                            'rule' => "El método Command '{$methodName}' declara un tipo de retorno de unión que contiene '{$typeName}'. Solo se permite void, bool o int."
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    // 2. Command methods should not have read-like names
                    if (preg_match('/^(find|get|select|read)[A-Z]/', $methodName)) {
                        $cqsWarnings[] = [
                            'file' => $file,
                            'class' => $fqn,
                            'rule' => "El método Command '{$methodName}' parece ser una consulta (comienza por find/get/select/read). Considera moverlo a un QueryRepository."
                        ];
                    }
                }
            }
        } catch (Throwable $e) {
            $cqsWarnings[] = [
                'file' => $file,
                'class' => $fqn,
                'rule' => "No se pudo realizar la reflexión en la clase: " . $e->getMessage()
            ];
        }
    }

    // Check if Handlers/Middlewares under src/Features/ directly inject DatabaseAdapter
    if (str_contains($file, '/src/Features/') && !$isRepository) {
        try {
            $reflector = new ReflectionClass($fqn);
            if ($reflector->isInstantiable()) {
                $constructor = $reflector->getConstructor();
                if ($constructor) {
                    foreach ($constructor->getParameters() as $parameter) {
                        $type = $parameter->getType();
                        if ($type instanceof ReflectionNamedType) {
                            $typeName = $type->getName();
                            if ($typeName === 'Parina\Shared\Infrastructure\DatabaseAdapter' || $typeName === 'DatabaseAdapter') {
                                $cqsViolations[] = [
                                    'file' => $file,
                                    'class' => $fqn,
                                    'rule' => "Inyección directa de DatabaseAdapter detectada en una clase de característica (Feature). Utilice repositorios CQS o servicios en su lugar."
                                ];
                            }
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
}

if (count($cqsViolations) > 0) {
    out("❌ CQS Repository Isolation violations found!", 'red');
    foreach ($cqsViolations as $violation) {
        out("  - Class: " . $violation['class'] . " (" . basename($violation['file']) . ")", 'red');
        out("    Rule: " . $violation['rule'], 'yellow');
    }
    $hasErrors = true;
    $errorsCount += count($cqsViolations);
} else {
    out("✅ CQS Repository Isolation verified. Repositories adhere to separation rules.", 'green');
}

if (count($cqsWarnings) > 0) {
    out("\n⚠️  CQS Isolation warnings/notices:", 'yellow');
    foreach ($cqsWarnings as $warning) {
        out("  - Class: " . $warning['class'] . " (" . basename($warning['file']) . ")", 'yellow');
        out("    Notice: " . $warning['rule'], 'cyan');
    }
    $warningsCount += count($cqsWarnings);
}

// ========================================================
// FINAL SUMMARY
// ========================================================
out("\n========================================================", 'purple');
out("📊  LINTER SUMMARY", 'bold');
out("========================================================", 'purple');

if ($hasErrors) {
    out("STATUS: FAILED ❌", 'red');
    out("Total Errors: {$errorsCount}", 'red');
    out("Total Warnings: {$warningsCount}", 'yellow');
    out("Please fix the highlighted issues.", 'yellow');
    exit(1);
} else {
    out("STATUS: SUCCESS ✅", 'green');
    out("Total Checked Files: " . count($phpFiles), 'green');
    out("Total Warnings: {$warningsCount}", 'yellow');
    out("Parina Framework is architecturally stable and consistent!", 'green');
    exit(0);
}

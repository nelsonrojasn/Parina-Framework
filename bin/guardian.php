<?php

/**
 * Parina Guardian Architectural Auditor & Linter for Parina Framework.
 * Verifies PHP syntax, DAG stability of the DI graph, DIP interface purity,
 * CQS isolation, Domain HTTP agnosticism, Slim Handlers, View boundaries,
 * and Feature coupling/monolith metrics.
 * 
 * Usage: php bin/guardian.php
 */

require_once dirname(__DIR__) . '/src/autoload.php';

// Helper for output formatting with ANSI colors
function out(string $text, ?string $color = null) {
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
out("🛡️   PARINA GUARDIAN ARCHITECTURAL AUDITOR  🛡️", 'bold');
out("========================================================", 'purple');

$hasErrors = false;
$warningsCount = 0;
$errorsCount = 0;

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

// Storage for priorities and metrics
$priorities = [];

// ========================================================
// STEP 1: PHP Syntax & Core Stability Check
// ========================================================
$step1Status = "🟢 PASS";
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
    $step1Status = "🔴 ERROR (" . count($syntaxErrors) . " syntax errors)";
    $hasErrors = true;
    $errorsCount += count($syntaxErrors);
    foreach ($syntaxErrors as $err) {
        $priorities[] = [
            'title' => "Error de sintaxis en " . basename($err['file']),
            'impact' => 'ALTO',
            'category' => 'Maintainability',
            'weight' => 10
        ];
    }
}
out(sprintf("%-52s %s", "[1/5] PHP Syntax & Core Stability", $step1Status), str_contains($step1Status, 'ERROR') ? 'red' : 'cyan');
if (count($syntaxErrors) > 0) {
    out("  ❌ Syntax check failed! Found " . count($syntaxErrors) . " file(s) with errors.", 'red');
    foreach ($syntaxErrors as $err) {
        out("    - File: " . $err['file'], 'red');
        out("      Error: " . trim($err['error']), 'yellow');
    }
}

// ========================================================
// STEP 2: DI Graph DAG Stability & DIP Interface Purity
// ========================================================
$step2Status = "🟢 PASS";
$dependenciesFile = dirname(__DIR__) . '/config/dependencies.php';
$hasCycle = false;
$cyclePath = [];

if (!file_exists($dependenciesFile)) {
    $warningsCount++;
} else {
    $config = require $dependenciesFile;
    
    $classes = array_merge(
        array_keys($config['bindings'] ?? []),
        array_values($config['bindings'] ?? []),
        array_keys($config['singletons'] ?? []),
        array_values($config['singletons'] ?? [])
    );

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

    $graph = [];
    foreach ($nodes as $node) {
        $graph[$node] = $getConstructorDeps($node);
    }

    $visited = [];
    foreach ($nodes as $node) {
        $visited[$node] = 0;
    }

    $hasCycleDfs = function (string $node, array $graph, array &$visited, array &$path, array &$cyclePath) use (&$hasCycleDfs): bool {
        $visited[$node] = 1;
        $path[] = $node;

        $neighbors = $graph[$node] ?? [];
        foreach ($neighbors as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $visited[$neighbor] = 0;
            }

            if ($visited[$neighbor] === 1) {
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
        $visited[$node] = 2;
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
        $hasErrors = true;
        $errorsCount++;
        $priorities[] = [
            'title' => "Ciclo detectado en el grafo DI: " . implode(" -> ", $cyclePath),
            'impact' => 'ALTO',
            'category' => 'Maintainability',
            'weight' => 9
        ];
    }
}

// DIP Interface Purity Check
$srcPhpFiles = findPhpFiles(dirname(__DIR__) . '/src');
$dipViolations = [];

foreach ($srcPhpFiles as $file) {
    $content = file_get_contents($file);
    if (!str_contains($content, 'interface ')) {
        continue;
    }

    if (preg_match('/interface\s+(\w+)/', $content, $intMatch)) {
        $interfaceName = $intMatch[1];
        
        if (preg_match_all('/use\s+([^;]+);/', $content, $useMatches)) {
            foreach ($useMatches[1] as $imported) {
                $imported = trim($imported);
                if (
                    str_contains($imported, '\Db') ||
                    str_ends_with($imported, 'Handler') ||
                    (str_contains($imported, '\Services\\') && !str_ends_with($imported, 'Interface') && !str_contains($imported, '\Interfaces\\'))
                ) {
                    $dipViolations[] = [
                        'file' => $file,
                        'interface' => $interfaceName,
                        'rule' => "La interfaz '{$interfaceName}' depende de la clase concreta '{$imported}'. Las interfaces deben depender únicamente de abstracciones (DIP)."
                    ];
                    $priorities[] = [
                        'title' => "La interfaz {$interfaceName} depende de la clase concreta " . basename(str_replace('\\', '/', $imported)),
                        'impact' => 'ALTO',
                        'category' => 'DDD Compliance',
                        'weight' => 8
                    ];
                }
            }
        }
    }
}

if ($hasCycle || count($dipViolations) > 0) {
    $step2Status = "🔴 ERROR (" . (count($dipViolations) + ($hasCycle ? 1 : 0)) . " violations)";
    $hasErrors = true;
    $errorsCount += count($dipViolations);
}
out(sprintf("%-52s %s", "[2/5] DI Graph DAG & DIP Interface Purity", $step2Status), str_contains($step2Status, 'ERROR') ? 'red' : 'cyan');
if ($hasCycle) {
    out("  ❌ DI Graph Cycle detected! Path: " . implode(" -> ", $cyclePath), 'red');
}
if (count($dipViolations) > 0) {
    out("  ❌ DIP Interface Purity violations found!", 'red');
    foreach ($dipViolations as $v) {
        out("    - " . $v['interface'] . ": " . $v['rule'], 'yellow');
    }
}

// ========================================================
// STEP 3: CQS Repository Isolation & Domain HTTP Agnosticism
// ========================================================
$step3Status = "🟢 PASS";
$cqsViolations = [];
$cqsWarnings = [];
$httpAgnosticViolations = [];

foreach ($srcPhpFiles as $file) {
    $content = file_get_contents($file);
    
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

    $isRepository = (
        str_contains($name, 'Repository') || 
        ((class_exists($fqn) || interface_exists($fqn)) && (
            is_subclass_of($fqn, 'Parina\Shared\Services\UserQueryRepositoryInterface') ||
            is_subclass_of($fqn, 'Parina\Shared\Services\UserCommandRepositoryInterface')
        ))
    );

    if ($isRepository) {
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
                if ($method->isConstructor() || $method->isDestructor() || str_starts_with($method->getName(), '__')) {
                    continue;
                }

                $methodName = $method->getName();

                if ($isQuery) {
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

                    if (!$reflector->isInterface()) {
                        $startLine = $method->getStartLine();
                        $endLine = $method->getEndLine();
                        if ($startLine > 0 && $endLine > 0) {
                            $fileLines = file($file);
                            $body = implode("", array_slice($fileLines, $startLine - 1, $endLine - $startLine + 1));

                            if (preg_match('/->(insert|update|delete)\s*\(/i', $body, $matches)) {
                                $cqsViolations[] = [
                                    'file' => $file,
                                    'class' => $fqn,
                                    'rule' => "El método Query '{$methodName}' llama al método de mutación '{$matches[1]}' de SqlGenerator."
                                ];
                            }

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
                        }
                    }

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
            // Ignore
        }
    }

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
            // Ignore
        }
    }

    $isDomainLayer = (
        str_contains($file, '/Services/') ||
        str_contains($file, '/Commands/') ||
        str_contains($file, '/Queries/') ||
        str_contains($file, '/Models/')
    );

    if ($isDomainLayer && !str_contains($file, '/Handlers/') && !str_contains($file, '/Middlewares/')) {
        if (preg_match('/\b(RequestInterface|HtmlResponse|RedirectResponse|\$_GET|\$_POST|\$_SESSION|\$_COOKIE|\$_SERVER)\b/', $content, $httpMatch)) {
            $httpAgnosticViolations[] = [
                'file' => $file,
                'class' => $fqn,
                'rule' => "La clase de dominio contiene referencias al concepto u objeto HTTP '{$httpMatch[1]}'. La capa de dominio debe ser agnóstica del protocolo HTTP."
            ];
            $priorities[] = [
                'title' => "La clase de dominio {$name} contiene acoplamiento HTTP ({$httpMatch[1]})",
                'impact' => 'ALTO',
                'category' => 'DDD Compliance',
                'weight' => 8
            ];
        }
    }
}

if (count($cqsViolations) > 0 || count($httpAgnosticViolations) > 0) {
    $step3Status = "🔴 ERROR (" . (count($cqsViolations) + count($httpAgnosticViolations)) . " violations)";
    $hasErrors = true;
    $errorsCount += count($cqsViolations) + count($httpAgnosticViolations);
} elseif (count($cqsWarnings) > 0) {
    $step3Status = "🟡 NOTICE (" . count($cqsWarnings) . " notices)";
    $warningsCount += count($cqsWarnings);
}

out(sprintf("%-52s %s", "[3/5] CQS Isolation & Domain HTTP Agnosticism", $step3Status), str_contains($step3Status, 'ERROR') ? 'red' : (str_contains($step3Status, 'NOTICE') ? 'yellow' : 'cyan'));
if (count($cqsViolations) > 0) {
    out("  ❌ CQS Repository Isolation violations found!", 'red');
    foreach ($cqsViolations as $v) {
        out("    - Class: " . $v['class'] . " -> " . $v['rule'], 'yellow');
    }
}
if (count($httpAgnosticViolations) > 0) {
    out("  ❌ Domain HTTP Agnosticism violations found!", 'red');
    foreach ($httpAgnosticViolations as $v) {
        out("    - Class: " . $v['class'] . " -> " . $v['rule'], 'yellow');
    }
}

// ========================================================
// STEP 4: Slim Handler Architecture & View Isolation Check
// ========================================================
$step4Status = "🟢 PASS";
$handlerViolations = [];
$handlerWarnings = [];
$viewViolations = [];

$totalHandlers = 0;
$handlersUnder50 = 0;

foreach ($srcPhpFiles as $file) {
    $content = file_get_contents($file);
    
    $isView = (
        str_contains($file, '/Views/') ||
        str_contains($file, '/Layouts/') ||
        str_contains($file, '/Partials/')
    );

    if ($isView) {
        if (preg_match('/new\s+\\\\?Parina\\\\(Features|Shared|Core)\\\\[A-Za-z0-9\\\\]*(Service|Repository|DatabaseAdapter|Container)/i', $content, $viewMatch)) {
            $viewViolations[] = [
                'file' => $file,
                'rule' => "La plantilla de vista instancial directamente la clase '{$viewMatch[0]}'. Las vistas deben ser puramente declarativas y de presentación."
            ];
            $priorities[] = [
                'title' => "La vista " . basename($file) . " instancial directamente la clase de servicio " . basename(str_replace('\\', '/', $viewMatch[0])),
                'impact' => 'ALTO',
                'category' => 'Architecture',
                'weight' => 8
            ];
        }
        continue;
    }

    $namespace = '';
    if (preg_match('/namespace\s+([^;]+);/', $content, $nsMatch)) {
        $namespace = trim($nsMatch[1]);
    }
    
    if (preg_match('/class\s+(\w+)/', $content, $classMatch)) {
        $name = $classMatch[1];
        $fqn = $namespace ? $namespace . '\\' . $name : $name;
    } else {
        continue;
    }

    $isHandler = (
        str_contains($file, '/Handlers/') || 
        str_ends_with($name, 'Handler') || 
        ((class_exists($fqn) || interface_exists($fqn)) && is_subclass_of($fqn, 'Parina\Core\Interfaces\Handler'))
    );

    if (!$isHandler) {
        continue;
    }

    $totalHandlers++;

    $htmlPattern = '/<(html|div|form|table|tr|td|th|input|button|select|textarea|label|h[1-6]|p|span|link|style)\b[^>]*>/i';
    if (preg_match($htmlPattern, $content)) {
        $handlerViolations[] = [
            'file' => $file,
            'class' => $fqn,
            'rule' => "El Handler contiene maquetación HTML inline. La lógica de presentación debe delegarse a una Vista (View template)."
        ];
        $priorities[] = [
            'title' => "{$name} contiene maquetación HTML inline",
            'impact' => 'ALTO',
            'category' => 'Architecture',
            'weight' => 9
        ];
    }

    $sqlPattern = '/\b(SELECT\s+.+\s+FROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i';
    if (preg_match($sqlPattern, $content)) {
        $handlerViolations[] = [
            'file' => $file,
            'class' => $fqn,
            'rule' => "El Handler contiene sentencias SQL directas. La interacción con la base de datos debe delegarse a Repositorios CQS o Modelos."
        ];
        $priorities[] = [
            'title' => "{$name} contiene sentencias SQL directas",
            'impact' => 'ALTO',
            'category' => 'Architecture',
            'weight' => 9
        ];
    }

    try {
        if (class_exists($fqn)) {
            $reflector = new ReflectionClass($fqn);
            if ($reflector->hasMethod('handle')) {
                $method = $reflector->getMethod('handle');
                $startLine = $method->getStartLine();
                $endLine = $method->getEndLine();

                if ($startLine > 0 && $endLine > 0) {
                    $fileLines = file($file);
                    $methodLines = array_slice($fileLines, $startLine - 1, $endLine - $startLine + 1);

                    $effectiveLines = 0;
                    foreach ($methodLines as $line) {
                        $trimmed = trim($line);
                        if ($trimmed === '' || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '*')) {
                            continue;
                        }
                        $effectiveLines++;
                    }

                    if ($effectiveLines <= 50) {
                        $handlersUnder50++;
                    } else {
                        $severity = 'NOTICE';
                        $impact = 'BAJO';
                        $weight = 3;

                        if ($effectiveLines > 150) {
                            $severity = 'WARNING (CRITICAL)';
                            $impact = 'ALTO';
                            $weight = 7;
                        } elseif ($effectiveLines > 80) {
                            $severity = 'WARNING';
                            $impact = 'MEDIO';
                            $weight = 5;
                        }

                        $handlerWarnings[] = [
                            'file' => $file,
                            'class' => $fqn,
                            'lines' => $effectiveLines,
                            'severity' => $severity,
                            'impact' => $impact,
                            'rule' => "El método handle() contiene {$effectiveLines} líneas efectivas de código."
                        ];

                        $priorities[] = [
                            'title' => "{$name} tiene {$effectiveLines} líneas efectivas en handle()",
                            'impact' => $impact,
                            'category' => 'Handler Slimness',
                            'weight' => $weight
                        ];
                    }
                }
            }
        }
    } catch (Throwable $e) {
        // Ignore
    }
}

if (count($handlerViolations) > 0 || count($viewViolations) > 0) {
    $step4Status = "🔴 ERROR (" . (count($handlerViolations) + count($viewViolations)) . " violations)";
    $hasErrors = true;
    $errorsCount += count($handlerViolations) + count($viewViolations);
} elseif (count($handlerWarnings) > 0) {
    $step4Status = "🟡 NOTICE (" . count($handlerWarnings) . " handlers candidates for extraction)";
    $warningsCount += count($handlerWarnings);
}

out(sprintf("%-52s %s", "[4/5] Slim Handler & View Layer Boundaries", $step4Status), str_contains($step4Status, 'ERROR') ? 'red' : (str_contains($step4Status, 'NOTICE') ? 'yellow' : 'cyan'));
if (count($handlerViolations) > 0) {
    out("  ❌ Slim Handler Architecture violations found!", 'red');
    foreach ($handlerViolations as $v) {
        out("    - Class: " . $v['class'] . " -> " . $v['rule'], 'yellow');
    }
}
if (count($viewViolations) > 0) {
    out("  ❌ View Layer Boundary violations found!", 'red');
    foreach ($viewViolations as $v) {
        out("    - File: " . basename($v['file']) . " -> " . $v['rule'], 'yellow');
    }
}

// ========================================================
// STEP 5: Feature Isolation & Monolith Coupling Metrics Audit
// ========================================================
$step5Status = "🟢 PASS";
$featuresDir = dirname(__DIR__) . '/src/Features';
$featureIsolationViolations = [];
$featureStats = [];
$totalFeatureLines = 0;

if (is_dir($featuresDir)) {
    $featureFolders = array_filter(scandir($featuresDir), fn($f) => $f !== '.' && $f !== '..' && is_dir($featuresDir . '/' . $f));

    foreach ($featureFolders as $feature) {
        $featureFiles = findPhpFiles($featuresDir . '/' . $feature);
        $lineCount = 0;
        $importedFeatures = [];

        foreach ($featureFiles as $ffile) {
            $fcontent = file_get_contents($ffile);
            $lines = count(file($ffile));
            $lineCount += $lines;

            if (preg_match_all('/use\s+Parina\\\\Features\\\\(\w+)\\\\/', $fcontent, $matches)) {
                foreach ($matches[1] as $targetFeature) {
                    if ($targetFeature !== $feature) {
                        $importedFeatures[$targetFeature] = true;

                        $featureIsolationViolations[] = [
                            'file' => $ffile,
                            'feature' => $feature,
                            'targetFeature' => $targetFeature,
                            'rule' => "La Feature '{$feature}' contiene una dependencia directa hacia la Feature interna '{$targetFeature}'."
                        ];
                        $priorities[] = [
                            'title' => "La Feature {$feature} depende directamente de la Feature interna {$targetFeature}",
                            'impact' => 'ALTO',
                            'category' => 'Feature Isolation',
                            'weight' => 9
                        ];
                    }
                }
            }
        }

        $featureStats[$feature] = [
            'files' => count($featureFiles),
            'lines' => $lineCount,
            'fanOut' => array_keys($importedFeatures)
        ];
        $totalFeatureLines += $lineCount;
    }
}

$featureMetricWarnings = [];
$maxFeaturePct = 0;
$maxFeatureName = '';

foreach ($featureStats as $fname => $stats) {
    $pct = $totalFeatureLines > 0 ? round(($stats['lines'] / $totalFeatureLines) * 100, 1) : 0;
    if ($pct > $maxFeaturePct) {
        $maxFeaturePct = $pct;
        $maxFeatureName = $fname;
    }

    $fanOutCount = count($stats['fanOut']);
    if ($fanOutCount > 2) {
        $featureMetricWarnings[] = "La Feature '{$fname}' consume {$fanOutCount} Features distintas.";
        $priorities[] = [
            'title' => "La Feature {$fname} consume {$fanOutCount} Features distintas (Fan-Out elevado)",
            'impact' => 'MEDIO',
            'category' => 'Feature Balance',
            'weight' => 5
        ];
    }

    if ($pct > 30.0 && count($featureStats) > 1) {
        $featureMetricWarnings[] = "La Feature '{$fname}' concentra el {$pct}% del código total.";
        $priorities[] = [
            'title' => "{$fname} ocupa el {$pct}% del código total del sistema (Riesgo de Monolito)",
            'impact' => ($pct > 50.0 ? 'ALTO' : 'MEDIO'),
            'category' => 'Feature Balance',
            'weight' => ($pct > 50.0 ? 8 : 6)
        ];
    }
}

if (count($featureIsolationViolations) > 0) {
    $step5Status = "🔴 ERROR (" . count($featureIsolationViolations) . " cross-feature violations)";
    $hasErrors = true;
    $errorsCount += count($featureIsolationViolations);
} elseif (count($featureMetricWarnings) > 0) {
    $step5Status = "🟡 NOTICE (" . count($featureMetricWarnings) . " balance observation)";
    $warningsCount += count($featureMetricWarnings);
}

out(sprintf("%-52s %s", "[5/5] Feature Isolation & Monolith Metrics", $step5Status), str_contains($step5Status, 'ERROR') ? 'red' : (str_contains($step5Status, 'NOTICE') ? 'yellow' : 'cyan'));
if (count($featureIsolationViolations) > 0) {
    out("  ❌ Feature Isolation violations found!", 'red');
    foreach ($featureIsolationViolations as $v) {
        out("    - Feature: " . $v['feature'] . " -> " . $v['targetFeature'] . " (" . basename($v['file']) . ")", 'yellow');
    }
}

// ========================================================
// CALCULATE ARCHITECTURE SCORES & CATEGORY BREAKDOWN
// ========================================================

// 1. Maintainability (0-100)
$maintainability = 100;
if (count($syntaxErrors) > 0) $maintainability -= 100;
if ($hasCycle) $maintainability -= 40;
foreach ($handlerWarnings as $hw) {
    if ($hw['lines'] > 150) $maintainability -= 5;
    elseif ($hw['lines'] > 80) $maintainability -= 3;
    else $maintainability -= 1;
}
$maintainability = (int) max(0, min(100, $maintainability));

// 2. Architecture (0-100)
$architecture = 100;
$architecture -= count($handlerViolations) * 20;
$architecture -= count($viewViolations) * 20;
$architecture = (int) max(0, min(100, $architecture));

// 3. DDD Compliance (0-100)
$dddCompliance = 100;
$dddCompliance -= count($cqsViolations) * 15;
$dddCompliance -= count($httpAgnosticViolations) * 15;
$dddCompliance -= count($dipViolations) * 15;
$dddCompliance = (int) max(0, min(100, $dddCompliance));

// 4. Feature Isolation (0-100)
$featureIsolation = 100;
$featureIsolation -= count($featureIsolationViolations) * 25;
$featureIsolation = (int) max(0, min(100, $featureIsolation));

// 5. Handler Slimness (0-100)
$handlerSlimness = $totalHandlers > 0 ? (int) round(($handlersUnder50 / $totalHandlers) * 100) : 100;

// 6. Feature Balance (0-100)
$featureBalance = 100;
if ($maxFeaturePct > 30.0 && count($featureStats) > 1) {
    $excess = $maxFeaturePct - 30.0;
    $featureBalance -= (int) round($excess * 1.05);
}
foreach ($featureStats as $fname => $st) {
    if (count($st['fanOut']) > 2) {
        $featureBalance -= 10;
    }
}
$featureBalance = (int) max(0, min(100, $featureBalance));

// Global Architecture Score
$architectureScore = (int) round(($maintainability + $architecture + $dddCompliance + $featureIsolation + $handlerSlimness + $featureBalance) / 6);

// Progress Bar
$barWidth = 20;
$filled = (int) round(($architectureScore / 100) * $barWidth);
$empty = $barWidth - $filled;
$progressBar = str_repeat("█", $filled) . str_repeat("░", $empty);

// System Status
$systemStatusText = "🟢 HEALTHY";
if ($hasErrors) {
    $systemStatusText = "🔴 UNHEALTHY";
} elseif ($architectureScore < 80 || $warningsCount >= 5) {
    $systemStatusText = "🟡 ATTENTION RECOMMENDED";
}

// Sort Priorities by Weight DESC
usort($priorities, fn($a, $b) => $b['weight'] <=> $a['weight']);

// ========================================================
// EXECUTIVE REPORT PRESENTATION
// ========================================================

out("\n--------------------------------------------------------", 'purple');
out("📊  ARCHITECTURE SCORE", 'bold');
out("--------------------------------------------------------", 'purple');

out("Score:  [{$progressBar}] {$architectureScore}/100", 'bold');
out("Status: {$systemStatusText}\n");

out("Category Breakdown:", 'bold');
out(sprintf("  %-24s %3d / 100", "- Architecture Standards", $architecture), $architecture >= 90 ? 'green' : 'yellow');
out(sprintf("  %-24s %3d / 100", "- DDD / CQS Compliance", $dddCompliance), $dddCompliance >= 90 ? 'green' : 'yellow');
out(sprintf("  %-24s %3d / 100", "- Feature Isolation", $featureIsolation), $featureIsolation >= 90 ? 'green' : 'yellow');
out(sprintf("  %-24s %3d / 100", "- Maintainability", $maintainability), $maintainability >= 90 ? 'green' : 'yellow');
out(sprintf("  %-24s %3d / 100", "- Handler Slimness", $handlerSlimness), $handlerSlimness >= 80 ? 'green' : 'yellow');
out(sprintf("  %-24s %3d / 100", "- Feature Balance", $featureBalance), $featureBalance >= 80 ? 'green' : 'yellow');

out("\n--------------------------------------------------------", 'purple');
out("📌  TOP ARCHITECTURAL PRIORITIES", 'bold');
out("--------------------------------------------------------", 'purple');

if (count($priorities) === 0) {
    out("  ✔  No architectural priorities or bottlenecks found! Excellent design.", 'green');
} else {
    $topCount = min(5, count($priorities));
    for ($i = 0; $i < $topCount; $i++) {
        $p = $priorities[$i];
        $impactColor = $p['impact'] === 'ALTO' ? 'red' : ($p['impact'] === 'MEDIO' ? 'yellow' : 'cyan');
        out(sprintf("  %d. %s", $i + 1, $p['title']));
        out(sprintf("     [Impacto: %s | Categoría: %s]", $p['impact'], $p['category']), $impactColor);
    }
}

out("\n--------------------------------------------------------", 'purple');
out("📝  GUARDIAN VERDICT", 'bold');
out("--------------------------------------------------------", 'purple');

if ($hasErrors) {
    out('"Critical architectural violations detected. Fix the highlighted issues to restore framework stability."', 'red');
} elseif ($architectureScore >= 90) {
    out('"The architecture is healthy and structurally sound. Future work should prioritize refactoring large Handlers and splitting ' . $maxFeatureName . ' into smaller Features."', 'green');
} else {
    out('"The architecture is functional and compliant with core rules. Addressing top priorities will further improve maintainability and feature balance."', 'yellow');
}

out("\n========================================================", 'purple');
out("📊  GUARDIAN REPORT SUMMARY", 'bold');
out("========================================================", 'purple');

if ($hasErrors) {
    out("STATUS: FAILED ❌", 'red');
    out("Total Errors: {$errorsCount}", 'red');
    out("Total Warnings/Notices: {$warningsCount}", 'yellow');
    out("Parina Guardian rejected the build. Please fix the highlighted architectural errors.", 'yellow');
    exit(1);
} else {
    out("STATUS: SUCCESS ✅", 'green');
    out("Total Checked Files: " . count($phpFiles), 'green');
    out("Total Warnings/Notices: {$warningsCount}", 'yellow');
    out("Parina Framework is architecturally secure, modular and compliant!", 'green');
    exit(0);
}

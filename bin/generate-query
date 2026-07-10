<?php

/**
 * CLI Tool to generate Query repositories in Parina Framework.
 * Usage: php bin/generate-query <feature> <name> [table_name]
 */

if ($argc < 3) {
    echo "Error: Missing arguments.\n";
    echo "Usage: php bin/generate-query <feature> <name> [table_name]\n";
    exit(1);
}

$feature = ucfirst(trim($argv[1]));
$name = ucfirst(trim($argv[2]));
$table = trim($argv[3] ?? strtolower($name));

$interfaceName = "{$name}QueryRepositoryInterface";
$concreteName = "Db{$name}QueryRepository";
$testName = "Db{$name}QueryRepositoryTest";

// File Paths (Placed in Shared/Services to allow DatabaseAdapter injection under CQS rules)
$interfacePath = "src/Shared/Services/{$feature}/Queries/{$interfaceName}.php";
$concretePath = "src/Shared/Services/{$feature}/Queries/{$concreteName}.php";
$testPath = "tests/Shared/Services/{$feature}/Queries/{$testName}.php";

// Create directories if they do not exist
@mkdir(dirname($interfacePath), 0755, true);
@mkdir(dirname($testPath), 0755, true);

// 1. Generate Interface
if (!file_exists($interfacePath)) {
    $interfaceContent = <<<PHP
<?php
namespace Parina\Shared\Services\\{$feature}\Queries;

interface {$interfaceName}
{
    public function findById(int \$id): ?array;
    public function all(): array;
}
PHP;
    file_put_contents($interfacePath, $interfaceContent);
    echo "  [Query Interface] Created: {$interfacePath}\n";
} else {
    echo "  [Query Interface] Already exists: {$interfacePath}\n";
}

// 2. Generate Concrete DB Class
if (!file_exists($concretePath)) {
    $concreteContent = <<<PHP
<?php
namespace Parina\Shared\Services\\{$feature}\Queries;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\SqlGeneratorInterface;
use Parina\Shared\Infrastructure\SqlGenerator;

class {$concreteName} implements {$interfaceName}
{
    private SqlGeneratorInterface \$sqlGenerator;

    public function __construct(
        private DatabaseAdapter \$db,
        ?SqlGeneratorInterface \$sqlGenerator = null
    ) {
        \$this->sqlGenerator = \$sqlGenerator ?? new SqlGenerator();
    }

    public function findById(int \$id): ?array
    {
        \$sql = \$this->sqlGenerator->selectFirst('{$table}', 'id = :id', '*') . \$this->db->getLimitSql(1);
        \$stmt = \$this->db->query(\$sql, ['id' => \$id]);
        return \$stmt->fetch() ?: null;
    }

    public function all(): array
    {
        \$sql = \$this->sqlGenerator->selectAll('{$table}', '*');
        return \$this->db->query(\$sql)->fetchAll();
    }
}
PHP;
    file_put_contents($concretePath, $concreteContent);
    echo "  [Query Repository] Created: {$concretePath}\n";
} else {
    echo "  [Query Repository] Already exists: {$concretePath}\n";
}

// 3. Generate Integration Test
if (!file_exists($testPath)) {
    $testContent = <<<PHP
<?php
namespace Tests\Shared\Services\\{$feature}\Queries;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\\{$feature}\Queries\\{$concreteName};
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;

class {$testName} extends TestCase
{
    private SqliteAdapter \$adapter;
    private {$concreteName} \$repository;

    protected function setUp(): void
    {
        \$config = [
            'dsn' => 'sqlite::memory:',
            'params' => []
        ];
        \$this->adapter = new SqliteAdapter(\$config);

        \$this->adapter->exec("CREATE TABLE IF NOT EXISTS {$table} (
            id INTEGER PRIMARY KEY AUTOINCREMENT
        )");

        \$this->repository = new {$concreteName}(\$this->adapter);
    }

    public function test_find_by_id_returns_null_when_empty()
    {
        \$this->assertNull(\$this->repository->findById(1));
    }

    public function test_all_returns_empty_array_when_empty()
    {
        \$this->assertEmpty(\$this->repository->all());
    }
}
PHP;
    file_put_contents($testPath, $testContent);
    echo "  [Query Test] Created: {$testPath}\n";
} else {
    echo "  [Query Test] Already exists: {$testPath}\n";
}

// 4. Register in config/dependencies.php
$dependenciesPath = 'config/dependencies.php';
if (file_exists($dependenciesPath)) {
    $dependenciesContent = file_get_contents($dependenciesPath);
    $bindingLine = "        \\Parina\\Shared\\Services\\{$feature}\\Queries\\{$interfaceName}::class => \\Parina\\Shared\\Services\\{$feature}\\Queries\\{$concreteName}::class,";
    
    if (str_contains($dependenciesContent, $bindingLine)) {
        echo "  [Dependencies] Already registered.\n";
    } else {
        $cqsBlockPattern = "/(\/\/ Repositories \(CQS\)\n)/";
        if (preg_match($cqsBlockPattern, $dependenciesContent)) {
            $updatedContent = preg_replace(
                $cqsBlockPattern,
                "\\1" . $bindingLine . "\n",
                $dependenciesContent
            );
            file_put_contents($dependenciesPath, $updatedContent);
            echo "  [Dependencies] Automatically registered query repository mapping.\n";
        } else {
            echo "  [Dependencies] Warning: Could not auto-register. Please add this manually:\n";
            echo "    " . trim($bindingLine) . "\n";
        }
    }
}

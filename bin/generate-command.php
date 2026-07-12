<?php

/**
 * CLI Tool to generate Command repositories in Parina Framework.
 * Usage: php bin/generate-command <feature> <name> [table_name]
 */

if ($argc < 3) {
    echo "Error: Missing arguments.\n";
    echo "Usage: php bin/generate-command <feature> <name> [table_name]\n";
    exit(1);
}

$feature = ucfirst(trim($argv[1]));
$name = ucfirst(trim($argv[2]));
$table = trim($argv[3] ?? strtolower($name));

$interfaceName = "{$name}CommandRepositoryInterface";
$concreteName = "Db{$name}CommandRepository";
$testName = "Db{$name}CommandRepositoryTest";

// File Paths (Placed in Features directory under strict FDA rules)
$interfacePath = "src/Features/{$feature}/Commands/{$interfaceName}.php";
$concretePath = "src/Features/{$feature}/Commands/{$concreteName}.php";
$testPath = "tests/Features/{$feature}/Commands/{$testName}.php";

// Create directories if they do not exist
@mkdir(dirname($interfacePath), 0755, true);
@mkdir(dirname($testPath), 0755, true);

// 1. Generate Interface
if (!file_exists($interfacePath)) {
    $interfaceContent = <<<PHP
<?php
namespace Parina\Features\\{$feature}\Commands;

interface {$interfaceName}
{
    public function save(array \$data): bool;
    public function delete(int \$id): bool;
}
PHP;
    file_put_contents($interfacePath, $interfaceContent);
    echo "  [Command Interface] Created: {$interfacePath}\n";
} else {
    echo "  [Command Interface] Already exists: {$interfacePath}\n";
}

// 2. Generate Concrete DB Class
if (!file_exists($concretePath)) {
    $concreteContent = <<<PHP
<?php
namespace Parina\Features\\{$feature}\Commands;

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

    public function save(array \$data): bool
    {
        if (isset(\$data['id'])) {
            \$id = \$data['id'];
            \$updateData = \$data;
            unset(\$updateData['id']);

            \$sql = \$this->sqlGenerator->update('{$table}', \$updateData, 'id');
            \$params = \$updateData;
            \$params['_id_where'] = \$id;

            \$stmt = \$this->db->query(\$sql, \$params);
            return \$stmt->rowCount() > 0;
        } else {
            \$sql = \$this->sqlGenerator->insert('{$table}', \$data);
            return (bool)\$this->db->query(\$sql, \$data);
        }
    }

    public function delete(int \$id): bool
    {
        \$sql = \$this->sqlGenerator->delete('{$table}', 'id');
        \$stmt = \$this->db->query(\$sql, ['id' => \$id]);
        return \$stmt->rowCount() > 0;
    }
}
PHP;
    file_put_contents($concretePath, $concreteContent);
    echo "  [Command Repository] Created: {$concretePath}\n";
} else {
    echo "  [Command Repository] Already exists: {$concretePath}\n";
}

// 3. Generate Integration Test
if (!file_exists($testPath)) {
    $testContent = <<<PHP
<?php
namespace Tests\Features\\{$feature}\Commands;

use PHPUnit\Framework\TestCase;
use Parina\Features\\{$feature}\Commands\\{$concreteName};
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
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255)
        )");

        \$this->repository = new {$concreteName}(\$this->adapter);
    }

    public function test_save_and_delete_operations()
    {
        // Test Insert
        \$data = ['name' => 'Test'];
        \$saved = \$this->repository->save(\$data);
        \$this->assertTrue(\$saved);

        // Test Delete
        \$deleted = \$this->repository->delete(1);
        \$this->assertTrue(\$deleted);
    }
}
PHP;
    file_put_contents($testPath, $testContent);
    echo "  [Command Test] Created: {$testPath}\n";
} else {
    echo "  [Command Test] Already exists: {$testPath}\n";
}

// 4. Register in config/dependencies.php
$dependenciesPath = 'config/dependencies.php';
if (file_exists($dependenciesPath)) {
    $dependenciesContent = file_get_contents($dependenciesPath);
    $bindingLine = "        \\Parina\\Features\\{$feature}\\Commands\\{$interfaceName}::class => \\Parina\\Features\\{$feature}\\Commands\\{$concreteName}::class,";
    
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
            echo "  [Dependencies] Automatically registered command repository mapping.\n";
        } else {
            echo "  [Dependencies] Warning: Could not auto-register. Please add this manually:\n";
            echo "    " . trim($bindingLine) . "\n";
        }
    }
}

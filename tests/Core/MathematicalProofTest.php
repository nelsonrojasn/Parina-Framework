<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\Container;
use Parina\Shared\Services\UserQueryRepositoryInterface;
use Parina\Shared\Services\UserCommandRepositoryInterface;
use Parina\Shared\Infrastructure\DatabaseAdapter;
use ReflectionClass;
use ReflectionNamedType;
use Exception;

class MathematicalProofTest extends TestCase
{
    private Container $container;
    private DatabaseAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->container = new Container();
        $this->container->load(require dirname(dirname(__DIR__)) . '/config/dependencies.php');
        
        $this->adapter = $this->container->get(DatabaseAdapter::class);
    }

    /**
     * Helper to compute a database-level checksum of the users table data.
     */
    private function getUsersTableChecksum(): string
    {
        $stmt = $this->adapter->query("SELECT SUM(id) as sum_id, COUNT(id) as count_id FROM users");
        $result = $stmt->fetch();
        return json_encode($result);
    }

    /**
     * EXPERIMENT: Empirical verification of CQS database invariance.
     * Asserts that Queries (Q) do not alter the database state (checksum),
     * while Commands (C) do alter the database state.
     */
    public function test_queries_preserve_database_state_cqs_invariance()
    {
        $queryRepo = $this->container->get(UserQueryRepositoryInterface::class);
        $commandRepo = $this->container->get(UserCommandRepositoryInterface::class);

        // 1. Snapshot database checksum before query
        $checksumBeforeQuery = $this->getUsersTableChecksum();

        // 2. Execute Query Operation (Read)
        $queryRepo->findByUsername('admin');

        // 3. Snapshot database checksum after query
        $checksumAfterQuery = $this->getUsersTableChecksum();

        // ASERCIÓN CQS 1: El estado de la base de datos es invariante bajo consultas
        $this->assertEquals(
            $checksumBeforeQuery,
            $checksumAfterQuery,
            "CQS Violation: Query operation modified the database state!"
        );

        // 4. Snapshot database checksum before command
        $checksumBeforeCommand = $this->getUsersTableChecksum();

        // 5. Execute Command Operation (Write)
        $randomUser = 'tmp_' . uniqid();
        $commandRepo->save([
            'username' => $randomUser,
            'email' => $randomUser . '@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'company_id' => 1
        ]);

        // 6. Snapshot database checksum after command
        $checksumAfterCommand = $this->getUsersTableChecksum();

        // ASERCIÓN CQS 2: El comando obligatoriamente debe provocar una mutación de estado
        $this->assertNotEquals(
            $checksumBeforeCommand,
            $checksumAfterCommand,
            "CQS Violation: Command operation did not alter the database state!"
        );

        // Cleanup: remove the temporary user to preserve database state
        $user = $queryRepo->findByUsername($randomUser);
        if ($user) {
            $commandRepo->delete((int)$user['id']);
        }
    }

    /**
     * EXPERIMENT: Empirical verification of DI Dependency Graph Aciclicity.
     * Scans all singletons and dependencies in dependencies.php and performs
     * DFS cycle detection to guarantee that the Dependency Graph is a DAG.
     */
    public function test_di_container_has_no_dependency_cycles_dag_verification()
    {
        $config = require dirname(dirname(__DIR__)) . '/config/dependencies.php';
        $classes = array_merge(
            array_keys($config['bindings'] ?? []),
            array_values($config['bindings'] ?? []),
            array_keys($config['singletons'] ?? []),
            array_values($config['singletons'] ?? [])
        );

        // Filter out closures and validate only class names that exist
        $nodes = [];
        foreach ($classes as $class) {
            if (is_string($class) && (class_exists($class) || interface_exists($class))) {
                $nodes[] = $class;
            }
        }
        $nodes = array_unique($nodes);

        // Build adjacency list (directed graph)
        $graph = [];
        foreach ($nodes as $node) {
            $graph[$node] = $this->getConstructorDependencies($node);
        }

        // Cycle detection using DFS coloring (0 = unvisited, 1 = visiting, 2 = visited)
        $visited = [];
        foreach ($nodes as $node) {
            $visited[$node] = 0;
        }

        foreach ($nodes as $node) {
            if ($visited[$node] === 0) {
                if ($this->hasCycleDfs($node, $graph, $visited)) {
                    $this->fail("DI Dependency Cycle detected! The DI graph is not a Directed Acyclic Graph (DAG).");
                }
            }
        }

        $this->assertTrue(true, "DI dependency graph verified as a Directed Acyclic Graph (DAG).");
    }

    private function getConstructorDependencies(string $className): array
    {
        if (interface_exists($className)) {
            return [];
        }

        $reflector = new ReflectionClass($className);
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return [];
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $type->getName();
            }
        }
        return $dependencies;
    }

    private function hasCycleDfs(string $node, array $graph, array &$visited): bool
    {
        $visited[$node] = 1; // Mark as visiting

        $neighbors = $graph[$node] ?? [];
        foreach ($neighbors as $neighbor) {
            if (isset($visited[$neighbor])) {
                if ($visited[$neighbor] === 1) {
                    return true;
                }
                if ($visited[$neighbor] === 0) {
                    if ($this->hasCycleDfs($neighbor, $graph, $visited)) {
                        return true;
                    }
                }
            }
        }

        $visited[$node] = 2; // Mark as fully visited
        return false;
    }
}

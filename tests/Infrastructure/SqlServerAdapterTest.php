<?php

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Infrastructure\Adapters\SqlServerAdapter;

class SqlServerAdapterTest extends TestCase
{
    public function test_get_limit_sql()
    {
        $adapter = new SqlServerAdapter(['dsn' => 'sqlite::memory:']);
        
        $this->assertEquals(" OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY", $adapter->getLimitSql(10, 0));
        $this->assertEquals(" OFFSET 20 ROWS FETCH NEXT 5 ROWS ONLY", $adapter->getLimitSql(5, 20));
    }

    public function test_query_intercepts_and_adds_order_by_when_missing()
    {
        // We use Reflection to inject a mock PDO object into the adapter
        $adapter = new SqlServerAdapter(['dsn' => 'dummy_dsn']);

        $mockPdo = $this->createMock(\PDO::class);
        $mockStmt = $this->createMock(\PDOStatement::class);

        $capturedSql = '';
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $mockStmt) {
                $capturedSql = $sql;
                return $mockStmt;
            });

        $mockStmt->expects($this->once())
            ->method('execute')
            ->with([]);

        // Inject the mock PDO using reflection
        $ref = new \ReflectionProperty(SqlServerAdapter::class, 'pdo');
        $ref->setAccessible(true);
        $ref->setValue($adapter, $mockPdo);

        // Run the query with OFFSET but without ORDER BY
        $adapter->query("SELECT * FROM users OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY");

        // The query should have "ORDER BY (SELECT NULL)" injected before "OFFSET"
        $this->assertEquals(
            "SELECT * FROM users ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY",
            $capturedSql
        );
    }

    public function test_query_does_not_add_order_by_when_already_present()
    {
        $adapter = new SqlServerAdapter(['dsn' => 'dummy_dsn']);

        $mockPdo = $this->createMock(\PDO::class);
        $mockStmt = $this->createMock(\PDOStatement::class);

        $capturedSql = '';
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $mockStmt) {
                $capturedSql = $sql;
                return $mockStmt;
            });

        // Inject mock PDO
        $ref = new \ReflectionProperty(SqlServerAdapter::class, 'pdo');
        $ref->setAccessible(true);
        $ref->setValue($adapter, $mockPdo);

        // Query that already has ORDER BY
        $adapter->query("SELECT * FROM users ORDER BY id OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY");

        // Should not be modified
        $this->assertEquals(
            "SELECT * FROM users ORDER BY id OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY",
            $capturedSql
        );
    }
}

<?php

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;
use Parina\Shared\Infrastructure\Adapters\MySqlAdapter;
use Parina\Shared\Infrastructure\Adapters\PostgreSqlAdapter;

class DbTest extends TestCase
{
    public function test_sqlite_adapter_lifecycle()
    {
        $config = [
            'dsn' => 'sqlite::memory:',
            'params' => []
        ];

        $adapter = new SqliteAdapter($config);

        // Crear una tabla temporal
        $adapter->exec("CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, val TEXT)");

        // Insertar dato
        $adapter->query("INSERT INTO test_table (val) VALUES (:val)", ['val' => 'hello']);

        // Consultar dato
        $stmt = $adapter->query("SELECT * FROM test_table WHERE val = :val", ['val' => 'hello']);
        $result = $stmt->fetch();

        $this->assertEquals('hello', $result['val']);
        $this->assertEquals(" LIMIT 10 OFFSET 20", $adapter->getLimitSql(10, 20));
    }

    public function test_mysql_adapter_lifecycle()
    {
        // Instanciar usando sqlite::memory: para evitar lanzar error de conexión de MySQL
        $config = [
            'dsn' => 'sqlite::memory:',
            'user' => null,
            'pass' => null
        ];

        $adapter = new MySqlAdapter($config);

        // Crear una tabla temporal
        $adapter->exec("CREATE TABLE IF NOT EXISTS test_table_mysql (id INTEGER PRIMARY KEY, val TEXT)");

        // Insertar dato
        $adapter->query("INSERT INTO test_table_mysql (val) VALUES (:val)", ['val' => 'hello_mysql']);

        // Consultar dato
        $stmt = $adapter->query("SELECT * FROM test_table_mysql WHERE val = :val", ['val' => 'hello_mysql']);
        $result = $stmt->fetch();

        $this->assertEquals('hello_mysql', $result['val']);
        $this->assertEquals(" LIMIT 30, 10", $adapter->getLimitSql(10, 30));
    }

    public function test_postgresql_adapter_lifecycle()
    {
        // Instanciar usando sqlite::memory: para evitar lanzar error de conexión de PostgreSQL
        $config = [
            'dsn' => 'sqlite::memory:',
            'user' => null,
            'pass' => null
        ];

        $adapter = new PostgreSqlAdapter($config);

        // Crear una tabla temporal
        $adapter->exec("CREATE TABLE IF NOT EXISTS test_table_pg (id INTEGER PRIMARY KEY, val TEXT)");

        // Insertar dato
        $adapter->query("INSERT INTO test_table_pg (val) VALUES (:val)", ['val' => 'hello_pg']);

        // Consultar dato
        $stmt = $adapter->query("SELECT * FROM test_table_pg WHERE val = :val", ['val' => 'hello_pg']);
        $result = $stmt->fetch();

        $this->assertEquals('hello_pg', $result['val']);
        $this->assertEquals(" LIMIT 10 OFFSET 20", $adapter->getLimitSql(10, 20));
    }
}

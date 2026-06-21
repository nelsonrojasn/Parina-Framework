<?php

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Infrastructure\Db;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;
use Parina\Shared\Infrastructure\Adapters\MySqlAdapter;

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

    public function test_mysql_adapter_limit_sql()
    {
        // Instanciar usando sqlite::memory: para evitar lanzar error de conexión de MySQL
        $config = [
            'dsn' => 'sqlite::memory:',
            'user' => null,
            'pass' => null
        ];

        $adapter = new MySqlAdapter($config);
        $this->assertEquals(" LIMIT 30, 10", $adapter->getLimitSql(10, 30));
    }

    public function test_db_facade_delegation()
    {
        $config = [
            'dsn' => 'sqlite::memory:',
            'params' => []
        ];
        $adapter = new SqliteAdapter($config);
        
        Db::init($adapter);

        Db::exec("CREATE TABLE IF NOT EXISTS facade_table (id INTEGER PRIMARY KEY, name TEXT)");
        Db::query("INSERT INTO facade_table (name) VALUES (?)", ['Nelson']);

        $stmt = Db::query("SELECT * FROM facade_table");
        $rows = $stmt->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('Nelson', $rows[0]['name']);
        $this->assertEquals(" LIMIT 5 OFFSET 0", Db::limit(5, 0));
    }
}

<?php
declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\SchemaGenerator;
use RuntimeException;

class SchemaGeneratorTest extends TestCase
{
    private SchemaGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new SchemaGenerator();
    }

    public function test_basic_schema_generation(): void
    {
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "producto,id,INTEGER,Y,N,N,,\n" .
               "producto,nombre,VARCHAR(100),N,N,Y,,\n" .
               "producto,precio,\"DECIMAL(10,2)\",N,N,N,0.00,";

        $schemas = $this->generator->generateSchemas($csv);

        $this->assertArrayHasKey('sqlite', $schemas);
        $this->assertArrayHasKey('mysql', $schemas);
        $this->assertArrayHasKey('pgsql', $schemas);

        // SQLite validation
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS producto', $schemas['sqlite']);
        $this->assertStringContainsString('id INTEGER PRIMARY KEY AUTOINCREMENT', $schemas['sqlite']);
        $this->assertStringContainsString('nombre VARCHAR(100) NOT NULL UNIQUE', $schemas['sqlite']);
        $this->assertStringContainsString('precio DECIMAL(10,2) NOT NULL DEFAULT 0.00', $schemas['sqlite']);

        // MySQL validation
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS producto', $schemas['mysql']);
        $this->assertStringContainsString('id INT AUTO_INCREMENT PRIMARY KEY', $schemas['mysql']);
        $this->assertStringContainsString('nombre VARCHAR(100) NOT NULL UNIQUE', $schemas['mysql']);
        $this->assertStringContainsString('precio DECIMAL(10,2) NOT NULL DEFAULT 0.00', $schemas['mysql']);

        // PostgreSQL validation
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS producto', $schemas['pgsql']);
        $this->assertStringContainsString('id SERIAL PRIMARY KEY', $schemas['pgsql']);
        $this->assertStringContainsString('nombre VARCHAR(100) NOT NULL UNIQUE', $schemas['pgsql']);
        $this->assertStringContainsString('precio DECIMAL(10,2) NOT NULL DEFAULT 0.00', $schemas['pgsql']);
    }

    public function test_topological_sorting_and_drops(): void
    {
        // pedido_item references pedido & producto, and pedido references cliente
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "pedido_item,id,INTEGER,Y,N,N,,\n" .
               "pedido_item,pedido_id,INTEGER,N,N,N,,pedido\n" .
               "pedido_item,producto_id,INTEGER,N,N,N,,producto(id)\n" .
               "pedido,id,INTEGER,Y,N,N,,\n" .
               "pedido,cliente_id,INTEGER,N,N,N,,cliente\n" .
               "cliente,id,INTEGER,Y,N,N,,\n" .
               "producto,id,INTEGER,Y,N,N,,";

        $schemas = $this->generator->generateSchemas($csv);

        // Validation for SQLite creation order (cliente and producto must be before pedido, and pedido before pedido_item)
        $sqlite = $schemas['sqlite'];
        $posCliente = strpos($sqlite, 'CREATE TABLE IF NOT EXISTS cliente');
        $posProducto = strpos($sqlite, 'CREATE TABLE IF NOT EXISTS producto');
        $posPedido = strpos($sqlite, 'CREATE TABLE IF NOT EXISTS pedido (');
        $posPedidoItem = strpos($sqlite, 'CREATE TABLE IF NOT EXISTS pedido_item');

        $this->assertNotFalse($posCliente);
        $this->assertNotFalse($posProducto);
        $this->assertNotFalse($posPedido);
        $this->assertNotFalse($posPedidoItem);

        $this->assertLessThan($posPedido, $posCliente, "cliente should be created before pedido");
        $this->assertLessThan($posPedidoItem, $posProducto, "producto should be created before pedido_item");
        $this->assertLessThan($posPedidoItem, $posPedido, "pedido should be created before pedido_item");

        // DROP order should be exactly reverse
        $dropCliente = strpos($sqlite, 'DROP TABLE IF EXISTS cliente;');
        $dropPedido = strpos($sqlite, 'DROP TABLE IF EXISTS pedido;');
        $dropPedidoItem = strpos($sqlite, 'DROP TABLE IF EXISTS pedido_item;');

        $this->assertNotFalse($dropCliente);
        $this->assertNotFalse($dropPedido);
        $this->assertNotFalse($dropPedidoItem);

        $this->assertLessThan($dropPedido, $dropPedidoItem, "pedido_item should be dropped before pedido");
        $this->assertLessThan($dropCliente, $dropPedido, "pedido should be dropped before cliente");
    }

    public function test_automatic_usuario_injection(): void
    {
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "producto,id,INTEGER,Y,N,N,,";

        $schemas = $this->generator->generateSchemas($csv);

        // The schemas should automatically contain usuario table creation and seeding
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS usuario', $schemas['sqlite']);
        $this->assertStringContainsString('INSERT OR IGNORE INTO usuario', $schemas['sqlite']);

        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS usuario', $schemas['mysql']);
        $this->assertStringContainsString('INSERT IGNORE INTO usuario', $schemas['mysql']);

        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS usuario', $schemas['pgsql']);
        $this->assertStringContainsString('INSERT INTO usuario', $schemas['pgsql']);
    }

    public function test_validation_reserved_keywords(): void
    {
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "select,id,INTEGER,Y,N,N,,";

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Table name 'select' is a reserved SQL keyword");

        $this->generator->generateSchemas($csv);
    }

    public function test_validation_nullable_primary_key(): void
    {
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "producto,id,INTEGER,Y,Y,N,,";

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Primary key column 'id' in table 'producto' cannot be nullable");

        $this->generator->generateSchemas($csv);
    }

    public function test_default_value_formatting(): void
    {
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "producto,id,INTEGER,Y,N,N,,\n" .
               "producto,status,VARCHAR(20),N,N,N,activo,\n" .
               "producto,is_active,INTEGER,N,N,N,1,";

        $schemas = $this->generator->generateSchemas($csv);

        // String default 'activo' should be quoted as 'activo'
        $this->assertStringContainsString("status VARCHAR(20) NOT NULL DEFAULT 'activo'", $schemas['mysql']);
        // Numeric default 1 should remain unquoted
        $this->assertStringContainsString("is_active INT NOT NULL DEFAULT 1", $schemas['mysql']);
    }

    public function test_flexible_boolean_parsing(): void
    {
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "producto,id,INTEGER,yes,no,N,,\n" .
               "producto,nombre,VARCHAR(100),N,0,true,,";

        $schemas = $this->generator->generateSchemas($csv);

        // id has pk=yes, null=no, unique=N
        $this->assertStringContainsString("id INTEGER PRIMARY KEY AUTOINCREMENT", $schemas['sqlite']);
        // nombre has pk=N, null=0 (NOT NULL), unique=true (UNIQUE)
        $this->assertStringContainsString("nombre VARCHAR(100) NOT NULL UNIQUE", $schemas['sqlite']);
    }

    public function test_circular_dependency(): void
    {
        // A references B, and B references A
        $csv = "table,attribute,type,pk,null,unique,default,references\n" .
               "tabla_a,id,INTEGER,Y,N,N,,\n" .
               "tabla_a,b_id,INTEGER,N,N,N,,tabla_b\n" .
               "tabla_b,id,INTEGER,Y,N,N,,\n" .
               "tabla_b,a_id,INTEGER,N,N,N,,tabla_a";

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Circular dependency detected involving table");

        $this->generator->generateSchemas($csv);
    }
}

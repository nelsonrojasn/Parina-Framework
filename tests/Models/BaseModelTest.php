<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Models\BaseModel;
use Parina\Shared\Infrastructure\Db;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;

class DummyModel extends BaseModel
{
    protected static string $table = 'dummies';
    
    public static function testPaginate(int $limit, int $offset = 0): string
    {
        return self::paginate($limit, $offset);
    }
}

class BaseModelTest extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'dsn' => 'sqlite::memory:',
            'params' => []
        ];
        $adapter = new SqliteAdapter($config);
        Db::init($adapter);

        Db::exec("CREATE TABLE IF NOT EXISTS dummies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT
        )");
    }

    public function test_crud_lifecycle()
    {
        // 1. Create
        $created = DummyModel::create(['name' => 'Item 1', 'description' => 'First item']);
        $this->assertTrue($created);

        // 2. Read All
        $all = DummyModel::all();
        $this->assertCount(1, $all);
        $this->assertEquals('Item 1', $all[0]['name']);

        // 3. Read single
        $item = DummyModel::find(1);
        $this->assertNotNull($item);
        $this->assertEquals('Item 1', $item['name']);

        // Read non-existent
        $this->assertNull(DummyModel::find(99));

        // 4. Update
        $updated = DummyModel::update(1, ['name' => 'Updated Item', 'description' => 'New description']);
        $this->assertTrue($updated);

        $itemUpdated = DummyModel::find(1);
        $this->assertEquals('Updated Item', $itemUpdated['name']);
        
        // Update empty data
        $this->assertFalse(DummyModel::update(1, []));

        // 5. Paginate helper
        $this->assertEquals(" LIMIT 10 OFFSET 5", DummyModel::testPaginate(10, 5));

        // 6. Delete
        $deleted = DummyModel::delete(1);
        $this->assertTrue($deleted);

        $this->assertNull(DummyModel::find(1));
    }

    public function test_custom_sql_generator()
    {
        $customGenerator = new class implements \Parina\Shared\Infrastructure\SqlGeneratorInterface {
            public function selectAll(string $table, array|string $columns): string {
                return "SELECT * FROM custom_table";
            }
            public function selectById(string $table, array|string $columns, string $primaryKey = 'id'): string { return ''; }
            public function selectFirst(string $table, string $condition, array|string $columns): string { return ''; }
            public function insert(string $table, array $data): string { return ''; }
            public function update(string $table, array $data, string $primaryKey = 'id'): string { return ''; }
            public function delete(string $table, string $primaryKey = 'id'): string { return ''; }
        };

        try {
            DummyModel::setSqlGenerator($customGenerator);
            
            // This should fail to run the query because custom_table doesn't exist, but it proves it uses the custom SQL!
            $this->expectException(\PDOException::class);
            DummyModel::all();
        } finally {
            DummyModel::setSqlGenerator(new \Parina\Shared\Infrastructure\SqlGenerator());
        }
    }
}

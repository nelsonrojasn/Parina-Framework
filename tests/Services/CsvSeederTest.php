<?php

namespace Tests\Services;

use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;
use Parina\Shared\Infrastructure\Db;
use Parina\Shared\Services\CsvSeeder;
use PHPUnit\Framework\TestCase;

class CsvSeederTest extends TestCase
{
    private string $dbPath;

    protected function setUp(): void
    {
        $this->dbPath = sys_get_temp_dir() . '/parina-csv-seeder-' . uniqid('', true) . '.sqlite';
        Db::init(new SqliteAdapter(['dsn' => 'sqlite:' . $this->dbPath, 'params' => []]));
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }

    public function test_it_inserts_rows_from_csv_into_the_target_table(): void
    {
        Db::exec('CREATE TABLE people (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT)');

        $csvPath = sys_get_temp_dir() . '/parina-csv-seeder-' . uniqid('', true) . '.csv';
        file_put_contents($csvPath, "name,email\nAna,ana@example.com\nBob,bob@example.com\n");

        $seeder = new CsvSeeder();
        $inserted = $seeder->seedFromCsv('people', $csvPath);

        $this->assertSame(2, $inserted);

        $stmt = Db::query('SELECT COUNT(*) as count FROM people');
        $this->assertSame(2, (int) $stmt->fetch()['count']);

        unlink($csvPath);
    }
}

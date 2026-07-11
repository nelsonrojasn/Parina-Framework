<?php

namespace Tests\Services;

use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;
use Parina\Shared\Infrastructure\SqlGenerator;
use Parina\Shared\Services\CsvSeeder;
use PHPUnit\Framework\TestCase;

class CsvSeederTest extends TestCase
{
    private string $dbPath;
    private SqliteAdapter $adapter;

    protected function setUp(): void
    {
        $this->dbPath = sys_get_temp_dir() . '/parina-csv-seeder-' . uniqid('', true) . '.sqlite';
        $this->adapter = new SqliteAdapter(['dsn' => 'sqlite:' . $this->dbPath, 'params' => []]);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }

    public function test_it_inserts_rows_from_csv_into_the_target_table(): void
    {
        $this->adapter->exec('CREATE TABLE people (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT)');

        $csvPath = sys_get_temp_dir() . '/parina-csv-seeder-' . uniqid('', true) . '.csv';
        file_put_contents($csvPath, "name,email\nAna,ana@example.com\nBob,bob@example.com\n");

        $seeder = new CsvSeeder($this->adapter, new SqlGenerator());
        $inserted = $seeder->seedFromCsv('people', $csvPath);

        $this->assertSame(2, $inserted);

        $stmt = $this->adapter->query('SELECT COUNT(*) as count FROM people');
        $this->assertSame(2, (int) $stmt->fetch()['count']);

        unlink($csvPath);
    }

    public function test_throws_exception_when_file_not_found(): void
    {
        $seeder = new CsvSeeder($this->adapter, new SqlGenerator());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("CSV file not found");
        $seeder->seedFromCsv('people', '/non-existent-file.csv');
    }

    public function test_throws_exception_when_empty_csv(): void
    {
        $csvPath = sys_get_temp_dir() . '/parina-csv-seeder-empty-' . uniqid('', true) . '.csv';
        file_put_contents($csvPath, "");

        $seeder = new CsvSeeder($this->adapter, new SqlGenerator());

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage("CSV file is empty");
            $seeder->seedFromCsv('people', $csvPath);
        } finally {
            if (file_exists($csvPath)) {
                unlink($csvPath);
            }
        }
    }
}

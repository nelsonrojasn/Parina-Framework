<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\DbUserQueryRepository;
use Parina\Shared\Services\DbUserCommandRepository;
use Parina\Shared\Infrastructure\Db;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;

class DbUserRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'dsn' => 'sqlite::memory:',
            'params' => []
        ];
        $adapter = new SqliteAdapter($config);
        Db::init($adapter);

        Db::exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            email TEXT,
            password TEXT
        )");
    }

    public function test_repository_lifecycle()
    {
        $queryRepo = new DbUserQueryRepository();
        $commandRepo = new DbUserCommandRepository();

        // 1. Create a user via Command repository
        $user = [
            'username' => 'nelson',
            'email' => 'nelson@example.com',
            'password' => password_hash('nelson123', PASSWORD_DEFAULT)
        ];

        $saved = $commandRepo->save($user);
        $this->assertTrue($saved);

        // 2. Fetch the user via Query repository by username
        $fetched = $queryRepo->findByUsername('nelson');
        $this->assertNotNull($fetched);
        $this->assertEquals('nelson', $fetched['username']);
        $this->assertEquals('nelson@example.com', $fetched['email']);
        $this->assertTrue(password_verify('nelson123', $fetched['password']));

        // 3. Fetch the user by id
        $id = (int)$fetched['id'];
        $fetchedById = $queryRepo->findById($id);
        $this->assertNotNull($fetchedById);
        $this->assertEquals('nelson', $fetchedById['username']);

        // 4. Update the user via Command repository
        $fetchedById['email'] = 'nelson_updated@example.com';
        $updated = $commandRepo->save($fetchedById);
        $this->assertTrue($updated);

        $fetchedUpdated = $queryRepo->findById($id);
        $this->assertEquals('nelson_updated@example.com', $fetchedUpdated['email']);

        // 5. Delete the user via Command repository
        $deleted = $commandRepo->delete($id);
        $this->assertTrue($deleted);

        // Verify it was deleted
        $this->assertNull($queryRepo->findById($id));
        $this->assertNull($queryRepo->findByUsername('nelson'));
    }
}

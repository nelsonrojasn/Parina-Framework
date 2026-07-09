<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Models\User;
use Parina\Core\Request;
use Parina\Shared\Infrastructure\Db;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        
        // Setup SQLite database directly
        $dbFile = \Parina\Core\Config::getDbPath();
        $dbDir = dirname($dbFile);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $projectRoot = dirname(dirname(__DIR__));
        $schemaFile = $projectRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema.sqlite.sql';
        $sqlTables = file_get_contents($schemaFile);
        Db::exec($sqlTables);
        
        // Clear previous records if any, then insert seed records
        Db::exec("DELETE FROM company");
        Db::exec("DELETE FROM profiles");
        Db::exec("DELETE FROM users");
        Db::exec("DELETE FROM profile_user");
        Db::exec("DELETE FROM resources");

        // 1. Company
        Db::query("INSERT INTO company (id, dni, name) VALUES (1, '766543211', 'Demo Company')");
        // 2. Profile
        Db::query("INSERT INTO profiles (id, name) VALUES (1, 'admin')");
        // 3. User
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
        Db::query(
            "INSERT INTO users (id, company_id, username, password, email) VALUES (1, 1, 'admin', :password, 'admin@democompany.org')",
            ['password' => $hashedPassword]
        );
        // 4. profile_user
        Db::query("INSERT INTO profile_user (profile_id, user_id) VALUES (1, 1)");
    }

    public function test_find_by_login_name()
    {
        $userModel = new User();
        
        $admin = $userModel->findByLoginName('admin');
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin['username']);
        $this->assertEquals('admin@democompany.org', $admin['email']);

        $nonExistent = $userModel->findByLoginName('non_existent');
        $this->assertNull($nonExistent);
    }

    public function test_check_auth()
    {
        $userModel = new User();

        // Credenciales correctas
        $this->assertTrue($userModel->checkAuth('admin', 'admin123'));
        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertTrue($_SESSION['is_logged_in']);
        $this->assertTrue($_SESSION['active']);

        // Credenciales incorrectas
        $_SESSION = [];
        $this->assertFalse($userModel->checkAuth('admin', 'wrong_pass'));
        $this->assertArrayNotHasKey('user_id', $_SESSION);

        // Usuario no existente
        $this->assertFalse($userModel->checkAuth('non_existent', 'admin123'));
    }
}

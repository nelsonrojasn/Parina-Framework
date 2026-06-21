<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Models\User;
use Parina\Core\Request;
use Parina\Modules\Public\SetupHandler;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        
        // Inicializar base de datos física para que exista el usuario admin
        $setup = new SetupHandler();
        $setup->handle(new Request([], [], [], [], []));
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

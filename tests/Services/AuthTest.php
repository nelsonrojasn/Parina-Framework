<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\SessionAuth;

class AuthTest extends TestCase
{
    private SessionAuth $auth;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->auth = new SessionAuth();
    }

    public function test_auth_lifecycle()
    {
        $this->auth->init();
        
        $this->assertFalse($this->auth->isLoggedIn());

        $user = ['id' => 123, 'username' => 'nelson', 'company_id' => 1];
        $this->auth->login($user);

        $this->assertTrue($this->auth->isLoggedIn());
        $this->assertEquals(123, $_SESSION['user_id']);

        $this->auth->logout();
        $this->assertFalse($this->auth->isLoggedIn());
    }
}

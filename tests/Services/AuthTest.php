<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\Auth;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_auth_lifecycle()
    {
        Auth::init();
        
        $this->assertFalse(Auth::isLoggedIn());

        $user = ['id' => 123, 'username' => 'nelson', 'company_id' => 1];
        Auth::login($user);

        $this->assertTrue(Auth::isLoggedIn());
        $this->assertEquals(123, $_SESSION['user_id']);

        Auth::logout();
        $this->assertFalse(Auth::isLoggedIn());
    }
}

<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\Auth;

class AuthServiceTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function test_auth_service_lifecycle()
    {
        $_SESSION = [];

        $this->assertFalse(Auth::isLoggedIn());

        $user = [
            'id' => 99,
            'username' => 'nelson',
            'company_id' => 5
        ];

        Auth::login($user);

        $this->assertTrue(Auth::isLoggedIn());
        $this->assertEquals(99, $_SESSION['user_id']);
        $this->assertEquals('nelson', $_SESSION['username']);
        $this->assertEquals(5, $_SESSION['company_id']);
        $this->assertTrue($_SESSION['active']);

        Auth::logout();

        $this->assertFalse(Auth::isLoggedIn());
        $this->assertEmpty($_SESSION);
    }
}

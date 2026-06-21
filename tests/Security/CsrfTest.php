<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Security\Csrf;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_token_generation()
    {
        $token1 = Csrf::token();
        $token2 = Csrf::token();

        $this->assertNotEmpty($token1);
        $this->assertEquals(64, strlen($token1)); // Hexadecimal de 32 bytes es 64 caracteres
        $this->assertEquals($token1, $token2); // Debe reusar el mismo token de sesión
        $this->assertEquals($token1, $_SESSION['csrf_token']);
    }

    public function test_validation_with_explicit_token()
    {
        $token = Csrf::token();

        $this->assertTrue(Csrf::validate($token));
        $this->assertFalse(Csrf::validate('invalid_token'));
    }

    public function test_validation_with_post_token()
    {
        $token = Csrf::token();

        $_POST['_csrf'] = $token;
        $this->assertTrue(Csrf::validate());

        $_POST['_csrf'] = 'invalid_post_token';
        $this->assertFalse(Csrf::validate());

        unset($_POST['_csrf']);
    }
}

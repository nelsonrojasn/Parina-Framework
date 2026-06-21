<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\Session;

class SessionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function test_session_lifecycle()
    {
        $_SESSION = [];

        Session::set('user', 'john');
        $this->assertEquals('john', Session::get('user'));
        $this->assertNull(Session::get('non_existent'));

        // Iniciar sesión (requiere ejecutarse en proceso separado)
        Session::start();
        Session::set('role', 'admin');
        $this->assertEquals('admin', Session::get('role'));
        
        // Destruir sesión
        Session::clear();
        $this->assertTrue(true); // Validar que se completó sin errores
    }
}

<?php

namespace Tests\Handlers;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Modules\Public\LoginCheckHandler;

class LoginCheckHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        
        // Inicializar base de datos
        $setup = new \Parina\Modules\Public\SetupHandler();
        $setup->handle(new Request([], [], [], [], []));
    }

    public function test_handler_returns_valid_response()
    {
        $handler = new LoginCheckHandler();
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatus());
    }
}
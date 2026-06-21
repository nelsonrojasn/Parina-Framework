<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\BasicAuth;
use Parina\Core\Responses\BasicRealmResponse;
use Parina\Modules\Public\SetupHandler;

class BasicAuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        
        // Inicializar base de datos y usuario admin de prueba
        $setup = new SetupHandler();
        $setup->handle(new Request([], [], [], [], []));
    }

    public function test_basic_auth_blocks_when_credentials_missing()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [], // Sin PHP_AUTH_USER ni PHP_AUTH_PW
            files: [],
            cookies: []
        );

        $middleware = new BasicAuth();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(BasicRealmResponse::class, $response);
        $this->assertEquals(401, $response->getStatus());
    }

    public function test_basic_auth_blocks_when_credentials_invalid()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [
                'PHP_AUTH_USER' => 'wrong_user',
                'PHP_AUTH_PW' => 'wrong_password'
            ],
            files: [],
            cookies: []
        );

        $middleware = new BasicAuth();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(BasicRealmResponse::class, $response);
        $this->assertEquals(401, $response->getStatus());
    }

    public function test_basic_auth_passes_when_credentials_valid()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin123'
            ],
            files: [],
            cookies: []
        );

        $middleware = new BasicAuth();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }
}

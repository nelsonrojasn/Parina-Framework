<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\JwtAuth;
use Parina\Shared\Services\JwtAuth as JwtService;
use Parina\Core\Responses\UnauthorizedResponse;

class JwtAuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_jwt_auth_blocks_when_token_missing()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [], // Sin cabecera de autorización
            files: [],
            cookies: []
        );

        $middleware = new JwtAuth();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(UnauthorizedResponse::class, $response);
        $this->assertEquals(401, $response->getStatus());
        $this->assertEquals("Token missing or malformed.", $response->getContent());
    }

    public function test_jwt_auth_blocks_when_token_invalid()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer invalid.token.here'
            ],
            files: [],
            cookies: []
        );

        $middleware = new JwtAuth();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(UnauthorizedResponse::class, $response);
        $this->assertEquals(401, $response->getStatus());
        $this->assertEquals("Invalid or expired token.", $response->getContent());
    }

    public function test_jwt_auth_passes_when_token_valid()
    {
        // Generar un token válido usando el servicio
        $payload = ['sub' => 123, 'username' => 'testuser'];
        $token = JwtService::createToken($payload);

        $request = new Request(
            query: [],
            post: [],
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            files: [],
            cookies: []
        );

        $middleware = new JwtAuth();
        $response = $middleware->handle($request);

        $this->assertNull($response);
        
        // Verificar que los datos del usuario se inyectaron en la sesión
        $this->assertEquals(123, $_SESSION['user_id']);
        $this->assertEquals('testuser', $_SESSION['user_data']['username']);
    }
}

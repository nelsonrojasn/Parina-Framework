<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\Csrf;
use Parina\Shared\Security\Csrf as CsrfValidator;
use Parina\Core\Responses\ErrorResponse;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        // Reiniciar la sesión antes de cada prueba
        $_SESSION = [];
    }

    public function test_get_request_bypasses_csrf()
    {
        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_METHOD' => 'GET'],
            files: [],
            cookies: []
        );

        $middleware = new Csrf();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_post_request_without_token_fails()
    {
        // Inicializar el token en la sesión para que tenga un valor con el que comparar
        $_SESSION['csrf_token'] = 'valid_token_123';

        $request = new Request(
            query: [],
            post: [], // No enviamos token en POST
            server: ['REQUEST_METHOD' => 'POST'],
            files: [],
            cookies: []
        );

        $middleware = new Csrf();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals('Invalid CSRF token.', $response->getContent());
    }

    public function test_post_request_with_correct_token_passes()
    {
        $_SESSION['csrf_token'] = 'valid_token_123';

        $request = new Request(
            query: [],
            post: ['_csrf' => 'valid_token_123'],
            server: ['REQUEST_METHOD' => 'POST'],
            files: [],
            cookies: []
        );

        $middleware = new Csrf();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_put_request_without_token_fails()
    {
        $_SESSION['csrf_token'] = 'valid_token_123';

        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_METHOD' => 'PUT'],
            files: [],
            cookies: []
        );

        $middleware = new Csrf();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
    }

    public function test_put_request_with_correct_token_passes()
    {
        $_SESSION['csrf_token'] = 'valid_token_123';

        $request = new Request(
            query: [],
            post: ['_csrf' => 'valid_token_123'],
            server: ['REQUEST_METHOD' => 'PUT'],
            files: [],
            cookies: []
        );

        $middleware = new Csrf();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_delete_request_without_token_fails()
    {
        $_SESSION['csrf_token'] = 'valid_token_123';

        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_METHOD' => 'DELETE'],
            files: [],
            cookies: []
        );

        $middleware = new Csrf();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
    }

    public function test_delete_request_with_correct_token_passes()
    {
        $_SESSION['csrf_token'] = 'valid_token_123';

        $request = new Request(
            query: [],
            post: ['_csrf' => 'valid_token_123'],
            server: ['REQUEST_METHOD' => 'DELETE'],
            files: [],
            cookies: []
        );

        $middleware = new Csrf();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }
}

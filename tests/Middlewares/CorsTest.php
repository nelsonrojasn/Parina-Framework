<?php

namespace Parina\Shared\Middlewares {
    function header(string $string, bool $replace = true, ?int $http_response_code = null): void {
        $GLOBALS['sent_headers'][] = $string;
    }
}

namespace Tests\Middlewares {

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\Cors;
use Parina\Core\Responses\PlainTextResponse;

class CorsTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['sent_headers'] = [];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['sent_headers']);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_options_request_returns_204_plain_text_response()
    {
        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_METHOD' => 'OPTIONS'],
            files: [],
            cookies: []
        );

        $middleware = new Cors();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(PlainTextResponse::class, $response);
        $this->assertEquals(204, $response->getStatus());
        $this->assertEquals('', $response->getContent());

        // Verificar cabeceras CORS interceptadas
        $headers = $GLOBALS['sent_headers'] ?? [];
        $this->assertContains('Access-Control-Allow-Origin: *', $headers);
        $this->assertContains('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS', $headers);
        $this->assertContains('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With', $headers);
        $this->assertContains('Access-Control-Allow-Credentials: true', $headers);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_get_request_returns_null_to_continue_execution()
    {
        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_METHOD' => 'GET'],
            files: [],
            cookies: []
        );

        $middleware = new Cors();
        $response = $middleware->handle($request);

        $this->assertNull($response);

        // Verificar cabeceras CORS interceptadas
        $headers = $GLOBALS['sent_headers'] ?? [];
        $this->assertContains('Access-Control-Allow-Origin: *', $headers);
        $this->assertContains('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS', $headers);
        $this->assertContains('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With', $headers);
        $this->assertContains('Access-Control-Allow-Credentials: true', $headers);
    }
}
}

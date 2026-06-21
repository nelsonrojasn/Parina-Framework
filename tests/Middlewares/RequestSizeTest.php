<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\RequestSize;
use Parina\Core\Responses\ErrorResponse;

class RequestSizeTest extends TestCase
{
    protected function setUp(): void
    {
        // Limpiar variables de servidor global
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            unset($_SERVER['CONTENT_LENGTH']);
        }
    }

    protected function tearDown(): void
    {
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            unset($_SERVER['CONTENT_LENGTH']);
        }
    }

    public function test_request_size_allows_request_when_under_limit()
    {
        $_SERVER['CONTENT_LENGTH'] = 1000; // 1 KB

        $request = new Request([], [], [], [], []);
        $middleware = new RequestSize();

        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_request_size_blocks_request_when_exceeding_limit()
    {
        $_SERVER['CONTENT_LENGTH'] = 1024 * 1024 * 6; // 6 MB (Límite es 5 MB)

        $request = new Request([], [], [], [], []);
        $middleware = new RequestSize();

        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(413, $response->getStatus());
        $this->assertEquals("Request length exceeded.", $response->getContent());
    }
}

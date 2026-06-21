<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\RateLimit;
use Parina\Core\Responses\ErrorResponse;

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_allows_request_when_under_limit()
    {
        $request = new Request([], [], [], [], []);
        $middleware = new RateLimit();

        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_blocks_request_when_exceeding_rate_limit()
    {
        if (!defined('RATE_LIMIT_MS')) {
            define('RATE_LIMIT_MS', 500);
        }
        $_SESSION['_pin_last_req'] = microtime(true); // Hace 0 milisegundos

        $request = new Request([], [], [], [], []);
        $middleware = new RateLimit();

        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(429, $response->getStatus());
    }

    public function test_bypasses_limit_when_bypass_session_active()
    {
        $_SESSION['_pin_bypass_limit'] = true;
        $_SESSION['_pin_last_req'] = microtime(true);

        $request = new Request([], [], [], [], []);
        $middleware = new RateLimit();

        $response = $middleware->handle($request);

        $this->assertNull($response);
    }
}

<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\Auth;
use Parina\Core\Responses\ErrorResponse;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_auth_middleware_blocks_when_not_logged_in()
    {
        $request = new Request([], [], [], [], []);
        $middleware = new Auth();
        
        $response = $middleware->handle($request);
        
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals("Not logged in.", $response->getContent());
    }

    public function test_auth_middleware_allows_when_logged_in()
    {
        $_SESSION['user_id'] = 42;
        $_SESSION['active'] = true;

        $request = new Request([], [], [], [], []);
        $middleware = new Auth();
        
        $response = $middleware->handle($request);
        
        $this->assertNull($response);
    }
}

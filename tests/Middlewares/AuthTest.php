<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\Auth;
use Parina\Shared\Services\AuthInterface;
use Parina\Core\Responses\ErrorResponse;

class AuthTest extends TestCase
{
    public function test_auth_middleware_blocks_when_not_logged_in()
    {
        $request = new Request([], [], [], [], []);
        
        $authMock = $this->createMock(AuthInterface::class);
        $authMock->method('isLoggedIn')->willReturn(false);

        $middleware = new Auth($authMock);
        $response = $middleware->handle($request);
        
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals("Not logged in.", $response->getContent());
    }

    public function test_auth_middleware_allows_when_logged_in()
    {
        $request = new Request([], [], [], [], []);
        
        $authMock = $this->createMock(AuthInterface::class);
        $authMock->method('isLoggedIn')->willReturn(true);

        $middleware = new Auth($authMock);
        $response = $middleware->handle($request);
        
        $this->assertNull($response);
    }
}

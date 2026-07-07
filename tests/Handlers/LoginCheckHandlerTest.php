<?php

namespace Tests\Handlers;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Modules\Public\LoginCheckHandler;
use Parina\Shared\Services\UserQueryRepositoryInterface;
use Parina\Shared\Services\AuthInterface;
use Parina\Core\Session;

class LoginCheckHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_handler_returns_valid_response_on_failed_login()
    {
        $repoMock = $this->createMock(UserQueryRepositoryInterface::class);
        $repoMock->method('checkCredentials')->willReturn(null);

        $authMock = $this->createMock(AuthInterface::class);
        $authMock->expects($this->never())->method('login');

        $handler = new LoginCheckHandler($repoMock, $authMock);
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('Credentials are not valid. Please check and try again!', Session::get('flash'));
    }

    public function test_handler_redirects_on_successful_login()
    {
        $user = ['id' => 1, 'username' => 'admin', 'company_id' => 1];

        $repoMock = $this->createMock(UserQueryRepositoryInterface::class);
        $repoMock->method('checkCredentials')->willReturn($user);

        $authMock = $this->createMock(AuthInterface::class);
        $authMock->expects($this->once())->method('login')->with($user);

        $handler = new LoginCheckHandler($repoMock, $authMock);
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertEquals(302, $response->getStatus());
        $this->assertInstanceOf(\Parina\Core\Responses\RedirectResponse::class, $response);
    }
}
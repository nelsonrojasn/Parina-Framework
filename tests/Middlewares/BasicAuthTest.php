<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\BasicAuth;
use Parina\Core\Responses\BasicRealmResponse;
use Parina\Shared\Services\UserQueryRepositoryInterface;

class BasicAuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_basic_auth_blocks_when_credentials_missing()
    {
        $repoMock = $this->createMock(UserQueryRepositoryInterface::class);
        
        $request = new Request(
            query: [],
            post: [],
            server: [], // No PHP_AUTH_USER nor PHP_AUTH_PW
            files: [],
            cookies: []
        );

        $middleware = new BasicAuth($repoMock);
        $response = $middleware->handle($request);

        $this->assertInstanceOf(BasicRealmResponse::class, $response);
        $this->assertEquals(401, $response->getStatus());
    }

    public function test_basic_auth_blocks_when_credentials_invalid()
    {
        $user = [
            'id' => 2,
            'username' => 'wrong_user',
            'password' => password_hash('correct_password', PASSWORD_DEFAULT)
        ];
        $repoMock = $this->createMock(UserQueryRepositoryInterface::class);
        $repoMock->method('findByUsername')->with('wrong_user')->willReturn($user);

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

        $middleware = new BasicAuth($repoMock);
        $response = $middleware->handle($request);

        $this->assertInstanceOf(BasicRealmResponse::class, $response);
        $this->assertEquals(401, $response->getStatus());
    }

    public function test_basic_auth_passes_when_credentials_valid()
    {
        $user = [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT)
        ];
        $repoMock = $this->createMock(UserQueryRepositoryInterface::class);
        $repoMock->method('findByUsername')->with('admin')->willReturn($user);

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

        $middleware = new BasicAuth($repoMock);
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }
}

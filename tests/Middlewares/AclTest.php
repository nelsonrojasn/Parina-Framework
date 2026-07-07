<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\Acl;
use Parina\Shared\Services\AclInterface;
use Parina\Core\Responses\ErrorResponse;

class AclTest extends TestCase
{
    public function test_acl_middleware_returns_null_when_has_permissions()
    {
        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_URI' => '/admin/dashboard'],
            files: [],
            cookies: []
        );

        $aclMock = $this->createMock(AclInterface::class);
        $aclMock->method('hasPermissions')
            ->with('/admin/dashboard')
            ->willReturn(true);

        $middleware = new Acl($aclMock);
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_acl_middleware_returns_error_response_when_no_permissions()
    {
        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_URI' => '/admin/restricted'],
            files: [],
            cookies: []
        );

        $aclMock = $this->createMock(AclInterface::class);
        $aclMock->method('hasPermissions')
            ->with('/admin/restricted')
            ->willReturn(false);

        $middleware = new Acl($aclMock);
        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals("Permission denied.", $response->getContent());
    }
}

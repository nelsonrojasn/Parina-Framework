<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\Acl;
use Parina\Shared\Services\Acl as AclService;
use Parina\Core\Responses\ErrorResponse;

class AclTest extends TestCase
{
    protected function tearDown(): void
    {
        AclService::setMockHasPermissions(null);
    }

    public function test_acl_middleware_returns_null_when_has_permissions()
    {
        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_URI' => '/admin/dashboard'],
            files: [],
            cookies: []
        );

        $middleware = new Acl();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_acl_middleware_returns_error_response_when_no_permissions()
    {
        AclService::setMockHasPermissions(false);

        $request = new Request(
            query: [],
            post: [],
            server: ['REQUEST_URI' => '/admin/restricted'],
            files: [],
            cookies: []
        );

        $middleware = new Acl();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals("Permission denied.", $response->getContent());
    }
}

<?php

namespace Tests\Features\UserManagement;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Features\UserManagement\Handlers\UsersListHandler;
use Parina\Core\Responses\HtmlResponse;

class UsersListHandlerTest extends TestCase
{
    public function test_handler_returns_valid_response()
    {
        $handler = new UsersListHandler();
        $request = new Request([], [], [], [], []);

        $response = $handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertStringContainsString('<h1>Listado de Usuarios</h1>', $response->getContent());
    }
}

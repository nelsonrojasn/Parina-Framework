<?php

namespace Tests\Handlers;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Modules\Admin\AdminHandler;
use Parina\Core\Responses\HtmlResponse;

class AdminHandlerTest extends TestCase
{
    public function test_handler_returns_valid_response()
    {
        $handler = new AdminHandler();
        $request = new Request([], [], [], [], []);

        $response = $handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertStringContainsString('<h1>Admin</h1>', $response->getContent());
    }
}

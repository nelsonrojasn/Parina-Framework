<?php

namespace Tests\Features\Marketing;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Features\Marketing\Handlers\HomeHandler;

class HomeHandlerTest extends TestCase
{
    public function test_handler_returns_valid_response()
    {
        $handler = new HomeHandler();
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatus());
    }
}
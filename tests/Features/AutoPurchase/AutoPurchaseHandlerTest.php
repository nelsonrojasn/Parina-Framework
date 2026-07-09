<?php

namespace Tests\Features\AutoPurchase;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Features\AutoPurchase\Handlers\AutoPurchaseHandler;

class AutoPurchaseHandlerTest extends TestCase
{
    public function test_handler_returns_valid_response()
    {
        $handler = new AutoPurchaseHandler();
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatus());
    }
}
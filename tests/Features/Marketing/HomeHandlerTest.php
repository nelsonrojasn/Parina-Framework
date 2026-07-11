<?php

namespace Tests\Features\Marketing;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Features\Marketing\Handlers\HomeHandler;

class HomeHandlerTest extends TestCase
{
    public function test_handler_returns_valid_response()
    {
        $configMock = $this->createMock(\Parina\Core\Interfaces\ConfigInterface::class);
        $configMock->method('getDbPath')->willReturn('/tmp/test.db');
        $configMock->method('allowSetup')->willReturn(true);
        $configMock->method('getTimeToLive')->willReturn(3600);
        $configMock->method('getCryptoKey')->willReturn('12345678901234567890123456789012');

        // We also create a container so that View render (which uses Container::getInstance()) works.
        $container = new \Parina\Core\Container();
        $container->bind(\Parina\Core\Interfaces\ConfigInterface::class, $configMock);

        $handler = new HomeHandler($configMock);
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatus());
    }
}
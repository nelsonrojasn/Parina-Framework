<?php

namespace Tests\Features\Database;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Interfaces\ConfigInterface;
use Parina\Shared\Services\DatabaseSetupServiceInterface;
use Parina\Features\Database\Handlers\SetupHandler;

class SetupHandlerTest extends TestCase
{
    public function test_handler_returns_valid_response()
    {
        // Mock de ConfigInterface
        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->method('allowSetup')->willReturn(true);
        $configMock->method('getDbConfig')->willReturn(['driver' => 'sqlite']);

        // Mock de DatabaseSetupServiceInterface
        $setupServiceMock = $this->createMock(DatabaseSetupServiceInterface::class);
        $setupServiceMock->expects($this->once())->method('setupDatabase');

        $handler = new SetupHandler($configMock, $setupServiceMock);
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(200, $response->getStatus());
    }

    public function test_handler_returns_forbidden_when_setup_not_allowed()
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->method('allowSetup')->willReturn(false);

        $setupServiceMock = $this->createMock(DatabaseSetupServiceInterface::class);
        $setupServiceMock->expects($this->never())->method('setupDatabase');

        $handler = new SetupHandler($configMock, $setupServiceMock);
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertEquals(403, $response->getStatus());
    }

    public function test_handler_returns_error_response_when_exception_thrown()
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->method('allowSetup')->willReturn(true);

        $setupServiceMock = $this->createMock(DatabaseSetupServiceInterface::class);
        $setupServiceMock->method('setupDatabase')->willThrowException(new \Exception("Connection failed"));

        $handler = new SetupHandler($configMock, $setupServiceMock);
        $request = new Request([], [], [], [], []);
        
        $response = $handler->handle($request);
        
        $this->assertNotNull($response);
        $this->assertInstanceOf(\Parina\Core\Responses\ErrorResponse::class, $response);
        $this->assertEquals(500, $response->getStatus());
        $this->assertStringContainsString("Connection failed", $response->getContent());
    }
}
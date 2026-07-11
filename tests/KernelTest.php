<?php

use PHPUnit\Framework\TestCase;
use Parina\Core\Router;
use Parina\Core\Kernel;
use Parina\Core\Request;

class KernelTest extends TestCase
{
    public function testKernelHtmlResponse()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $request = Request::capture();

        $router = new Router();
        $router->add('GET', '/', \Tests\Handlers\TestHandler::class);

        $kernel = new Kernel($router);
        $response = $kernel->handle($request);

        $this->assertEquals("<h1>TEST OK</h1>", $response->getContent());
        $this->assertEquals(200, $response->getStatus());
    }

    public function testKernelNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/non-existent';
        $request = Request::capture();

        $router = new Router();
        $kernel = new Kernel($router);
        $response = $kernel->handle($request);

        $this->assertInstanceOf(\Parina\Core\Responses\NotFoundResponse::class, $response);
    }

    public function testKernelMiddlewarePass()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $request = Request::capture();

        $router = new Router();
        $middlewareMock = $this->createMock(\Parina\Core\Interfaces\Middleware::class);
        $middlewareMock->method('handle')->willReturn(null);

        $router->add('GET', '/', \Tests\Handlers\TestHandler::class, [$middlewareMock]);

        $kernel = new Kernel($router);
        $response = $kernel->handle($request);

        $this->assertEquals(200, $response->getStatus());
    }

    public function testKernelMiddlewareShortCircuit()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $request = Request::capture();

        $router = new Router();
        $mockResponse = $this->createMock(\Parina\Core\Interfaces\Response::class);
        $middlewareMock = $this->createMock(\Parina\Core\Interfaces\Middleware::class);
        $middlewareMock->method('handle')->willReturn($mockResponse);

        $router->add('GET', '/', \Tests\Handlers\TestHandler::class, [$middlewareMock]);

        $kernel = new Kernel($router);
        $response = $kernel->handle($request);

        $this->assertSame($mockResponse, $response);
    }

    public function testKernelInvalidHandler()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $request = Request::capture();

        $router = new Router();
        // Add an invalid handler string that resolves to stdClass or register stdClass instance
        $container = new \Parina\Core\Container();
        $container->bind('stdClass', new \stdClass());

        $router->add('GET', '/', 'stdClass');

        $kernel = new Kernel($router, $container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Handler debe implementar HandlerInterface.");
        $kernel->handle($request);
    }
}

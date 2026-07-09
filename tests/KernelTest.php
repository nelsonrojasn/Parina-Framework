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
}

<?php

use PHPUnit\Framework\TestCase;
use Parina\Core\Router;
use Parina\Core\Kernel;

class KernelTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testKernelHtmlResponse()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $router = new Router();
        $router->add('GET', '/', \Tests\Handlers\TestHandler::class);

        $kernel = new Kernel($router);

        ob_start();
        $kernel->run();
        $output = ob_get_clean(); // cerramos el buffer aquí mismo

        $this->assertEquals("<h1>TEST OK</h1>", $output);
    }
}

<?php

use PHPUnit\Framework\TestCase;
use Parina\Core\Router;

class RouterTest extends TestCase
{
    public function testRouteMatchSimple()
    {
        $router = new Router();
        $router->add('GET', '/', 'HomeHandler');

        $match = $router->match('GET', '/');

        $this->assertNotNull($match);
        $this->assertEquals('HomeHandler', $match['route']['handler']);
    }

    public function testRouteWithParameters()
    {
        $router = new Router();
        $router->add('GET', '/instancia/{hash}', 'InstHandler');

        $match = $router->match('GET', '/instancia/abc123');

        $this->assertNotNull($match);
        $this->assertEquals('InstHandler', $match['route']['handler']);
        $this->assertEquals(['hash' => 'abc123'], $match['params']);
    }

    public function testRouteNoMatch()
    {
        $router = new Router();
        $router->add('GET', '/home', 'HomeHandler');

        $match = $router->match('GET', '/about');

        $this->assertNull($match);
    }

    public function testRouteMethodMismatch()
    {
        $router = new Router();
        $router->add('POST', '/submit', 'SubmitHandler');

        $match = $router->match('GET', '/submit');

        $this->assertNull($match);
    }
}

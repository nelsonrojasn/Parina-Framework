<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;

class RequestTest extends TestCase
{
    public function test_request_instantiation_and_getters()
    {
        $request = new Request(
            query: ['foo' => 'bar'],
            post: ['baz' => 'qux'],
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/hello/world?foo=bar'],
            files: [],
            cookies: [],
            params: ['id' => 123]
        );

        $this->assertEquals('bar', $request->query('foo'));
        $this->assertEquals('qux', $request->post('baz'));
        $this->assertEquals('POST', $request->method());
        $this->assertEquals('/hello/world', $request->path());
        $this->assertEquals(123, $request->param('id'));
        $this->assertEquals('POST', $request->server('REQUEST_METHOD'));
    }

    public function test_default_values_and_filtering()
    {
        $request = new Request(
            query: ['number' => '123a'],
            post: [],
            server: [],
            files: [],
            cookies: []
        );

        $this->assertEquals('default_val', $request->query('missing_key', 'default_val'));
        $this->assertEquals('default_val', $request->post('missing_key', 'default_val'));
        $this->assertEquals('default_val', $request->param('missing_key', 'default_val'));
        $this->assertEquals('default_val', $request->server('missing_key', 'default_val'));

        // Probar filtrado (FILTER_VALIDATE_INT debería retornar false para '123a')
        $this->assertFalse($request->query('number', null, FILTER_VALIDATE_INT));
    }

    public function test_request_capture()
    {
        $_GET['test_get'] = 'get_val';
        $_POST['test_post'] = 'post_val';
        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        $request = Request::capture();

        $this->assertEquals('get_val', $request->query('test_get'));
        $this->assertEquals('post_val', $request->post('test_post'));
        $this->assertEquals('PATCH', $request->method());

        // Limpiar
        unset($_GET['test_get'], $_POST['test_post'], $_SERVER['REQUEST_METHOD']);
    }
}

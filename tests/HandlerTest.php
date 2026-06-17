<?php

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Tests\Handlers\TestHandler;
use Tests\Handlers\ParamHandler;
use Parina\Core\Responses\PlainTextResponse;


class HandlerTest extends TestCase
{
    public function testSimpleHandlerReturnsHtml()
    {
        $handler = new TestHandler();
        $request = Request::capture();

        $response = $handler->handle($request);

        $this->assertEquals("<h1>TEST OK</h1>", $response->getContent());
    }

    public function testParamHandlerReceivesParameter()
    {
        $handler = new ParamHandler();
        $request = Request::capture();
        $request->params['hash'] = 'abc999';
        
        $response = $handler->handle($request);

        $this->assertEquals("<h1>abc999</h1>", $response->getContent());
    }

    public function testParamHandlerReceivesMultipleParams()
    {
        // Create an anonymous handler that uses multiple params
        $request = Request::capture();
        $handler = new class implements Handler {
            public function handle(Request $request): Response
            {
                $a = $request->params['a'];
                $b = $request->params['b'];
                $c = $request->params['c'];

                return (new PlainTextResponse("$a-$b-$c"));
            }
        };

        $request->params['a'] = 'uno';
        $request->params['b'] = 'dos';
        $request->params['c'] = 'tres';

        $response = $handler->handle($request);

        $this->assertEquals("uno-dos-tres", $response->getContent());
    }

    public function testHandlerMustImplementInterface()
    {
        $this->assertInstanceOf(
            Handler::class,
            new TestHandler()
        );
    }

    public function testHandlerThrowsExceptionIfNeeded()
    {
        $handler = new class implements Handler {
            public function handle(Request $request): Response
            {
                throw new \RuntimeException("boom");
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("boom");

        $handler->handle(Request::capture());
    }
}

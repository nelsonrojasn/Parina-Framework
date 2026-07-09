<?php

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\RequestInterface;
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
        $request->setParam('hash', 'abc999');
        
        $response = $handler->handle($request);

        $this->assertEquals("<h1>abc999</h1>", $response->getContent());
    }

    public function testParamHandlerReceivesMultipleParams()
    {
        // Create an anonymous handler that uses multiple params
        $request = Request::capture();
        $handler = new class implements Handler {
            public function handle(RequestInterface $request): Response
            {
                $a = $request->param('a');
                $b = $request->param('b');
                $c = $request->param('c');

                return (new PlainTextResponse("$a-$b-$c"));
            }
        };

        $request->setParam('a', 'uno');
        $request->setParam('b', 'dos');
        $request->setParam('c', 'tres');

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
            public function handle(RequestInterface $request): Response
            {
                throw new \RuntimeException("boom");
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("boom");

        $handler->handle(Request::capture());
    }
}

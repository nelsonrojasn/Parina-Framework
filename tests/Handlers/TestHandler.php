<?php

namespace Tests\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\PlainTextResponse;

class TestHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {
        return (new PlainTextResponse("<h1>TEST OK</h1>", 200));
    }
}

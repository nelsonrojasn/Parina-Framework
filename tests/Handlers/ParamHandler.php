<?php

namespace Tests\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Interfaces\Response;

class ParamHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {
        $hash = $request->params['hash'];
        return (new HtmlResponse("<h1>$hash</h1>", 200));
    }
}

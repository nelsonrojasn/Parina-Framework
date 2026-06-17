<?php

namespace Tests\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Interfaces\Response;

class ParamHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $hash = $request->params['hash'];
        return (new HtmlResponse("<h1>$hash</h1>", 200));
    }
}

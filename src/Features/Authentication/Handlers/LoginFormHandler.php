<?php

namespace Parina\Features\Authentication\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Config;
use Parina\Core\View;


class LoginFormHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {        
        $content = View::renderWithLayout("Authentication/Views/login", "default");
        return (new HtmlResponse($content, 200));
    }
}
<?php

namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Config;
use Parina\Core\View;


class LoginFormHandler implements Handler
{
    public function handle(Request $request): Response
    {        
        $content = View::renderWithLayout("Public/Views/login", "default");
        return (new HtmlResponse($content, 200));
    }
}
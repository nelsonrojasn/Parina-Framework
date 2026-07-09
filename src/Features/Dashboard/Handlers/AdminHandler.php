<?php

namespace Parina\Features\Dashboard\Handlers;

use Parina\Core\View;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;

class AdminHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Dashboard/Views/home", "default");
        return (new HtmlResponse($content, 200));        
    }
}
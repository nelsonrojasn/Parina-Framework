<?php

namespace Parina\Modules\Admin;

use Parina\Core\View;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;

class AdminHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Admin/views/admin/home", "default");
        return (new HtmlResponse($content, 200));        
    }
}
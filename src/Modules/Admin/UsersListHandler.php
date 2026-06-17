<?php

namespace Parina\Modules\Admin;

use Parina\Core\View;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;

class UsersListHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Admin/Views/users/list", "default");
        return (new HtmlResponse($content, 200));        
    }    
}
<?php

namespace Parina\Features\UserManagement\Handlers;

use Parina\Core\View;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;

class UsersListHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {
        $content = View::renderWithLayout("UserManagement/Views/list", "default");
        return (new HtmlResponse($content, 200));        
    }    
}
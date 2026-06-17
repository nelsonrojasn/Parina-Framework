<?php

namespace Parina\Modules\Private;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Session;
use Parina\Core\Responses\RedirectResponse;


class LogoutHandler implements Handler
{
    public function handle(Request $request): Response
    {
        Session::clear();
        return (new RedirectResponse('/', 302));
    }
}
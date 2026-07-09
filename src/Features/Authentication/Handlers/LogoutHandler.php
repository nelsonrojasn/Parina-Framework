<?php

namespace Parina\Features\Authentication\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Session;
use Parina\Core\Responses\RedirectResponse;


class LogoutHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {
        Session::clear();
        return (new RedirectResponse('/', 302));
    }
}
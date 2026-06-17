<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Services\Auth as AuthService;
use Parina\Core\Responses\ErrorResponse;

class Auth implements Middleware
{
    public function handle(Request $request): ?Response
    {
        if (!AuthService::isLoggedIn()) {
            return (new ErrorResponse("Not logged in.", 403));
        }

        //it's all good, go to next middleware
        return null;
    }
}

<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Models\User;
use Parina\Core\Responses\BasicRealmResponse;

class BasicAuth implements Middleware
{
    public function handle(Request $request): ?Response
    {
        if (empty($request->server('PHP_AUTH_USER')) || empty($request->server('PHP_AUTH_PW'))) {
            return (new BasicRealmResponse("Unauthorized", 401));
        }

        $userModel = new User();
        $user = $request->server('PHP_AUTH_USER');
        $password = $request->server('PHP_AUTH_PW');

        if ($userModel->checkAuth($user, $password) === false) {
            return (new BasicRealmResponse("Unauthorized", 401));
        }

        //it's all good, go to next middleware
        return null;
    }
}

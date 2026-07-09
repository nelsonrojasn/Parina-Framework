<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Services\AuthInterface;
use Parina\Core\Responses\ErrorResponse;

class Auth implements Middleware
{
    private AuthInterface $auth;

    public function __construct(AuthInterface $auth)
    {
        $this->auth = $auth;
    }

    public function handle(RequestInterface $request): ?Response
    {
        if (!$this->auth->isLoggedIn()) {
            return (new ErrorResponse("Not logged in.", 403));
        }

        //it's all good, go to next middleware
        return null;
    }
}

<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class SameOrigin implements Middleware
{
    public function handle(RequestInterface $request): ?Response
    {
        $origin = $request->server('HTTP_ORIGIN');
        $host   = $request->server('HTTP_HOST');

        if ($origin && !str_contains($origin, $host)) {
            return (new ErrorResponse("Forbidden (same-origin)", 403));
        }

        //it's all good, go to next middleware
        return null;
    }
}

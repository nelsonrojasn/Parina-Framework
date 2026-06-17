<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class SameOrigin implements Middleware
{
    public function handle(Request $request): ?Response
    {
        $origin = $request->server['HTTP_ORIGIN'] ?? null;
        $host   = $request->server['HTTP_HOST'] ?? null;

        if ($origin && !str_contains($origin, $host)) {
            return (new ErrorResponse("Forbidden (same-origin)", 403));
        }

        //it's all good, go to next middleware
        return null;
    }
}

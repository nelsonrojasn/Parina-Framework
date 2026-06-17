<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Shared\Security\Csrf as CsrfValidator;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class Csrf implements Middleware
{
    public function handle(Request $request): ?Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            if (!CsrfValidator::validate($request->post('_csrf'))) {
                return (new ErrorResponse("Invalid CSRF token.", 403));
            }
        }
        //it's all good, go to next middleware
        return null;
    }
}

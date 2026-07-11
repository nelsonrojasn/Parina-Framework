<?php
declare(strict_types=1);
namespace Parina\Shared\Middlewares;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Shared\Security\Csrf as CsrfValidator;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class Csrf implements Middleware
{
    public function handle(RequestInterface $request): ?Response
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

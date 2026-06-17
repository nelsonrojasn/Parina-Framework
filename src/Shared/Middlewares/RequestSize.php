<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;

use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class RequestSize implements Middleware
{
    public function handle(Request $request): ?Response
    {        
        if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > MAX_REQUEST_SIZE) {
            return (new ErrorResponse("Request length exceeded.", 413));
        }

        //it's all good, go to next middleware
        return null; 
    }
}

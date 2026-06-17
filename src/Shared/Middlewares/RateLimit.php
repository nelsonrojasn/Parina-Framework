<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Session;
use Parina\Core\Responses\ErrorResponse;

class RateLimit implements Middleware
{
    public function handle(Request $request): ?Response
    {
        $bypass = Session::get('_pin_bypass_limit');
        $last_request = Session::get('_pin_last_req') ?? 0;
        $current_time = microtime(true);
        if (!$bypass && defined('RATE_LIMIT_MS') && RATE_LIMIT_MS > 0) {
            if (($current_time - $last_request) < (RATE_LIMIT_MS / 1000)) {
                return (new ErrorResponse('Too many requests. Please wait a momment and try again.', 429));
            }
        }
        Session::set('_pin_last_req', $current_time);
        
        //it's all good, go to next middleware
        return null; 
    }
}

<?php
declare(strict_types=1);
namespace Parina\Shared\Middlewares;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Session;
use Parina\Core\Responses\ErrorResponse;
use Parina\Core\Interfaces\ConfigInterface;

class RateLimit implements Middleware
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function handle(RequestInterface $request): ?Response
    {
        $bypass = Session::get('_pin_bypass_limit');
        $last_request = Session::get('_pin_last_req') ?? 0;
        $current_time = microtime(true);
        $rateLimit = $this->config->getRateLimitMs();
        if (!$bypass && $rateLimit > 0) {
            if (($current_time - $last_request) < ($rateLimit / 1000)) {
                return (new ErrorResponse('Too many requests. Please wait a momment and try again.', 429));
            }
        }
        Session::set('_pin_last_req', $current_time);
        
        //it's all good, go to next middleware
        return null; 
    }
}

<?php
declare(strict_types=1);
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;
use Parina\Core\Interfaces\ConfigInterface;
use Parina\Core\Interfaces\RequestInterface;

class RequestSize implements Middleware
{
    private ConfigInterface $config;

    public function __construct(?ConfigInterface $config = null)
    {
        $this->config = $config ?? new \Parina\Core\AppConfig();
    }

    public function handle(RequestInterface $request): ?Response
    {        
        if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > $this->config->getMaxRequestSize()) {
            return (new ErrorResponse("Request length exceeded.", 413));
        }

        //it's all good, go to next middleware
        return null; 
    }
}

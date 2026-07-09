<?php
namespace Parina\Core;

use Parina\Core\Router;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\NotFoundResponse;

use Parina\Core\Interfaces\RequestInterface;

class Kernel
{
    private Container $container;

    public function __construct(
        private Router $router,
        ?Container $container = null
    ) {
        $this->container = $container ?? new Container();
    }

    public function handle(RequestInterface $request): Response
    {
        $method  = $request->method();
        $uri     = $request->path();

        // Find a defined route
        $match = $this->router->match($method, $uri);

        if (!$match) {
            return new NotFoundResponse();
        }

        $route  = $match['route'];
        $request->setParams($match['params']);

        // Execute middlewares if available
        foreach ($route['middleware'] as $mw) {
            $mwInstance = is_string($mw) ? $this->container->get($mw) : $mw;
            $response = $mwInstance->handle($request, $route);
            
            // if middleware returns a response, we break the execution
            if ($response !== null) {
                return $response;
            }
        }

        // Instantiate handler
        $handler = $route['handler'];

        if (is_string($handler)) {
            $handler = $this->container->get($handler);
        }

        if (!$handler instanceof Handler) {
            throw new \RuntimeException("Handler debe implementar HandlerInterface.", 401);
        }

        // Ejecutar handler con parámetros
        return $handler->handle($request);
    }
}

<?php
namespace Parina\Core;

use Parina\Core\Router;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Responses\NotFoundResponse;


class Kernel
{

    private Container $container;

    public function __construct(private Router $router, ?Container $container = null)
    {
        $this->container = $container ?? new Container();
    }

    public function run(): void
    {
        $request = Request::capture();
        $method  = $request->method();
        $uri     = $request->path();

        // Find a defined route
        $match = $this->router->match($method, $uri);

        if (!$match) {
            $this->send((new NotFoundResponse()));
            return;
        }

        $route  = $match['route'];
        $request->params = $match['params'];

        // Execute middlewares if available
        foreach ($route['middleware'] as $mw) {
            $mwInstance = is_string($mw) ? $this->container->get($mw) : $mw;
            $response = $mwInstance->handle($request, $route);
            // if middleware doesn't return null, we break the execution
            if ($response !== null) {
                $this->send($response);
                return;
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
        $result = $handler->handle($request);
        $this->send($result);
    }

    private function send(mixed $result): void
    {
        if ($result === null) {
            return;
        }

        // Send Status Code
        http_response_code($result->getStatus());

        // Send Headers
        foreach ($result->getHeaders() as $name => $value) {
            header("$name: $value");
        }

        // Send body
        echo $result->getContent();
        
        // it is a redirection — stop further processing by returning to caller
        if ($result->getStatus() >= 300 && $result->getStatus() < 400) {
            return;
        }
    }
}

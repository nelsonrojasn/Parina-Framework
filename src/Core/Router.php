<?php
namespace Parina\Core;

class Router
{
    private array $routes = [];

    public function add(
        string $method,
        string $path,
        string|object $handler,
        array $middleware = [],
        array $acl = []
    ): void
    {
        // Convertir {param} → (?P<param>[^/]+)
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);

        // Regex final
        $regex = '#^' . $regex . '$#';

        // Guardar ruta como arreglo asociativo
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => $middleware,
            'acl'        => $acl
        ];
    }

    public function match(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {

            // Método HTTP debe coincidir
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            // Intentamos match del regex
            if (preg_match($route['regex'], $uri, $matches)) {

                // Filtrar sólo parámetros con nombre
                $params = array_filter(
                    $matches,
                    fn($k) => !is_int($k),
                    ARRAY_FILTER_USE_KEY
                );

                return [
                    'route'  => $route,
                    'params' => $params
                ];
            }
        }

        return null;
    }
}

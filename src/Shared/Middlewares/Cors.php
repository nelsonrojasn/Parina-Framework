<?php

namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\PlainTextResponse;

class Cors implements Middleware
{
    public function handle(Request $request): ?Response
    {
        // En un entorno real, podrías obtener esto de Parina\Core\Config
        $allowedOrigin = '*'; 
        
        // Seteamos las cabeceras básicas de CORS
        // Usamos header() directamente para que se apliquen incluso si la cadena de middlewares falla después
        header("Access-Control-Allow-Origin: $allowedOrigin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");

        // Si la petición es de tipo OPTIONS (Preflight), respondemos con un 204 (No Content)
        // y cortamos la ejecución del resto del stack.
        if ($request->method() === 'OPTIONS') {
            return new PlainTextResponse('', 204);
        }

        // Para el resto de métodos (GET, POST, etc.), permitimos que continúe el flujo
        return null;
    }
}
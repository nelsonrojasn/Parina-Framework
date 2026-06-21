<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Services\JwtAuth as JwtService;
use Parina\Core\Responses\UnauthorizedResponse;
use Parina\Core\Session;

class JwtAuth implements Middleware
{
    public function handle(Request $request): ?Response
    {
        // En algunos servidores PHP, el header puede venir en $_SERVER['HTTP_AUTHORIZATION']
        $authHeader = $request->server['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new UnauthorizedResponse("Token missing or malformed.");
        }
        
        $token = $matches[1];
        $payload = JwtService::validateToken($token);

        if (!$payload) {
            return new UnauthorizedResponse("Invalid or expired token.");
        }

        // Inyectar datos del usuario para que el Handler los tenga disponibles
        Session::set('user_id', $payload['sub'] ?? null);
        Session::set('user_data', $payload);

        return null; // Todo bien, siguiente middleware/handler
    }
}
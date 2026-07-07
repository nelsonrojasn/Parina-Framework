<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Services\TokenServiceInterface;
use Parina\Core\Responses\UnauthorizedResponse;
use Parina\Core\Session;

class JwtAuth implements Middleware
{
    private TokenServiceInterface $tokenService;

    public function __construct(?TokenServiceInterface $tokenService = null)
    {
        $this->tokenService = $tokenService ?? new \Parina\Shared\Services\JwtTokenService();
    }

    public function handle(Request $request): ?Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return new UnauthorizedResponse("Token missing or malformed.");
        }
        
        $payload = $this->tokenService->validateToken($token);

        if (!$payload) {
            return new UnauthorizedResponse("Invalid or expired token.");
        }

        // Store in Request Attributes (for stateless handlers)
        $request->setAttribute('user_id', $payload['sub'] ?? null);
        $request->setAttribute('user_data', $payload);

        // Store in Session (backward compatibility for legacy handlers)
        Session::set('user_id', $payload['sub'] ?? null);
        Session::set('user_data', $payload);

        return null; // Todo bien, siguiente middleware/handler
    }
}
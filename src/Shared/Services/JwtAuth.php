<?php
declare(strict_types=1);

namespace Parina\Shared\Services;

class JwtAuth
{
    private static function getService(): TokenServiceInterface
    {
        static $service = null;
        if ($service === null) {
            $service = new JwtTokenService(new \Parina\Core\AppConfig());
        }
        return $service;
    }

    /**
     * Generates a JWT token (facade delegation)
     */
    public static function createToken(array $payload): string
    {
        return self::getService()->createToken($payload);
    }

    /**
     * Validates a JWT token (facade delegation)
     */
    public static function validateToken(string $token): ?array
    {
        return self::getService()->validateToken($token);
    }
}
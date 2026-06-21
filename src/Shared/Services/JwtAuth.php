<?php
declare(strict_types=1);

namespace Parina\Shared\Services;

use Parina\Core\Config;

class JwtAuth
{
    /**
     * Genera un token JWT para un usuario
     */
    public static function createToken(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Añadir timestamps estándar
        $payload['iat'] = time();
        $payload['exp'] = time() + (Config::getTimeToLive() ?? 3600);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, Config::getCryptoKey(), true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Valida un token y retorna el payload si es correcto
     */
    public static function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        // Verificar firma
        $validSignature = hash_hmac('sha256', $header . "." . $payload, Config::getCryptoKey(), true);
        if (!hash_equals(self::base64UrlEncode($validSignature), $signature)) {
            return null;
        }

        $data = json_decode(self::base64UrlDecode($payload), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return null;
        }

        // Verificar expiración
        if (isset($data['exp']) && $data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    private static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
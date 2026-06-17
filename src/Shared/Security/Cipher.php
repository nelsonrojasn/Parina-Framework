<?php

namespace Parina\Shared\Security;

use Parina\Core\Config;


abstract class Cipher
{
    public static function encrypt(string $data, string $key): string {
        $method = 'aes-256-cbc';
        $ivLength = openssl_cipher_iv_length($method); // 16 bytes
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        // Use OPENSSL_RAW_DATA to get raw binary encryption (more compact)
        $encryptedRaw = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        
        // Calculate a signature to ensure data integrity
        $hmac = hash_hmac('sha256', $iv . $encryptedRaw, $key, true); // 32 bytes
        
        // Pack everything sequentially: IV (16) + HMAC (32) + Encrypted Data
        $base64 = base64_encode($iv . $hmac . $encryptedRaw);
        return str_replace(['+', '/', '='], ['-', '_', ''], $base64);
    }

    public static function decrypt(string $encryptedData, string $key): string {
        $method = 'aes-256-cbc';
        $base64 = str_replace(['-', '_'], ['+', '/'], $encryptedData);
        // PHP handles missing padding (=) automatically in base64_decode
        $payload = base64_decode($base64);
        
        $ivLength = openssl_cipher_iv_length($method); // 16
        $hmacLength = 32; // SHA256 raw is 32 bytes
        
        // Extract parts using fixed positions
        $iv = substr($payload, 0, $ivLength);
        $receivedHmac = substr($payload, $ivLength, $hmacLength);
        $encryptedRaw = substr($payload, $ivLength + $hmacLength);
        
        // Verify signature before attempting decryption
        $calculatedHmac = hash_hmac('sha256', $iv . $encryptedRaw, $key, true);
        
        // hash_equals protects against timing attacks
        if (!hash_equals($receivedHmac, $calculatedHmac)) {
            return ''; // Manipulated data or incorrect key
        }
        
        return openssl_decrypt($encryptedRaw, $method, $key, OPENSSL_RAW_DATA, $iv) ?: '';
    }

    
    /**
     * Generate an encrypted URL hash for use in links
     * @param string $action - The action name
     * @param array $parameters - Optional additional parameters
     * @return string - The encrypted URL hash
     */
    public static function encryptUrl(string $action, ...$parameters): string
    {
        // Construir el query string
        $query_parts = ['action' => $action];
        
        // Añadir parámetros adicionales
        $query_parts = array_merge($query_parts, $parameters);

        // Añadir el sello de tiempo para "quemar" la URL
        $query_parts['_ttl'] = time() + Config::getTimeToLive();

        // Generar query string
        $query_string = http_build_query($query_parts);

        // Encriptar y retornar
        return self::encrypt($query_string, Config::getCryptoKey());
    }

    /**
     * Parse and decrypt an encrypted URL hash
     * Extracts page, action, and parameters from the encrypted URL
     * @param string $encrypted_url - The encrypted URL hash
     * @return array - [page, action, parameters] where parameters is an associative array
     * @throws Exception - If decryption fails or format is invalid
     */
    public static function parseUrlHash(string $encrypted_url): array
    {
        $decrypted = self::decrypt($encrypted_url, Config::getCryptoKey());
        
        if (empty($decrypted)) {
            throw new \Exception("Security validation failed");
        }

        // Remover el '?' inicial
        if (strpos($decrypted, '?') === 0) {
            $decrypted = substr($decrypted, 1);
        }

        // Parsear como query string
        $parsed = [];
        parse_str($decrypted, $parsed);

        // Verificar integridad y expiración del sello de tiempo (TTL)
        if (!isset($parsed['_ttl']) || (int)$parsed['_ttl'] < time()) {
            throw new \Exception("Security validation failed");
        }

        // Extraer componentes requeridos
        $action = $parsed['action'] ?? null;
        
        // Limpiar parámetros internos antes de devolver el array al enrutador
        unset($parsed['action'], $parsed['_ttl']);
        $parameters = $parsed;

        if (!$action) {
            throw new \Exception("Security validation failed");
        }
        return [$action, $parameters];
    }

}
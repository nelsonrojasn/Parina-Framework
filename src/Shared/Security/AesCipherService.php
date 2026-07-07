<?php
declare(strict_types=1);

namespace Parina\Shared\Security;

use Parina\Core\Config;

class AesCipherService implements CipherInterface
{
    public function encrypt(string $data, string $key): string
    {
        $method = 'aes-256-cbc';
        $ivLength = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encryptedRaw = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $iv . $encryptedRaw, $key, true);
        $base64 = base64_encode($iv . $hmac . $encryptedRaw);
        return str_replace(['+', '/', '='], ['-', '_', ''], $base64);
    }

    public function decrypt(string $encryptedData, string $key): string
    {
        $method = 'aes-256-cbc';
        $base64 = str_replace(['-', '_'], ['+', '/'], $encryptedData);
        $payload = base64_decode($base64);
        
        $ivLength = openssl_cipher_iv_length($method);
        $hmacLength = 32;
        
        $iv = substr($payload, 0, $ivLength);
        $receivedHmac = substr($payload, $ivLength, $hmacLength);
        $encryptedRaw = substr($payload, $ivLength + $hmacLength);
        
        $calculatedHmac = hash_hmac('sha256', $iv . $encryptedRaw, $key, true);
        
        if (!hash_equals($receivedHmac, $calculatedHmac)) {
            return '';
        }
        
        return openssl_decrypt($encryptedRaw, $method, $key, OPENSSL_RAW_DATA, $iv) ?: '';
    }

    public function encryptUrl(string $action, ...$parameters): string
    {
        $query_parts = ['action' => $action];
        $query_parts = array_merge($query_parts, $parameters);
        $query_parts['_ttl'] = time() + Config::getTimeToLive();
        $query_string = http_build_query($query_parts);
        return $this->encrypt($query_string, Config::getCryptoKey());
    }

    public function parseUrlHash(string $encrypted_url): array
    {
        $decrypted = $this->decrypt($encrypted_url, Config::getCryptoKey());
        
        if (empty($decrypted)) {
            throw new \Exception("Security validation failed");
        }

        if (strpos($decrypted, '?') === 0) {
            $decrypted = substr($decrypted, 1);
        }

        $parsed = [];
        parse_str($decrypted, $parsed);

        if (!isset($parsed['_ttl']) || (int)$parsed['_ttl'] < time()) {
            throw new \Exception("Security validation failed");
        }

        $action = $parsed['action'] ?? null;
        unset($parsed['action'], $parsed['_ttl']);
        $parameters = $parsed;

        if (!$action) {
            throw new \Exception("Security validation failed");
        }
        return [$action, $parameters];
    }
}

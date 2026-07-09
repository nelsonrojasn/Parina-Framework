<?php

namespace Parina\Shared\Security;

class Cipher
{
    private static function getService(): CipherInterface
    {
        static $service = null;
        if ($service === null) {
            $service = new AesCipherService(new \Parina\Core\AppConfig());
        }
        return $service;
    }

    public static function encrypt(string $data, string $key): string
    {
        return self::getService()->encrypt($data, $key);
    }

    public static function decrypt(string $encryptedData, string $key): string
    {
        return self::getService()->decrypt($encryptedData, $key);
    }

    public static function encryptUrl(string $action, ...$parameters): string
    {
        return self::getService()->encryptUrl($action, ...$parameters);
    }

    public static function parseUrlHash(string $encrypted_url): array
    {
        return self::getService()->parseUrlHash($encrypted_url);
    }
}
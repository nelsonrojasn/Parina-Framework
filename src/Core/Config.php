<?php

namespace Parina\Core;

class Config
{
    private static string $cryptoKey = '';
    private static string $dbPath = '';
    private static int $rateLimitMs = 500;

    public static function getRateLimitMs(): int
    {
        return self::$rateLimitMs;
    }

    public static function setRateLimitMs(int $ms): void
    {
        self::$rateLimitMs = $ms;
    }

    public static function getCryptoKey(): string
    {
        // Hash is executed ONLY the first time it's requested in the request lifecycle
        if (self::$cryptoKey === '') {
            self::$cryptoKey = hash('sha256', 'AX18-12A.AaC4n7.@$%&@#_ParinaUltraSecret.', true);
        }
        return self::$cryptoKey;
    }

    public static function getDbPath(): string
    {
        if (self::$dbPath === '') {
            self::$dbPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Db' . DIRECTORY_SEPARATOR . 'app.sqlite';
        }
        return self::$dbPath;
    }

    public static function getTimeToLive(): int
    {
        return 60 * 30; // 30 minutes
    }

    public static function getMaxRequestSize(): int
    {
        return 1024 * 1024 * 5; // 10 MB
    }

    public static function allowSetup(): bool
    {
        return true;
    }

    public static function getDbConfig(string $env='default'): array
    {
        $dbConfig =[
            'default'=>[
                'dsn' => 'sqlite:' . Config::getDbPath(),
                'driver' => 'sqlite',
                'host' => '',
                'port' => '',
                'user' => '',
                'pass' => '',
                'params' => []
            ],
        ];
        return $dbConfig[$env];
    }

}
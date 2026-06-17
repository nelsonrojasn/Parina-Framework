<?php

namespace Parina\Core;

class Config
{
    private static ?string $cryptoKey = null;
    private static ?string $dbPath = null;

    public static function getCryptoKey(): string
    {
        // Hash is executed ONLY the first time it's requested in the request lifecycle
        if (self::$cryptoKey === null) {
            self::$cryptoKey = hash('sha256', 'AX18-12A.AaC4n7.@$%&@#_PinZeroSecret', true);
        }
        return self::$cryptoKey;
    }

    public static function getDbPath(): string
    {
        if (self::$dbPath === null) {
            self::$dbPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Db' . DIRECTORY_SEPARATOR . 'app.sqlite';
        }
        return self::$dbPath;
    }

    public static function getTimeToLive(): int
    {
        return 60 * 30; // 30 minutes
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
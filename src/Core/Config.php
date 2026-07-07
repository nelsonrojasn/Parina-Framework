<?php

namespace Parina\Core;

class Config
{
    private static ?AppConfig $instance = null;

    private static function getInstance(): AppConfig
    {
        if (self::$instance === null) {
            self::$instance = new AppConfig();
        }
        if (self::$instance === null) {
            throw new \RuntimeException("Config instance not initialized.");
        }
        return self::$instance;
    }

    public static function getRateLimitMs(): int
    {
        return self::getInstance()->getRateLimitMs();
    }

    public static function setRateLimitMs(int $ms): void
    {
        self::getInstance()->setRateLimitMs($ms);
    }

    public static function getCryptoKey(): string
    {
        return self::getInstance()->getCryptoKey();
    }

    public static function getDbPath(): string
    {
        return self::getInstance()->getDbPath();
    }

    public static function getTimeToLive(): int
    {
        return self::getInstance()->getTimeToLive();
    }

    public static function getMaxRequestSize(): int
    {
        return self::getInstance()->getMaxRequestSize();
    }

    public static function allowSetup(): bool
    {
        return self::getInstance()->allowSetup();
    }

    public static function getDbConfig(string $env = 'default'): array
    {
        return self::getInstance()->getDbConfig($env);
    }
}
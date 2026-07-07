<?php

namespace Parina\Shared\Services;

class Auth
{
    private static ?SessionAuth $instance = null;

    private static function getInstance(): SessionAuth
    {
        if (self::$instance === null) {
            self::$instance = new SessionAuth();
        }
        return self::$instance;
    }

    public static function init(): void
    {
        self::getInstance()->init();
    }

    public static function isLoggedIn(): bool
    {
        return self::getInstance()->isLoggedIn();
    }

    public static function login(array $user): void
    {
        self::getInstance()->login($user);
    }

    public static function logout(): void
    {
        self::getInstance()->logout();
    }
}
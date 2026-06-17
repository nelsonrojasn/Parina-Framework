<?php

namespace Parina\Shared\Services;

class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Ajustes de seguridad mínimos para la cookie de sesión
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            session_start();
        }
    }

    public static function isLoggedIn(): bool
    {
        self::init();
        return isset($_SESSION['user_id']) && $_SESSION['active'] === true;
    }

    public static function login(array $user): void
    {
        self::init();
        // Guardas solo lo esencial para no inflar la memoria
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['active']     = true;
    }

    public static function logout(): void
    {
        self::init();
        $_SESSION = [];
        session_destroy();
    }
}
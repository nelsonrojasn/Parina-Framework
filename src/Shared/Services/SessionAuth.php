<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

class SessionAuth implements AuthInterface
{
    public function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Ajustes de seguridad mínimos para la cookie de sesión
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            session_start();
        }
    }

    public function isLoggedIn(): bool
    {
        $this->init();
        return isset($_SESSION['user_id']) && $_SESSION['active'] === true;
    }

    public function login(array $user): void
    {
        $this->init();
        // Guardas solo lo esencial para no inflar la memoria
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['active']     = true;
    }

    public function logout(): void
    {
        $this->init();
        $_SESSION = [];
        session_destroy();
    }
}

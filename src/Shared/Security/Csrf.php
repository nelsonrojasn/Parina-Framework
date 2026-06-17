<?php
namespace Parina\Shared\Security;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validate(?string $token = null): bool
    {
        $token = $token ?? $_POST['_csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        return true;
    }
}

<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

interface AuthInterface
{
    public function init(): void;
    public function isLoggedIn(): bool;
    public function login(array $user): void;
    public function logout(): void;
}

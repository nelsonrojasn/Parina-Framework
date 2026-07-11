<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

interface TokenServiceInterface
{
    public function createToken(array $payload): string;
    public function validateToken(string $token): ?array;
}

<?php

namespace Parina\Shared\Services;

interface TokenServiceInterface
{
    public function createToken(array $payload): string;
    public function validateToken(string $token): ?array;
}

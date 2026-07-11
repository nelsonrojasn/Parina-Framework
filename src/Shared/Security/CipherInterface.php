<?php
declare(strict_types=1);
namespace Parina\Shared\Security;

interface CipherInterface
{
    public function encrypt(string $data, string $key): string;
    public function decrypt(string $encryptedData, string $key): string;
    public function encryptUrl(string $action, ...$parameters): string;
    public function parseUrlHash(string $encrypted_url): array;
}

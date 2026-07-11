<?php
declare(strict_types=1);
namespace Parina\Core\Interfaces;

interface ConfigInterface
{
    public function getRateLimitMs(): int;
    public function setRateLimitMs(int $ms): void;
    public function getCryptoKey(): string;
    public function getDbPath(): string;
    public function getTimeToLive(): int;
    public function getMaxRequestSize(): int;
    public function allowSetup(): bool;
    public function getDbConfig(string $env = 'default'): array;
}

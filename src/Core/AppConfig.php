<?php
declare(strict_types=1);
namespace Parina\Core;

use Parina\Core\Interfaces\ConfigInterface;

class AppConfig implements ConfigInterface
{
    private string $cryptoKey = '';
    private string $dbPath = '';
    private int $rateLimitMs = 500;

    public function getRateLimitMs(): int
    {
        return $this->rateLimitMs;
    }

    public function setRateLimitMs(int $ms): void
    {
        $this->rateLimitMs = $ms;
    }

    public function getCryptoKey(): string
    {
        if ($this->cryptoKey === '') {
            $this->cryptoKey = hash('sha256', 'AX18-12A.AaC4n7.@$%&@#_ParinaUltraSecret.', true);
        }
        return $this->cryptoKey;
    }

    public function getDbPath(): string
    {
        if ($this->dbPath === '') {
            $this->dbPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Db' . DIRECTORY_SEPARATOR . 'app.sqlite';
        }
        return $this->dbPath;
    }

    public function getTimeToLive(): int
    {
        return 60 * 30; // 30 minutes
    }

    public function getMaxRequestSize(): int
    {
        return 1024 * 1024 * 5; // 5 MB
    }

    public function allowSetup(): bool
    {
        return true;
    }

    public function getDbConfig(string $env = 'default'): array
    {
        $dbConfig = [
            'default' => [
                'dsn' => 'sqlite:' . $this->getDbPath(),
                'driver' => 'sqlite',
                'host' => '',
                'port' => '',
                'user' => '',
                'pass' => '',
                'params' => []
            ],
        ];
        return $dbConfig[$env];
    }
}

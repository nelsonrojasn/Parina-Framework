<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\AppConfig;

class ConfigTest extends TestCase
{
    private AppConfig $config;

    protected function setUp(): void
    {
        $this->config = new AppConfig();
    }

    public function test_get_crypto_key()
    {
        $key1 = $this->config->getCryptoKey();
        $key2 = $this->config->getCryptoKey();

        $this->assertNotEmpty($key1);
        $this->assertEquals($key1, $key2); // Debería persistir el valor en cache
    }

    public function test_get_db_path()
    {
        $path = $this->config->getDbPath();
        $this->assertNotEmpty($path);
        $this->assertStringContainsString('app.sqlite', $path);
    }

    public function test_get_time_to_live()
    {
        $ttl = $this->config->getTimeToLive();
        $this->assertGreaterThan(0, $ttl);
    }

    public function test_get_max_request_size()
    {
        $size = $this->config->getMaxRequestSize();
        $this->assertGreaterThan(0, $size);
    }

    public function test_allow_setup()
    {
        $this->assertTrue($this->config->allowSetup());
    }

    public function test_get_db_config()
    {
        $config = $this->config->getDbConfig('default');
        $this->assertArrayHasKey('dsn', $config);
        $this->assertArrayHasKey('driver', $config);
    }
}

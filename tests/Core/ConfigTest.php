<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\Config;

class ConfigTest extends TestCase
{
    public function test_get_crypto_key()
    {
        $key1 = Config::getCryptoKey();
        $key2 = Config::getCryptoKey();

        $this->assertNotEmpty($key1);
        $this->assertEquals($key1, $key2); // Debería persistir el valor en cache
    }

    public function test_get_db_path()
    {
        $path = Config::getDbPath();
        $this->assertNotEmpty($path);
        $this->assertStringContainsString('app.sqlite', $path);
    }

    public function test_get_time_to_live()
    {
        $ttl = Config::getTimeToLive();
        $this->assertGreaterThan(0, $ttl);
    }

    public function test_get_max_request_size()
    {
        $size = Config::getMaxRequestSize();
        $this->assertGreaterThan(0, $size);
    }

    public function test_allow_setup()
    {
        $this->assertTrue(Config::allowSetup());
    }

    public function test_get_db_config()
    {
        $config = Config::getDbConfig('default');
        $this->assertArrayHasKey('dsn', $config);
        $this->assertArrayHasKey('driver', $config);
    }
}

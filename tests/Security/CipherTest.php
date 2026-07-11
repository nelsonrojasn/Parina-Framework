<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Security\AesCipherService;
use Parina\Core\Interfaces\ConfigInterface;

class CipherTest extends TestCase
{
    private string $key = '12345678901234567890123456789012'; // 32 bytes key
    private AesCipherService $cipher;
    private $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->configMock->method('getTimeToLive')->willReturn(3600);
        $this->configMock->method('getCryptoKey')->willReturn($this->key);

        $this->cipher = new AesCipherService($this->configMock);
    }

    public function test_encrypt_and_decrypt()
    {
        $original = "Hello World!";
        $encrypted = $this->cipher->encrypt($original, $this->key);
        
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($original, $encrypted);

        $decrypted = $this->cipher->decrypt($encrypted, $this->key);
        $this->assertEquals($original, $decrypted);
    }

    public function test_decrypt_returns_empty_when_corrupted()
    {
        $original = "Hello World!";
        $encrypted = $this->cipher->encrypt($original, $this->key);
        
        // Modificar un carácter del ciphertext para corromper la firma HMAC
        $corrupted = substr($encrypted, 0, -2) . 'A';

        $decrypted = $this->cipher->decrypt($corrupted, $this->key);
        $this->assertEquals('', $decrypted);
    }

    public function test_decrypt_returns_empty_when_key_wrong()
    {
        $original = "Hello World!";
        $encrypted = $this->cipher->encrypt($original, $this->key);
        
        $wrongKey = 'wrong_key_1234567890123456789012';

        $decrypted = $this->cipher->decrypt($encrypted, $wrongKey);
        $this->assertEquals('', $decrypted);
    }

    public function test_url_encryption_and_decryption()
    {
        $action = 'user/edit';
        $encryptedUrl = $this->cipher->encryptUrl($action, name: 'John', id: 45);

        $this->assertNotEmpty($encryptedUrl);

        [$parsedAction, $params] = $this->cipher->parseUrlHash($encryptedUrl);

        $this->assertEquals($action, $parsedAction);
        $this->assertEquals('John', $params['name']);
        $this->assertEquals(45, $params['id']);
    }

    public function test_parse_url_hash_throws_exception_on_expired_ttl()
    {
        $action = 'user/edit';
        // Generar un hash temporal con TTL expirado alterando el _ttl interno
        // Para simular esto, encriptamos manualmente una query string con _ttl expirado
        $queryString = "action=" . urlencode($action) . "&_ttl=" . (time() - 100);
        $expiredHash = $this->cipher->encrypt($queryString, $this->key);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Security validation failed");

        $this->cipher->parseUrlHash($expiredHash);
    }

    public function test_parse_url_hash_throws_exception_on_missing_action()
    {
        $queryString = "_ttl=" . (time() + 100);
        $invalidHash = $this->cipher->encrypt($queryString, $this->key);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Security validation failed");

        $this->cipher->parseUrlHash($invalidHash);
    }
}

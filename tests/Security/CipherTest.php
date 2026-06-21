<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Security\Cipher;

class CipherTest extends TestCase
{
    private string $key = '12345678901234567890123456789012'; // 32 bytes key

    public function test_encrypt_and_decrypt()
    {
        $original = "Hello World!";
        $encrypted = Cipher::encrypt($original, $this->key);
        
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($original, $encrypted);

        $decrypted = Cipher::decrypt($encrypted, $this->key);
        $this->assertEquals($original, $decrypted);
    }

    public function test_decrypt_returns_empty_when_corrupted()
    {
        $original = "Hello World!";
        $encrypted = Cipher::encrypt($original, $this->key);
        
        // Modificar un carácter del ciphertext para corromper la firma HMAC
        $corrupted = substr($encrypted, 0, -2) . 'A';

        $decrypted = Cipher::decrypt($corrupted, $this->key);
        $this->assertEquals('', $decrypted);
    }

    public function test_decrypt_returns_empty_when_key_wrong()
    {
        $original = "Hello World!";
        $encrypted = Cipher::encrypt($original, $this->key);
        
        $wrongKey = 'wrong_key_1234567890123456789012';

        $decrypted = Cipher::decrypt($encrypted, $wrongKey);
        $this->assertEquals('', $decrypted);
    }

    public function test_url_encryption_and_decryption()
    {
        $action = 'user/edit';
        $encryptedUrl = Cipher::encryptUrl($action, name: 'John', id: 45);

        $this->assertNotEmpty($encryptedUrl);

        [$parsedAction, $params] = Cipher::parseUrlHash($encryptedUrl);

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
        $expiredHash = Cipher::encrypt($queryString, \Parina\Core\Config::getCryptoKey());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Security validation failed");

        Cipher::parseUrlHash($expiredHash);
    }

    public function test_parse_url_hash_throws_exception_on_missing_action()
    {
        $queryString = "_ttl=" . (time() + 100);
        $invalidHash = Cipher::encrypt($queryString, \Parina\Core\Config::getCryptoKey());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Security validation failed");

        Cipher::parseUrlHash($invalidHash);
    }
}

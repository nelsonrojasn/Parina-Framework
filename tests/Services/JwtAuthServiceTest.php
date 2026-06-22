<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\JwtAuth;

class JwtAuthServiceTest extends TestCase
{
    public function test_create_and_validate_token()
    {
        $payload = ['sub' => 123, 'username' => 'nelson'];

        $token = JwtAuth::createToken($payload);
        $this->assertNotEmpty($token);
        $this->assertCount(3, explode('.', $token));

        $decoded = JwtAuth::validateToken($token);

        $this->assertNotNull($decoded);
        $this->assertEquals(123, $decoded['sub']);
        $this->assertEquals('nelson', $decoded['username']);
    }

    public function test_validation_fails_on_tampered_signature()
    {
        $payload = ['sub' => 123];
        $token = JwtAuth::createToken($payload);

        // Alterar el último carácter del token garantizando que sea diferente
        $lastChar = substr($token, -1);
        $newChar = ($lastChar === 'A') ? 'B' : 'A';
        $tampered = substr($token, 0, -1) . $newChar;

        $decoded = JwtAuth::validateToken($tampered);
        $this->assertNull($decoded);
    }

    public function test_validation_fails_on_malformed_token()
    {
        $this->assertNull(JwtAuth::validateToken('invalid.token'));
        $this->assertNull(JwtAuth::validateToken('a.b.c.d'));
    }

    public function test_validation_fails_on_expired_token()
    {
        // Generar un payload manualmente con expiración en el pasado
        $payload = [
            'sub' => 123,
            'iat' => time() - 200,
            'exp' => time() - 100
        ];

        // Codificar a base64url manualmente el header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        // Firmar
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, \Parina\Core\Config::getCryptoKey(), true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $expiredToken = $base64Header . "." . $base64Payload . "." . $base64Signature;

        $decoded = JwtAuth::validateToken($expiredToken);
        $this->assertNull($decoded);
    }
}

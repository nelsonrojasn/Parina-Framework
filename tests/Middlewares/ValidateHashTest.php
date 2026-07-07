<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\ValidateHash;
use Parina\Shared\Security\CipherInterface;
use Parina\Core\Responses\NotFoundResponse;

class ValidateHashTest extends TestCase
{
    public function test_validate_hash_allows_valid_hash()
    {
        $cipherMock = $this->createMock(CipherInterface::class);
        $cipherMock->method('parseUrlHash')->with('valid_hash_value')->willReturn(['admin/home', ['id' => 42]]);

        $request = new Request(
            query: [],
            post: [],
            server: [],
            files: [],
            cookies: [],
            params: ['hash' => 'valid_hash_value']
        );

        $route = ['path' => '/admin/home/{hash}'];
        $middleware = new ValidateHash($cipherMock);
        $response = $middleware->handle($request, $route);

        $this->assertNull($response);
        $this->assertEquals(42, $request->param('id'));
        $this->assertEquals('admin/home', $request->param('_action'));
    }

    public function test_validate_hash_rejects_missing_hash()
    {
        $cipherMock = $this->createMock(CipherInterface::class);

        $request = new Request([], [], [], [], [], []);

        $middleware = new ValidateHash($cipherMock);
        $response = $middleware->handle($request);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }

    public function test_validate_hash_rejects_invalid_hash()
    {
        $cipherMock = $this->createMock(CipherInterface::class);
        $cipherMock->method('parseUrlHash')->with('invalid_hash_value')->willThrowException(new \Exception("Decryption failed"));

        $request = new Request(
            query: [],
            post: [],
            server: [],
            files: [],
            cookies: [],
            params: ['hash' => 'invalid_hash_value']
        );

        $middleware = new ValidateHash($cipherMock);
        $response = $middleware->handle($request);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }

    public function test_validate_hash_rejects_mismatched_action()
    {
        $cipherMock = $this->createMock(CipherInterface::class);
        $cipherMock->method('parseUrlHash')->with('mismatched_hash_value')->willReturn(['logout', []]);

        $request = new Request(
            query: [],
            post: [],
            server: [],
            files: [],
            cookies: [],
            params: ['hash' => 'mismatched_hash_value']
        );

        $route = ['path' => '/admin/home/{hash}'];
        $middleware = new ValidateHash($cipherMock);
        $response = $middleware->handle($request, $route);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }
}

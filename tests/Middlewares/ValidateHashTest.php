<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\ValidateHash;
use Parina\Shared\Security\Cipher;
use Parina\Core\Responses\NotFoundResponse;

class ValidateHashTest extends TestCase
{
    public function test_validate_hash_allows_valid_hash()
    {
        $encryptedHash = Cipher::encryptUrl('admin/home', id: 42);

        $request = new Request(
            query: [],
            post: [],
            server: [],
            files: [],
            cookies: [],
            params: ['hash' => $encryptedHash]
        );

        $route = ['path' => '/admin/home/{hash}'];
        $middleware = new ValidateHash();
        $response = $middleware->handle($request, $route);

        $this->assertNull($response);
        $this->assertEquals(42, $request->param('id'));
        $this->assertEquals('admin/home', $request->param('_action'));
    }

    public function test_validate_hash_rejects_missing_hash()
    {
        $request = new Request([], [], [], [], [], []);

        $middleware = new ValidateHash();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }

    public function test_validate_hash_rejects_invalid_hash()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [],
            files: [],
            cookies: [],
            params: ['hash' => 'invalid_hash_value']
        );

        $middleware = new ValidateHash();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }

    public function test_validate_hash_rejects_mismatched_action()
    {
        // Hash encrypted for 'logout' action
        $encryptedHash = Cipher::encryptUrl('logout');

        $request = new Request(
            query: [],
            post: [],
            server: [],
            files: [],
            cookies: [],
            params: ['hash' => $encryptedHash]
        );

        // Path is for 'admin/home' action
        $route = ['path' => '/admin/home/{hash}'];
        $middleware = new ValidateHash();
        $response = $middleware->handle($request, $route);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }
}

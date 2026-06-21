<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Core\HashResolver;
use Parina\Shared\Security\Cipher;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\PlainTextResponse;
use Parina\Core\Responses\NotFoundResponse;
use Parina\Core\Responses\RedirectResponse;

class HashResolverTest extends TestCase
{
    public function test_resolves_valid_encrypted_url()
    {
        // Encriptar una url válida para la acción 'hello' con un parámetro extra
        $encryptedHash = Cipher::encryptUrl('hello', name: 'Nelson');

        $request = new Request(
            query: ['r' => $encryptedHash],
            post: [],
            server: [],
            files: [],
            cookies: []
        );

        // Crear mock handler
        $dummyHandler = new class implements Handler {
            public function handle(Request $request): Response
            {
                $name = $request->param('name');
                $action = $request->param('_action');
                return new PlainTextResponse("Hello $name from $action");
            }
        };

        $resolver = new HashResolver([
            'hello' => get_class($dummyHandler)
        ]);

        $response = $resolver->handle($request);

        $this->assertInstanceOf(PlainTextResponse::class, $response);
        $this->assertEquals("Hello Nelson from hello", $response->getContent());
    }

    public function test_returns_not_found_when_r_missing()
    {
        $request = new Request([], [], [], [], []);
        $resolver = new HashResolver([]);

        $response = $resolver->handle($request);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }

    public function test_returns_not_found_when_action_unregistered()
    {
        $encryptedHash = Cipher::encryptUrl('unregistered_action');

        $request = new Request(
            query: ['r' => $encryptedHash],
            post: [],
            server: [],
            files: [],
            cookies: []
        );

        $resolver = new HashResolver([]);

        $response = $resolver->handle($request);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
    }

    public function test_redirects_on_invalid_or_expired_hash()
    {
        $request = new Request(
            query: ['r' => 'invalid_hash_value'],
            post: [],
            server: [],
            files: [],
            cookies: []
        );

        $resolver = new HashResolver([]);

        $response = $resolver->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getHeaders()['Location']);
    }
}

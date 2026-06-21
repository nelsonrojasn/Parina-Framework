<?php

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Shared\Middlewares\SameOrigin;
use Parina\Core\Responses\ErrorResponse;

class SameOriginTest extends TestCase
{
    public function test_same_origin_allows_request_when_no_origin_header()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [
                'HTTP_HOST' => 'localhost'
            ],
            files: [],
            cookies: []
        );

        $middleware = new SameOrigin();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_same_origin_allows_request_when_origin_matches_host()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [
                'HTTP_HOST' => 'localhost',
                'HTTP_ORIGIN' => 'http://localhost'
            ],
            files: [],
            cookies: []
        );

        $middleware = new SameOrigin();
        $response = $middleware->handle($request);

        $this->assertNull($response);
    }

    public function test_same_origin_blocks_request_when_origin_does_not_match_host()
    {
        $request = new Request(
            query: [],
            post: [],
            server: [
                'HTTP_HOST' => 'localhost',
                'HTTP_ORIGIN' => 'http://malicious-site.com'
            ],
            files: [],
            cookies: []
        );

        $middleware = new SameOrigin();
        $response = $middleware->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals("Forbidden (same-origin)", $response->getContent());
    }
}

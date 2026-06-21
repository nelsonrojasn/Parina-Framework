<?php

namespace Tests\Responses;

use PHPUnit\Framework\TestCase;
use Parina\Core\Responses\JsonResponse;
use Parina\Core\Responses\RedirectResponse;
use Parina\Core\Responses\ErrorResponse;
use Parina\Core\Responses\BasicRealmResponse;
use Parina\Core\Responses\ForbiddenResponse;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Responses\NotFoundResponse;
use Parina\Core\Responses\PlainTextResponse;
use Parina\Core\Responses\UnauthorizedResponse;

class ResponseTest extends TestCase
{
    public function test_json_response()
    {
        $data = json_encode(['foo' => 'bar']);
        $response = new JsonResponse($data, 201);

        $this->assertEquals($data, $response->getContent());
        $this->assertEquals(201, $response->getStatus());
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type']);
    }

    public function test_redirect_response()
    {
        $response = new RedirectResponse('/home', 301);

        $this->assertEquals('', $response->getContent());
        $this->assertEquals(301, $response->getStatus());
        $this->assertEquals('/home', $response->getHeaders()['Location']);
    }

    public function test_error_response()
    {
        $errorMessage = 'Internal Server Error';
        $response = new ErrorResponse($errorMessage, 500);

        $this->assertEquals($errorMessage, $response->getContent());
        $this->assertEquals(500, $response->getStatus());
        $this->assertEquals('text/html; charset=UTF-8', $response->getHeaders()['Content-Type']);
    }

    public function test_basic_realm_response()
    {
        $response = new BasicRealmResponse('Unauthorized');
        $this->assertEquals('Unauthorized', $response->getContent());
        $this->assertEquals(401, $response->getStatus());
        $this->assertEquals('Basic realm="Parina Control Panel"', $response->getHeaders()['WWW-Authenticate']);
        $this->assertEquals('text/html; charset=UTF-8', $response->getHeaders()['Content-Type']);
    }

    public function test_forbidden_response()
    {
        $response = new ForbiddenResponse();
        $this->assertStringContainsString('403 Forbidden', $response->getContent());
        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals('text/html; charset=UTF-8', $response->getHeaders()['Content-Type']);
    }

    public function test_html_response()
    {
        $response = new HtmlResponse('<h1>Hello</h1>', 200);
        $this->assertEquals('<h1>Hello</h1>', $response->getContent());
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('text/html; charset=UTF-8', $response->getHeaders()['Content-Type']);
    }

    public function test_not_found_response()
    {
        $response = new NotFoundResponse();
        $this->assertStringContainsString('404 Not Found', $response->getContent());
        $this->assertEquals(404, $response->getStatus());
        $this->assertEquals('text/html; charset=UTF-8', $response->getHeaders()['Content-Type']);
    }

    public function test_plain_text_response()
    {
        $response = new PlainTextResponse('Hello World');
        $this->assertEquals('Hello World', $response->getContent());
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('text/plain; charset=UTF-8', $response->getHeaders()['Content-Type']);
    }

    public function test_unauthorized_response()
    {
        $response = new UnauthorizedResponse();
        $this->assertStringContainsString('401 Unauthorized', $response->getContent());
        $this->assertEquals(401, $response->getStatus());
        $this->assertEquals('text/html; charset=UTF-8', $response->getHeaders()['Content-Type']);
    }
}

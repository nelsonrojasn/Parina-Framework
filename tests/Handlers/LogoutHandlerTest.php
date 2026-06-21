<?php

namespace Tests\Handlers;

use PHPUnit\Framework\TestCase;
use Parina\Core\Request;
use Parina\Modules\Private\LogoutHandler;
use Parina\Core\Responses\RedirectResponse;
use Parina\Core\Session;

class LogoutHandlerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function test_handler_returns_valid_response()
    {
        $_SESSION = ['user_id' => 123];

        $handler = new LogoutHandler();
        $request = new Request([], [], [], [], []);

        $response = $handler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatus());
        $this->assertEquals('/', $response->getHeaders()['Location']);
    }
}

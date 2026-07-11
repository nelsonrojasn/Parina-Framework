<?php
declare(strict_types=1);
namespace Parina\Core;

use Parina\Core\Interfaces\Response;

class ResponseEmitter
{
    public function emit(Response $response): void
    {
        if (headers_sent()) {
            return;
        }

        // Send Status Code
        http_response_code($response->getStatus());

        // Send Headers
        foreach ($response->getHeaders() as $name => $value) {
            header("$name: $value");
        }

        // Send body
        echo $response->getContent();
    }
}

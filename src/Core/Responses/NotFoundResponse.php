<?php

namespace Parina\Core\Responses;

use Parina\Core\Interfaces\Response;

class NotFoundResponse implements Response
{
    private string $content;
    private int $status;
    private array $headers = [];

    public function __construct(string $message = "<h1>404 Not Found</h1><p>The requested resource could not be found.</p>", int $status = 404)
    {
        $this->content = $message;
        $this->status = $status;
        $this->headers['Content-Type'] = 'text/html; charset=UTF-8';
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
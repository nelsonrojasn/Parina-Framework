<?php

namespace Parina\Core\Responses;

use Parina\Core\Interfaces\Response;

class ForbiddenResponse implements Response
{
    private string $content;
    private int $status;
    private array $headers = [];

    public function __construct(string $message = "<h1>403 Forbidden</h1><p>You do not have permission to access this resource.</p>", int $status = 403)
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
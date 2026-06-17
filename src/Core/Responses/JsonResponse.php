<?php

namespace Parina\Core\Responses;

use Parina\Core\Interfaces\Response;

class JsonResponse implements Response
{
    private string $content;
    private int $status;
    private array $headers = [];

    public function __construct(string $content, int $status = 200)
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers['Content-Type'] = 'application/json';
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
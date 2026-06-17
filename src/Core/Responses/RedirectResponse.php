<?php

namespace Parina\Core\Responses;

use Parina\Core\Interfaces\Response;

class RedirectResponse implements Response
{
    private string $content;
    private int $status;
    private array $headers = [];

    public function __construct(string $url, int $status = 302)
    {
        $this->content = "";
        $this->status = $status;
        $this->headers['Location'] = $url;
    }
    public function getContent(): string
    {
        return "";
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
<?php

namespace Parina\Core\Responses;

use Parina\Core\Interfaces\Response;

class HtmlResponse implements Response
{
    private string $content;
    private int $status;
    private array $headers = []; 

    public function __construct(string $content, int $status = 200)
    {
        $this->content = $content;
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
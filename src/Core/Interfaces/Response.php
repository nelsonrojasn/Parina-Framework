<?php
namespace Parina\Core\Interfaces;

interface Response
{
    public function __construct(string $content, int $status);

    public function getStatus(): int;
    public function getContent(): string;
    public function getHeaders(): array;

}

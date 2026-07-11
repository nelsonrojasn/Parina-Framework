<?php
declare(strict_types=1);
namespace Parina\Core\Interfaces;

interface Response
{
    public function getStatus(): int;
    public function getContent(): string;
    public function getHeaders(): array;
}

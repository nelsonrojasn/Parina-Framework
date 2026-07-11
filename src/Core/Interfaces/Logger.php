<?php
declare(strict_types=1);
namespace Parina\Core\Interfaces;

interface Logger {
    public function log(string $message): void;
}
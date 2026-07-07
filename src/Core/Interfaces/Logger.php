<?php

namespace Parina\Core\Interfaces;

interface Logger {
    public function log(string $message): void;
}
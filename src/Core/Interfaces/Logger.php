<?php

namespace Parina\Core\Interfaces;

interface Logger {
    public static function log(string $message): void;
}
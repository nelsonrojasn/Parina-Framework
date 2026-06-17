<?php

namespace Parina\Core;

use Parina\Core\Interfaces\Logger;

class FileLogger implements Logger
{
    public static function log(string $message): void
    {
        error_log($message);    
    }
    
}
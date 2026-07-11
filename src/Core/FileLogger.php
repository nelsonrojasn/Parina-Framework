<?php
declare(strict_types=1);
namespace Parina\Core;

use Parina\Core\Interfaces\Logger;

class FileLogger implements Logger
{
    public function log(string $message): void
    {
        error_log($message);    
    }
}
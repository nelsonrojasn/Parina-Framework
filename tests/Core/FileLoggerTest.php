<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\FileLogger;

class FileLoggerTest extends TestCase
{
    public function test_log()
    {
        // Validamos que se ejecute sin lanzar excepciones.
        $this->expectNotToPerformAssertions();
        FileLogger::log("Test log message from unit tests");
    }
}

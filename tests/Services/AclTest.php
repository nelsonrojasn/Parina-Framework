<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\Acl;
use Parina\Core\Interfaces\Logger;

class AclTest extends TestCase
{
    public function test_acl_has_permissions()
    {
        $loggerMock = $this->createMock(Logger::class);
        $loggerMock->expects($this->once())
            ->method('log')
            ->with($this->stringContains("Checking permissions for action: test_action"));

        $acl = new Acl($loggerMock);
        $result = $acl->hasPermissions("test_action");

        $this->assertTrue($result);
    }
}

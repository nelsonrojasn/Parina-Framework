<?php

namespace Parina\Shared\Services;

use Parina\Shared\Infrastructure\DB;
use Parina\Core\Config;
use Parina\Core\FileLogger;

class Acl
{
    private static ?bool $mockHasPermissions = null;

    public static function setMockHasPermissions(?bool $value): void
    {
        self::$mockHasPermissions = $value;
    }

    public static function hasPermissions(string $action):bool
    {
        if (self::$mockHasPermissions !== null) {
            return self::$mockHasPermissions;
        }

        FileLogger::log("Checking permissions for action: $action");
        return true;
    }
}
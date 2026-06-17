<?php

namespace Parina\Shared\Services;

use Parina\Shared\Infrastructure\DB;
use Parina\Core\Config;
use Parina\Core\FileLogger;

class Acl
{
    public static function hasPermissions(string $action):bool
    {
        FileLogger::log("Checking permissions for action: $action");
        return true;
    }
}
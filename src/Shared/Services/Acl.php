<?php

namespace Parina\Shared\Services;

use Parina\Shared\Infrastructure\DB;
use Parina\Core\Config;

class Acl
{
    public static function hasPermissions(string $action):bool
    {
        return true;
    }
}
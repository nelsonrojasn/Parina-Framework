<?php

namespace Parina\Shared\Services;

interface AclInterface
{
    public function hasPermissions(string $action): bool;
}

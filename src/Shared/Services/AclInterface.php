<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

interface AclInterface
{
    public function hasPermissions(string $action): bool;
}

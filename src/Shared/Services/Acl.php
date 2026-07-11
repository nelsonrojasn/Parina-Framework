<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

use Parina\Core\Interfaces\Logger;

class Acl implements AclInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function hasPermissions(string $action): bool
    {
        $this->logger->log("Checking permissions for action: $action");
        return true;
    }
}
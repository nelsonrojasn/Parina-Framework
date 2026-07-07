<?php

namespace Parina\Shared\Services;

use Parina\Core\Interfaces\Logger;

class Acl implements AclInterface
{
    private Logger $logger;

    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? new \Parina\Core\FileLogger();
    }

    public function hasPermissions(string $action): bool
    {
        $this->logger->log("Checking permissions for action: $action");
        return true;
    }

    /**
     * Facade static call delegation for backward compatibility
     */
    public static function __callStatic(string $name, array $arguments)
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return call_user_func_array([$instance, $name], $arguments);
    }
}
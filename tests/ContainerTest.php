<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Parina\Core\Container;

class ContainerTest extends TestCase
{
    public function test_container_resolves_all_bindings()
    {
        $container = new Container();
        $dependenciesFile = dirname(__DIR__) . '/config/dependencies.php';
        
        $this->assertFileExists($dependenciesFile);
        $config = require $dependenciesFile;
        $container->load($config);

        // Try resolving each registered dependency to make sure they auto-wire successfully
        $singletons = $config['singletons'] ?? [];
        foreach ($singletons as $interface => $concrete) {
            $instance = $container->get($interface);
            $this->assertNotNull($instance, "Failed to resolve {$interface}");
            $this->assertInstanceOf($interface, $instance, "Resolved instance is not of type {$interface}");
        }
    }
}

<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\Container;
use Exception;

// Stub classes for testing
class StubNoConstructor {}

class StubDependency {}

class StubWithConstructorDependency {
    public StubDependency $dependency;
    public function __construct(StubDependency $dependency) {
        $this->dependency = $dependency;
    }
}

interface StubInterface {}

class StubImplementation implements StubInterface {}

class StubWithDefaultValue {
    public string $value;
    public function __construct(string $value = 'default_val') {
        $this->value = $value;
    }
}

class StubWithPrimitiveNoDefault {
    public string $value;
    public function __construct(string $value) {
        $this->value = $value;
    }
}

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function testResolvesClassWithoutConstructor()
    {
        $instance = $this->container->get(StubNoConstructor::class);
        $this->assertInstanceOf(StubNoConstructor::class, $instance);
    }

    public function testResolvesConstructorDependencyRecursively()
    {
        $instance = $this->container->get(StubWithConstructorDependency::class);
        $this->assertInstanceOf(StubWithConstructorDependency::class, $instance);
        $this->assertInstanceOf(StubDependency::class, $instance->dependency);
    }

    public function testResolvesInterfaceToConcreteBinding()
    {
        $this->container->bind(StubInterface::class, StubImplementation::class);
        $instance = $this->container->get(StubInterface::class);
        $this->assertInstanceOf(StubImplementation::class, $instance);
    }

    public function testResolvesSingletonInstance()
    {
        $this->container->singleton(StubNoConstructor::class);
        
        $instance1 = $this->container->get(StubNoConstructor::class);
        $instance2 = $this->container->get(StubNoConstructor::class);
        
        $this->assertSame($instance1, $instance2);
    }

    public function testResolvesPrimitiveParameterWithDefaultValue()
    {
        $instance = $this->container->get(StubWithDefaultValue::class);
        $this->assertInstanceOf(StubWithDefaultValue::class, $instance);
        $this->assertEquals('default_val', $instance->value);
    }

    public function testThrowsExceptionOnUnresolvedPrimitiveParameter()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No se puede resolver el parámetro primitivo 'value'");
        
        $this->container->get(StubWithPrimitiveNoDefault::class);
    }

    public function testThrowsExceptionOnNonInstantiableClass()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La clase o interfaz " . StubInterface::class . " no es instanciable");
        
        // Try to resolve interface without binding it
        $this->container->get(StubInterface::class);
    }

    public function testResolvesFactoryClosure()
    {
        $this->container->bind(StubInterface::class, function () {
            return new StubImplementation();
        });
        
        $instance = $this->container->get(StubInterface::class);
        $this->assertInstanceOf(StubImplementation::class, $instance);
    }
}

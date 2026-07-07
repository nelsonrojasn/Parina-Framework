<?php

namespace Parina\Core;

use ReflectionClass;
use Exception;
use ReflectionNamedType;

class Container
{
    // Resolved singleton instances
    private array $instances = [];

    // Mappings of interfaces/abstracts to concrete class names or factory callables
    private array $bindings = [];

    /**
     * Bind an interface or class name to a concrete implementation or a factory.
     */
    public function bind(string $interface, mixed $concrete): void
    {
        $this->bindings[$interface] = $concrete;
    }

    /**
     * Register a singleton instance or concrete class to be treated as a singleton.
     */
    public function singleton(string $class, mixed $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $class;
        }
        $this->bindings[$class] = $concrete;
        // Mark as a singleton so we cache it after the first resolution
        $this->instances[$class] = null;
    }

    /**
     * Resolve a class dependency.
     */
    public function get(string $className)
    {
        // 1. If we already have a resolved singleton instance, return it
        if (isset($this->instances[$className]) && $this->instances[$className] !== null) {
            return $this->instances[$className];
        }

        // 2. Resolve mapped binding
        $concrete = $this->bindings[$className] ?? $className;

        // If the mapped binding resolves to a pre-existing object (and is not a factory closure), return it
        if (is_object($concrete) && !($concrete instanceof \Closure)) {
            return $concrete;
        }

        // If the mapped binding is a factory callable, execute it
        if (is_callable($concrete)) {
            $instance = $concrete($this);
            if (array_key_exists($className, $this->instances)) {
                $this->instances[$className] = $instance;
            }
            return $instance;
        }

        // If the concrete class already has a resolved singleton, return it
        if (isset($this->instances[$concrete]) && $this->instances[$concrete] !== null) {
            return $this->instances[$concrete];
        }

        // 3. Build the concrete instance
        $instance = $this->build($concrete);

        // 4. Cache the instance if registered as a singleton
        if (array_key_exists($className, $this->instances)) {
            $this->instances[$className] = $instance;
        }
        if (array_key_exists($concrete, $this->instances)) {
            $this->instances[$concrete] = $instance;
        }

        return $instance;
    }

    /**
     * Build the concrete instance of a class.
     */
    private function build(string $concrete)
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("La clase o interfaz {$concrete} no es instanciable.");
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return new $concrete();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $concrete);
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve dependencies for a constructor.
     */
    private function resolveDependencies(array $parameters, string $concrete): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                // Recursively resolve dependency
                $dependencies[] = $this->get($type->getName());
            } else {
                // If it has a default value, use it
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception("No se puede resolver el parámetro primitivo '{$parameter->getName()}' en {$concrete}");
                }
            }
        }

        return $dependencies;
    }

    /**
     * Load bindings and singletons configuration.
     */
    public function load(array $config): self
    {
        foreach ($config['bindings'] ?? [] as $interface => $concrete) {
            $this->bind($interface, $concrete);
        }

        foreach ($config['singletons'] ?? [] as $class => $concrete) {
            $this->singleton($class, $concrete);
        }

        return $this;
    }

    /**
     * Check if a class or binding is registered.
     */
    public function has(string $className): bool
    {
        return isset($this->bindings[$className]) || isset($this->instances[$className]) || class_exists($className);
    }
}

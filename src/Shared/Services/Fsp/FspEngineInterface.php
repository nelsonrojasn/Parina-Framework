<?php

namespace Parina\Shared\Services\Fsp;

/**
 * Interface FspEngineInterface
 * 
 * Contract for the high-performance prefix-notation sandbox rules engine.
 */
interface FspEngineInterface
{
    /**
     * Compiles raw DSL source code into a flat bytecode instruction array.
     *
     * @param string $source The raw rule engine source code.
     * @return array Pre-compiled instructions (bytecode) ready for execution.
     */
    public function compile(string $source): array;

    /**
     * Executes pre-compiled bytecode instructions within a secure sandbox environment.
     *
     * @param array $instructions Pre-compiled instruction set (bytecode).
     * @param array $params Input parameters injected from external context.
     * @return array Computed result values.
     */
    public function execute(array $instructions, array $params = []): array;
}

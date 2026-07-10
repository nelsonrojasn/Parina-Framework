<?php

namespace Tests\Shared\Services;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Services\Fps\FpsEngine;

class FpsEngineTest extends TestCase
{
    private FpsEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new FpsEngine();
    }

    /**
     * Test basic rules execution and variable mapping.
     */
    public function test_compilation_and_execution_basics()
    {
        $source = <<<DSL
begin
    subtotal = 1000
    tax = *(subtotal, 0.19)
    total = +(subtotal, tax)
    result << tax, total
end
DSL;

        $bytecode = $this->engine->compile($source);
        $result = $this->engine->execute($bytecode);

        $this->assertEquals(190, $result['tax']);
        $this->assertEquals(1190, $result['total']);
    }

    /**
     * Test that conditional branches (if, else, end) execute the correct paths.
     */
    public function test_nested_if_else_branching()
    {
        $source = <<<DSL
begin
    score = 85
    if >=(score, 90)
        grade = "A"
    else
        if >=(score, 80)
            grade = "B"
        else
            grade = "C"
        end
    end
    result << grade
end
DSL;

        $bytecode = $this->engine->compile($source);
        $result = $this->engine->execute($bytecode);

        $this->assertEquals("B", $result['grade']);
    }

    /**
     * Test that comments within double-quoted strings are preserved.
     */
    public function test_comment_stripping_retains_hashes_in_strings()
    {
        $source = <<<DSL
begin
    # Hash character inside a string literal
    message = "Report #42 is ready"
    result << message
end
DSL;

        $bytecode = $this->engine->compile($source);
        $result = $this->engine->execute($bytecode);

        $this->assertEquals("Report #42 is ready", $result['message']);
    }

    /**
     * Test that string literals containing commas are evaluated correctly inside function calls.
     */
    public function test_comma_splitting_in_string_arguments()
    {
        $source = <<<DSL
begin
    name = "Rojas, Nelson"
    match = ==(name, "Rojas, Nelson")
    result << match
end
DSL;

        $bytecode = $this->engine->compile($source);
        $result = $this->engine->execute($bytecode);

        $this->assertTrue($result['match']);
    }

    /**
     * Test that string literals containing parentheses do not break parsing.
     */
    public function test_unbalanced_parentheses_in_string()
    {
        $source = <<<DSL
begin
    details = "Name (Extra)"
    match = ==(details, "Name (Extra)")
    result << match
end
DSL;

        $bytecode = $this->engine->compile($source);
        $result = $this->engine->execute($bytecode);

        $this->assertTrue($result['match']);
    }

    /**
     * Test compatibility with original behavior where undefined variables return their name string.
     */
    public function test_undefined_variable_fallback()
    {
        $source = <<<DSL
begin
    val = undefined_var
    result << val
end
DSL;

        $bytecode = $this->engine->compile($source);
        $result = $this->engine->execute($bytecode);

        $this->assertEquals("undefined_var", $result['val']);
    }
}

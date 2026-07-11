<?php

namespace Parina\Shared\Services\Fsp;

/**
 * Class FspCompiler
 * 
 * Compiles raw FSP Rules DSL source code into a flat, jump-resolved bytecode array.
 */
class FspCompiler
{
    /**
     * @var array Flat list of compiled instructions.
     */
    private array $instructions = [];

    /**
     * @var array Stack for resolving jump offsets.
     */
    private array $jmpStack = [];

    /**
     * @var array Token list for the current expression.
     */
    private array $tokens = [];

    /**
     * @var int Pointer within the current token stream.
     */
    private int $tokenIndex = 0;

    /**
     * Compiles raw DSL source code into a flat array of instructions (bytecode).
     *
     * @param string $source Raw rule script source.
     * @return array Pre-compiled instructions with jump offsets resolved.
     */
    public function compile(string $source): array
    {
        $this->instructions = [];
        $this->jmpStack = [];

        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $source));

        foreach ($lines as $lineNo => $rawLine) {
            $line = $this->stripComments($rawLine);

            $matched = false;
            foreach ($this->getLineParsers() as $parser) {
                if ($this->$parser($line)) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $this->instructions[] = ['type' => 'noop'];
            }
        }

        return $this->instructions;
    }

    /**
     * Strips comments safely, preserving '#' characters inside double-quoted string literals.
     */
    private function stripComments(string $rawLine): string
    {
        $line = '';
        $inString = false;
        $len = strlen($rawLine);
        for ($i = 0; $i < $len; $i++) {
            $char = $rawLine[$i];
            if ($char === '"') {
                $inString = !$inString;
            }
            if ($char === '#' && !$inString) {
                break;
            }
            $line .= $char;
        }
        return trim($line);
    }

    /**
     * Get the registry of line parser method names.
     *
     * @return array<string>
     */
    private function getLineParsers(): array
    {
        return [
            'parseNoop',
            'parseEnd',
            'parseIf',
            'parseElse',
            'parseResult',
            'parseAssign',
        ];
    }

    private function parseNoop(string $line): bool
    {
        if (empty($line) || str_starts_with(strtolower($line), 'formula') || strtolower($line) === 'begin') {
            $this->instructions[] = ['type' => 'noop'];
            return true;
        }
        return false;
    }

    private function parseEnd(string $line): bool
    {
        if (strtolower($line) !== 'end') {
            return false;
        }
        if (!empty($this->jmpStack)) {
            $pop = array_pop($this->jmpStack);
            $this->instructions[$pop['idx']]['jmp_target'] = count($this->instructions) + 1;
            $this->instructions[] = ['type' => 'noop'];
        } else {
            $this->instructions[] = ['type' => 'noop'];
        }
        return true;
    }

    private function parseIf(string $line): bool
    {
        if (!str_starts_with($line, 'if ')) {
            return false;
        }
        $condStr = trim(substr($line, 3));
        $this->tokens = $this->tokenize($condStr);
        $this->tokenIndex = 0;
        $expr = $this->parseExpression();
        
        $instIdx = count($this->instructions);
        $this->instructions[] = [
            'type' => 'if',
            'expr' => $expr,
            'jmp_target' => null
        ];
        $this->jmpStack[] = ['type' => 'if', 'idx' => $instIdx];
        return true;
    }

    private function parseElse(string $line): bool
    {
        if ($line !== 'else') {
            return false;
        }
        $pop = array_pop($this->jmpStack);
        if ($pop && $pop['type'] === 'if') {
            $this->instructions[$pop['idx']]['jmp_target'] = count($this->instructions) + 1;
        }
        
        $instIdx = count($this->instructions);
        $this->instructions[] = [
            'type' => 'jmp',
            'jmp_target' => null
        ];
        $this->jmpStack[] = ['type' => 'else', 'idx' => $instIdx];
        return true;
    }

    private function parseResult(string $line): bool
    {
        if (!preg_match('/^result\s*(?:<<|=)\s*(.*)$/i', $line, $matches)) {
            return false;
        }
        $vars = array_map('trim', explode(',', $matches[1]));
        $this->instructions[] = [
            'type' => 'result',
            'vars' => $vars
        ];
        return true;
    }

    private function parseAssign(string $line): bool
    {
        if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
            return false;
        }
        $this->tokens = $this->tokenize(trim($matches[2]));
        $this->tokenIndex = 0;
        $expr = $this->parseExpression();
        $this->instructions[] = [
            'type' => 'assign',
            'var' => $matches[1],
            'expr' => $expr
        ];
        return true;
    }

    /**
     * Tokenizes an expression string, respecting strings containing quotes, commas, and parentheses.
     *
     * @param string $expr The raw expression string.
     * @return array List of token arrays.
     */
    private function tokenize(string $expr): array
    {
        $patterns = [
            'PARAM'      => '/^param\.([a-zA-Z_][a-zA-Z0-9_]*)/i',
            'BOOL'       => '/^(true|false)\b/i',
            'NUMBER'     => '/^(\d+(?:\.\d+)?)\b/',
            'STRING'     => '/^"([^"]*)"/',
            'OP'         => '/^(==|~=|<=|>=|&&|\|\||[+\-*\/%<>])/',
            'LPAREN'     => '/^(\()/',
            'RPAREN'     => '/^(\))/',
            'COMMA'      => '/^(,)/',
            'IDENTIFIER' => '/^([a-zA-Z_][a-zA-Z0-9_]*)/',
        ];

        $tokens = [];
        $expr = trim($expr);
        while ($expr !== '') {
            $matched = false;
            foreach ($patterns as $type => $pattern) {
                if (preg_match($pattern, $expr, $m)) {
                    $val = $m[1];
                    $tokens[] = $this->buildToken($type, $val);
                    $expr = ltrim(substr($expr, strlen($m[0])));
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $expr = ltrim(substr($expr, 1));
            }
        }
        return $tokens;
    }

    /**
     * Helper to construct type-casted tokens.
     */
    private function buildToken(string $type, string $val): array
    {
        if ($type === 'NUMBER') {
            return ['type' => 'NUMBER', 'value' => str_contains($val, '.') ? (float)$val : (int)$val];
        }
        if ($type === 'BOOL') {
            return ['type' => 'BOOL', 'value' => strtolower($val) === 'true'];
        }
        return ['type' => $type, 'value' => $val];
    }

    /**
     * Parses tokens recursively to produce a structured array AST node.
     *
     * @return array Parsed expression node.
     */
    private function parseExpression(): array
    {
        if (!isset($this->tokens[$this->tokenIndex])) {
            return ['type' => 'const', 'value' => null];
        }
        $token = $this->tokens[$this->tokenIndex];
        if ($token['type'] === 'STRING' || $token['type'] === 'NUMBER' || $token['type'] === 'BOOL') {
            $this->tokenIndex++;
            return ['type' => 'const', 'value' => $token['value']];
        }
        if ($token['type'] === 'PARAM') {
            $this->tokenIndex++;
            return ['type' => 'param', 'key' => $token['value']];
        }
        
        // Function or operator call notation: OP(arg1, arg2)
        if (($token['type'] === 'OP' || $token['type'] === 'IDENTIFIER') 
            && isset($this->tokens[$this->tokenIndex + 1]) 
            && $this->tokens[$this->tokenIndex + 1]['type'] === 'LPAREN'
        ) {
            return $this->parseFunctionCall($token['value']);
        }

        if ($token['type'] === 'IDENTIFIER') {
            $this->tokenIndex++;
            return ['type' => 'var', 'name' => $token['value']];
        }

        $this->tokenIndex++;
        return ['type' => 'const', 'value' => $token['value']];
    }

    /**
     * Parses a function/operator call, extracting arguments and skipping parentheses.
     */
    private function parseFunctionCall(string $op): array
    {
        $this->tokenIndex += 2; // Skip OP/IDENTIFIER and LPAREN
        
        $args = [];
        while (isset($this->tokens[$this->tokenIndex]) && $this->tokens[$this->tokenIndex]['type'] !== 'RPAREN') {
            $args[] = $this->parseExpression();
            if (isset($this->tokens[$this->tokenIndex]) && $this->tokens[$this->tokenIndex]['type'] === 'COMMA') {
                $this->tokenIndex++; // Skip COMMA
            }
        }
        if (isset($this->tokens[$this->tokenIndex]) && $this->tokens[$this->tokenIndex]['type'] === 'RPAREN') {
            $this->tokenIndex++;
        }
        return ['type' => 'op', 'op' => $op, 'args' => $args];
    }
}

<?php

namespace Parina\Shared\Services\Fps;

/**
 * Class FpsCompiler
 * 
 * Compiles raw FPS Rules DSL source code into a flat, jump-resolved bytecode array.
 */
class FpsCompiler
{
    /**
     * Compiles raw DSL source code into a flat array of instructions (bytecode).
     *
     * @param string $source Raw rule script source.
     * @return array Pre-compiled instructions with jump offsets resolved.
     */
    public function compile(string $source): array
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $source));
        $instructions = [];
        $jmpStack = [];

        foreach ($lines as $lineNo => $rawLine) {
            // Strip comments safely, preserving '#' characters inside double-quoted string literals.
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
            $line = trim($line);

            if (empty($line) || str_starts_with(strtolower($line), 'formula') || strtolower($line) === 'begin') {
                $instructions[] = ['type' => 'noop'];
                continue;
            }

            if (strtolower($line) === 'end') {
                if (!empty($jmpStack)) {
                    $pop = array_pop($jmpStack);
                    $instructions[$pop['idx']]['jmp_target'] = count($instructions) + 1;
                    $instructions[] = ['type' => 'noop'];
                } else {
                    $instructions[] = ['type' => 'noop'];
                }
                continue;
            }

            if (str_starts_with($line, 'if ')) {
                $condStr = trim(substr($line, 3));
                $tokens = $this->tokenize($condStr);
                $idx = 0;
                $expr = $this->parseExpression($tokens, $idx);
                
                $instIdx = count($instructions);
                $instructions[] = [
                    'type' => 'if',
                    'expr' => $expr,
                    'jmp_target' => null
                ];
                $jmpStack[] = ['type' => 'if', 'idx' => $instIdx];
                continue;
            }

            if ($line === 'else') {
                $pop = array_pop($jmpStack);
                if ($pop && $pop['type'] === 'if') {
                    $instructions[$pop['idx']]['jmp_target'] = count($instructions) + 1;
                }
                
                $instIdx = count($instructions);
                $instructions[] = [
                    'type' => 'jmp',
                    'jmp_target' => null
                ];
                $jmpStack[] = ['type' => 'else', 'idx' => $instIdx];
                continue;
            }

            if (preg_match('/^result\s*(?:<<|=)\s*(.*)$/i', $line, $matches)) {
                $vars = array_map('trim', explode(',', $matches[1]));
                $instructions[] = [
                    'type' => 'result',
                    'vars' => $vars
                ];
                continue;
            }

            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
                $tokens = $this->tokenize(trim($matches[2]));
                $idx = 0;
                $expr = $this->parseExpression($tokens, $idx);
                $instructions[] = [
                    'type' => 'assign',
                    'var' => $matches[1],
                    'expr' => $expr
                ];
                continue;
            }

            $instructions[] = ['type' => 'noop'];
        }

        return $instructions;
    }

    /**
     * Tokenizes an expression string, respecting strings containing quotes, commas, and parentheses.
     *
     * @param string $expr The raw expression string.
     * @return array List of token arrays.
     */
    private function tokenize(string $expr): array
    {
        $tokens = [];
        $len = strlen($expr);
        $i = 0;
        while ($i < $len) {
            $char = $expr[$i];
            if (ctype_space($char)) {
                $i++;
                continue;
            }
            if ($char === '(') {
                $tokens[] = ['type' => 'LPAREN', 'value' => '('];
                $i++;
                continue;
            }
            if ($char === ')') {
                $tokens[] = ['type' => 'RPAREN', 'value' => ')'];
                $i++;
                continue;
            }
            if ($char === ',') {
                $tokens[] = ['type' => 'COMMA', 'value' => ','];
                $i++;
                continue;
            }
            if ($char === '"') {
                $start = $i + 1;
                $i++;
                while ($i < $len && $expr[$i] !== '"') {
                    $i++;
                }
                $value = substr($expr, $start, $i - $start);
                $tokens[] = ['type' => 'STRING', 'value' => $value];
                $i++; // Skip closing quote
                continue;
            }
            
            // Match two-character operators.
            $twoChars = substr($expr, $i, 2);
            if (in_array($twoChars, ['==', '~=', '<=', '>=', '&&', '||'])) {
                $tokens[] = ['type' => 'OP', 'value' => $twoChars];
                $i += 2;
                continue;
            }
            if (in_array($char, ['+', '-', '*', '/', '%', '<', '>'])) {
                $tokens[] = ['type' => 'OP', 'value' => $char];
                $i++;
                continue;
            }

            // Read identifiers, parameters, numbers or booleans.
            $start = $i;
            while ($i < $len && (ctype_alnum($expr[$i]) || $expr[$i] === '_' || $expr[$i] === '.')) {
                $i++;
            }
            $value = substr($expr, $start, $i - $start);
            if ($value === '') {
                $i++;
                continue;
            }
            if (is_numeric($value)) {
                $tokens[] = ['type' => 'NUMBER', 'value' => str_contains($value, '.') ? (float)$value : (int)$value];
            } elseif (strtolower($value) === 'true') {
                $tokens[] = ['type' => 'BOOL', 'value' => true];
            } elseif (strtolower($value) === 'false') {
                $tokens[] = ['type' => 'BOOL', 'value' => false];
            } elseif (str_starts_with($value, 'param.')) {
                $tokens[] = ['type' => 'PARAM', 'value' => substr($value, 6)];
            } else {
                $tokens[] = ['type' => 'IDENTIFIER', 'value' => $value];
            }
        }
        return $tokens;
    }

    /**
     * Parses tokens recursively to produce a structured array AST node.
     *
     * @param array $tokens List of parsed tokens.
     * @param int $index Current token pointer.
     * @return array Parsed expression node.
     */
    private function parseExpression(array $tokens, int &$index): array
    {
        if (!isset($tokens[$index])) {
            return ['type' => 'const', 'value' => null];
        }
        $token = $tokens[$index];
        if ($token['type'] === 'STRING' || $token['type'] === 'NUMBER' || $token['type'] === 'BOOL') {
            $index++;
            return ['type' => 'const', 'value' => $token['value']];
        }
        if ($token['type'] === 'PARAM') {
            $index++;
            return ['type' => 'param', 'key' => $token['value']];
        }
        
        // Function or operator call notation: OP(arg1, arg2)
        if (($token['type'] === 'OP' || $token['type'] === 'IDENTIFIER') && isset($tokens[$index + 1]) && $tokens[$index + 1]['type'] === 'LPAREN') {
            $op = $token['value'];
            $index += 2; // Skip OP and LPAREN
            
            $args = [];
            while (isset($tokens[$index]) && $tokens[$index]['type'] !== 'RPAREN') {
                $args[] = $this->parseExpression($tokens, $index);
                if (isset($tokens[$index]) && $tokens[$index]['type'] === 'COMMA') {
                    $index++; // Skip COMMA
                }
            }
            if (isset($tokens[$index]) && $tokens[$index]['type'] === 'RPAREN') {
                $index++;
            }
            return ['type' => 'op', 'op' => $op, 'args' => $args];
        }

        if ($token['type'] === 'IDENTIFIER') {
            $index++;
            return ['type' => 'var', 'name' => $token['value']];
        }

        $index++;
        return ['type' => 'const', 'value' => $token['value']];
    }
}

<?php

namespace Parina\Shared\Services\Fsp;

/**
 * Class FspEngine
 * 
 * Secure, zero-RCE sandbox virtual machine executing pre-compiled FSP bytecode.
 */
class FspEngine implements FspEngineInterface
{
    /**
     * @var FspCompiler Instance of the compiler helper.
     */
    private FspCompiler $compiler;

    /**
     * FspEngine constructor.
     */
    public function __construct()
    {
        $this->compiler = new FspCompiler();
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $source): array
    {
        return $this->compiler->compile($source);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $instructions, array $params = []): array
    {
        $variables = [];
        $result = [];
        $pc = 0;
        $total = count($instructions);

        while ($pc < $total) {
            $inst = $instructions[$pc] ?? null;
            if (!is_array($inst) || !isset($inst['type'])) {
                $pc++;
                continue;
            }
            switch ($inst['type']) {
                case 'noop':
                    $pc++;
                    break;

                case 'assign':
                    if (isset($inst['var'], $inst['expr'])) {
                        $variables[$inst['var']] = $this->evalNode($inst['expr'], $variables, $params);
                    }
                    $pc++;
                    break;

                case 'result':
                    if (isset($inst['vars']) && is_array($inst['vars'])) {
                        foreach ($inst['vars'] as $v) {
                            $result[$v] = $variables[$v] ?? "";
                        }
                    }
                    $pc++;
                    break;

                case 'if':
                    if (isset($inst['expr'])) {
                        $cond = $this->evalNode($inst['expr'], $variables, $params);
                        if ($cond) {
                            $pc++;
                        } else {
                            $pc = $inst['jmp_target'] ?? $total;
                        }
                    } else {
                        $pc++;
                    }
                    break;

                case 'jmp':
                    $pc = $inst['jmp_target'] ?? $total;
                    break;
            }
        }
        return $result;
    }

    /**
     * Evaluates a compiled expression node within the sandbox context.
     *
     * @param array $node The compiled AST/expression node.
     * @param array $variables Reference to current local variable registry.
     * @param array $params Reference to current input parameters.
     * @return mixed Evaluated result value.
     */
    private function evalNode(array $node, array &$variables, array &$params): mixed
    {
        if (!isset($node['type'])) {
            return null;
        }
        switch ($node['type']) {
            case 'const':
                return $node['value'] ?? null;
            case 'param':
                return isset($node['key']) ? ($params[$node['key']] ?? null) : null;
            case 'var':
                // Follow fallback to variable name if not instantiated.
                $varName = $node['name'] ?? '';
                return $variables[$varName] ?? $varName;
            case 'op':
                $args = $node['args'] ?? [];
                $left = isset($args[0]) ? $this->evalNode($args[0], $variables, $params) : null;
                $right = isset($args[1]) ? $this->evalNode($args[1], $variables, $params) : null;
                
                // Switch inline evaluation to avoid method call overhead on low-resource hardware.
                $op = $node['op'] ?? '';
                switch ($op) {
                    case '==': return $left == $right;
                    case '~=': return $left != $right;
                    case '<':  return $left < $right;
                    case '<=': return $left <= $right;
                    case '>':  return $left > $right;
                    case '>=': return $left >= $right;
                    case '&&': return $left && $right;
                    case '||': return $left || $right;
                    case '+':  return (is_numeric($left) && is_numeric($right)) ? $left + $right : 0;
                    case '-':  return (is_numeric($left) && is_numeric($right)) ? $left - $right : 0;
                    case '*':  return (is_numeric($left) && is_numeric($right)) ? $left * $right : 0;
                    case '/':  return (is_numeric($left) && is_numeric($right) && $right != 0) ? $left / $right : 0;
                    case '%':  return (is_numeric($left) && is_numeric($right) && $right != 0) ? $left % $right : 0;
                    default:   return null;
                }
        }
        return null;
    }
}

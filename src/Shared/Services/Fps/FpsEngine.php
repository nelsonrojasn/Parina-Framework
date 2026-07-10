<?php

namespace Parina\Shared\Services\Fps;

/**
 * Class FpsEngine
 * 
 * Secure, zero-RCE sandbox virtual machine executing pre-compiled FPS bytecode.
 */
class FpsEngine implements FpsEngineInterface
{
    /**
     * @var FpsCompiler Instance of the compiler helper.
     */
    private FpsCompiler $compiler;

    /**
     * FpsEngine constructor.
     */
    public function __construct()
    {
        $this->compiler = new FpsCompiler();
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
            $inst = $instructions[$pc];
            switch ($inst['type']) {
                case 'noop':
                    $pc++;
                    break;

                case 'assign':
                    $variables[$inst['var']] = $this->evalNode($inst['expr'], $variables, $params);
                    $pc++;
                    break;

                case 'result':
                    foreach ($inst['vars'] as $v) {
                        $result[$v] = $variables[$v] ?? "";
                    }
                    $pc++;
                    break;

                case 'if':
                    $cond = $this->evalNode($inst['expr'], $variables, $params);
                    if ($cond) {
                        $pc++;
                    } else {
                        $pc = $inst['jmp_target'] ?? $total;
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
    private function evalNode(array $node, array &$variables, array &$params)
    {
        switch ($node['type']) {
            case 'const':
                return $node['value'];
            case 'param':
                return $params[$node['key']] ?? null;
            case 'var':
                // Follow fallback to variable name if not instantiated.
                return $variables[$node['name']] ?? $node['name'];
            case 'op':
                $left = $this->evalNode($node['args'][0], $variables, $params);
                $right = isset($node['args'][1]) ? $this->evalNode($node['args'][1], $variables, $params) : null;
                
                // Switch inline evaluation to avoid method call overhead on low-resource hardware.
                switch ($node['op']) {
                    case '==': return $left == $right;
                    case '~=': return $left != $right;
                    case '<':  return $left < $right;
                    case '<=': return $left <= $right;
                    case '>':  return $left > $right;
                    case '>=': return $left >= $right;
                    case '&&': return $left && $right;
                    case '||': return $left || $right;
                    case '+':  return $left + $right;
                    case '-':  return $left - $right;
                    case '*':  return $left * $right;
                    case '/':  return $right != 0 ? $left / $right : 0;
                    case '%':  return $right != 0 ? $left % $right : 0;
                    default:   return null;
                }
        }
        return null;
    }
}

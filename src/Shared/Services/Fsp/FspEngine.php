<?php
declare(strict_types=1);
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
     * @var array Mapped operators to callables.
     */
    private array $operators;

    /**
     * FspEngine constructor.
     */
    public function __construct()
    {
        $this->compiler = new FspCompiler();
        $this->operators = [
            '==' => fn($a, $b) => $a == $b,
            '~=' => fn($a, $b) => $a != $b,
            '<'  => fn($a, $b) => $a < $b,
            '<=' => fn($a, $b) => $a <= $b,
            '>'  => fn($a, $b) => $a > $b,
            '>=' => fn($a, $b) => $a >= $b,
            '&&' => fn($a, $b) => $a && $b,
            '||' => fn($a, $b) => $a || $b,
            '+'  => fn($a, $b) => $a + $b,
            '-'  => fn($a, $b) => $a - $b,
            '*'  => fn($a, $b) => $a * $b,
            '/'  => fn($a, $b) => $b != 0 ? $a / $b : 0,
            '%'  => fn($a, $b) => $b != 0 ? $a % $b : 0,
        ];
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
            $type = $inst['type'] ?? 'noop';
            $method = 'exec' . ucfirst($type);
            
            if (method_exists($this, $method)) {
                $this->$method($inst, $pc, $variables, $params, $result, $total);
            } else {
                $pc++;
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
        $type = $node['type'] ?? '';
        $method = 'eval' . ucfirst($type);
        if (method_exists($this, $method)) {
            return $this->$method($node, $variables, $params);
        }
        return null;
    }

    private function evalConst(array $node, array &$variables, array &$params)
    {
        return $node['value'];
    }

    private function evalParam(array $node, array &$variables, array &$params)
    {
        return $params[$node['key']] ?? null;
    }

    private function evalVar(array $node, array &$variables, array &$params)
    {
        return $variables[$node['name']] ?? $node['name'];
    }

    private function evalOp(array $node, array &$variables, array &$params)
    {
        $left = $this->evalNode($node['args'][0], $variables, $params);
        $right = isset($node['args'][1]) ? $this->evalNode($node['args'][1], $variables, $params) : null;
        $op = $node['op'];
        
        return isset($this->operators[$op]) ? $this->operators[$op]($left, $right) : null;
    }

    private function execNoop(array $inst, int &$pc, array &$variables, array &$params, array &$result, int $total): void
    {
        $pc++;
    }

    private function execAssign(array $inst, int &$pc, array &$variables, array &$params, array &$result, int $total): void
    {
        $variables[$inst['var']] = $this->evalNode($inst['expr'], $variables, $params);
        $pc++;
    }

    private function execResult(array $inst, int &$pc, array &$variables, array &$params, array &$result, int $total): void
    {
        foreach ($inst['vars'] as $v) {
            $result[$v] = $variables[$v] ?? "";
        }
        $pc++;
    }

    private function execIf(array $inst, int &$pc, array &$variables, array &$params, array &$result, int $total): void
    {
        $cond = $this->evalNode($inst['expr'], $variables, $params);
        if ($cond) {
            $pc++;
        } else {
            $pc = $inst['jmp_target'] ?? $total;
        }
    }

    private function execJmp(array $inst, int &$pc, array &$variables, array &$params, array &$result, int $total): void
    {
        $pc = $inst['jmp_target'] ?? $total;
    }
}

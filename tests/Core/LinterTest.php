<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;

class LinterTest extends TestCase
{
    private string $tempFile = '';

    protected function tearDown(): void
    {
        if (!empty($this->tempFile) && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
        parent::tearDown();
    }

    public function test_linter_succeeds_on_pristine_repository()
    {
        $output = [];
        $retval = 0;
        exec('php ' . escapeshellarg(dirname(dirname(__DIR__)) . '/bin/linter.php') . ' 2>&1', $output, $retval);
        
        $outputStr = implode("\n", $output);
        $this->assertEquals(0, $retval, "Linter should pass with status 0 on clean framework: \n" . $outputStr);
        $this->assertStringContainsString('STATUS: SUCCESS', $outputStr);
    }

    public function test_linter_detects_php_syntax_errors()
    {
        // Create a temporary php file with invalid syntax
        $this->tempFile = dirname(dirname(__DIR__)) . '/src/invalid_syntax_file.php';
        file_put_contents($this->tempFile, '<?php phpinfo('); // missing paren/semicolon syntax error
        
        $output = [];
        $retval = 0;
        exec('php ' . escapeshellarg(dirname(dirname(__DIR__)) . '/bin/linter.php') . ' 2>&1', $output, $retval);
        
        $outputStr = implode("\n", $output);
        $this->assertEquals(1, $retval, "Linter should fail when there is a PHP syntax error");
        $this->assertStringContainsString('Syntax check failed!', $outputStr);
    }

    public function test_linter_detects_cqs_violations()
    {
        // Create a temporary repository with CQS violation: a QueryRepository method returning void
        $this->tempFile = dirname(dirname(__DIR__)) . '/src/Shared/Services/ViolationQueryRepository.php';
        file_put_contents($this->tempFile, <<<'PHP'
<?php

namespace Parina\Shared\Services;

class ViolationQueryRepository
{
    public function invalidQueryMethod(): void
    {
        // Query methods must not return void
    }
}
PHP
        );
        
        $output = [];
        $retval = 0;
        exec('php ' . escapeshellarg(dirname(dirname(__DIR__)) . '/bin/linter.php') . ' 2>&1', $output, $retval);
        
        $outputStr = implode("\n", $output);
        $this->assertEquals(1, $retval, "Linter should fail when there is a CQS repository violation");
        $this->assertStringContainsString('CQS Repository Isolation violations found!', $outputStr);
        $this->assertStringContainsString('retorna void', $outputStr);
    }
}

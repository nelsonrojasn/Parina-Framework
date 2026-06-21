<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Parina\Core\View;

class ViewTest extends TestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/../fixtures/views';
        if (!is_dir($this->fixturesDir)) {
            mkdir($this->fixturesDir, 0755, true);
        }

        // Crear archivos de plantilla temporales
        file_put_contents($this->fixturesDir . '/test_view.php', '<h1>Hello <?= $name ?></h1>');
        file_put_contents($this->fixturesDir . '/test_layout.php', '<html><body><?= $content ?></body></html>');
        file_put_contents($this->fixturesDir . '/test_partial.php', '<p>My Partial</p>');

        // Configurar View para buscar en esta ruta
        View::setPaths([$this->fixturesDir . '/']);
    }

    protected function tearDown(): void
    {
        // Limpiar archivos temporales
        @unlink($this->fixturesDir . '/test_view.php');
        @unlink($this->fixturesDir . '/test_layout.php');
        @unlink($this->fixturesDir . '/test_partial.php');
        @rmdir($this->fixturesDir);
        @rmdir(dirname($this->fixturesDir));

        // Restaurar las rutas por defecto de View para no romper otros tests
        $srcDir = dirname(__DIR__, 2) . '/src';
        View::setPaths([
            $srcDir . '/Shared/Layouts/',
            $srcDir . '/Shared/Partials/',
            $srcDir . '/Modules/'
        ]);
    }

    public function test_add_path()
    {
        View::addPath('/some/other/path');
        $this->assertTrue(true);
    }

    public function test_render_view()
    {
        ob_start();
        View::render('test_view', ['name' => 'World']);
        $output = ob_get_clean();

        $this->assertEquals('<h1>Hello World</h1>', $output);
    }

    public function test_render_partial()
    {
        ob_start();
        View::partial('test_partial');
        $output = ob_get_clean();

        $this->assertEquals('<p>My Partial</p>', $output);
    }

    public function test_render_with_layout()
    {
        $output = View::renderWithLayout('test_view', 'test_layout', ['name' => 'Nelson']);

        $this->assertEquals('<html><body><h1>Hello Nelson</h1></body></html>', $output);
    }

    public function test_view_not_found_throws_exception()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("View not found: 'missing_view'");

        View::render('missing_view');
    }
}

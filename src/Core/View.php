<?php
namespace Parina\Core;

class View
{
    private static array $basePaths = [
        __DIR__ . '/../Shared/Layouts/',     // Path for templates
        __DIR__ . '/../Shared/Partials/',     // Path for partials
        __DIR__ . '/../Modules/'     // Path for modules
    ];

    /**
     * Allows dynamically adding new search paths
     */
    public static function addPath(string $path): void
    {
        self::$basePaths[] = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Useful for clearing state between tests
     */
    public static function setPaths(array $paths): void
    {
        self::$basePaths = $paths;
    }

    public static function render(string $path, array $data = []): void
    {
        $content = self::capture($path, $data);
        echo $content;
    }

    /**
     * Renders a partial directly to the output stream.
     * Ideal for being called inside other views: View::partial('navbar');
     */
    public static function partial(string $path, array $data = []): void
    {
        echo self::capture($path, $data);
    }

    public static function renderWithLayout(
        string $path,
        string $layout,
        array $data = []
    ): string {
        $content = self::capture($path, $data);

        // Pass content as a special variable $content
        $data['content'] = $content;

        // Search for the layout directly in basePaths. 
        // Si el usuario quiere subcarpetas, las puede pasar en el string: "admin/main"
        return self::capture($layout, $data);
    }

    private static function capture(string $path, array $data = []): string
    {
        $resolvedPath = null;
        $triedPaths = [];

        foreach (self::$basePaths as $base) {
            $candidate = $base . $path . '.php';
            $triedPaths[] = $candidate;
            if (file_exists($candidate)) {
                $resolvedPath = $candidate;
                break;
            }
        }

        if (!$resolvedPath) {
            $list = implode("\n - ", $triedPaths);
            throw new \RuntimeException("View not found: '$path'.\nTried in:\n - $list");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $resolvedPath;
        return ob_get_clean();
    }
}

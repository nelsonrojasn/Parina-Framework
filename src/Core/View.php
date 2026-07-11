<?php
declare(strict_types=1);
namespace Parina\Core;

use Parina\Core\Interfaces\ConfigInterface;
use Parina\Shared\Services\AuthInterface;
use Parina\Shared\Security\CipherInterface;
use Parina\Core\AppConfig;
use Parina\Shared\Services\SessionAuth;
use Parina\Shared\Security\AesCipherService;

class View
{
    private static array $basePaths = [
        __DIR__ . '/../Shared/Layouts/',     // Path for templates
        __DIR__ . '/../Shared/Partials/',     // Path for partials
        __DIR__ . '/../Features/'     // Path for features
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
        $resolvedPath = self::resolvePath($path);
        $data = self::mergeLayoutDependencies($data);

        extract($data, EXTR_SKIP);

        ob_start();
        include $resolvedPath;
        return ob_get_clean();
    }

    private static function resolvePath(string $path): string
    {
        foreach (self::$basePaths as $base) {
            $candidate = $base . $path . '.php';
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $tried = array_map(fn($base) => $base . $path . '.php', self::$basePaths);
        throw new \RuntimeException("View not found: '$path'.\nTried in:\n - " . implode("\n - ", $tried));
    }

    private static function mergeLayoutDependencies(array $data): array
    {
        $container = Container::getInstance();

        $config = ($container && $container->has(ConfigInterface::class))
            ? $container->get(ConfigInterface::class)
            : new AppConfig();

        $auth = ($container && $container->has(AuthInterface::class))
            ? $container->get(AuthInterface::class)
            : new SessionAuth();

        $cipher = ($container && $container->has(CipherInterface::class))
            ? $container->get(CipherInterface::class)
            : new AesCipherService($config);

        if (!isset($data['config'])) {
            $data['config'] = $config;
        }
        if (!isset($data['auth'])) {
            $data['auth'] = $auth;
        }
        if (!isset($data['cipher'])) {
            $data['cipher'] = $cipher;
        }

        return $data;
    }
}

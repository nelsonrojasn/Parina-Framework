<?php
namespace Parina\Core;

class Request
{
    public function __construct(
        public readonly array $query,
        public readonly array $post,
        public readonly array $server,
        public readonly array $files,
        public readonly array $cookies,
        public array $params = []
    ) {}

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE, []);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        return ($path === '' || $path === '/index.php') ? '/' : $path;
    }

    public function query(string $key, mixed $default = null, int $filter = FILTER_DEFAULT, mixed $options = 0): mixed
    {
        $value = $this->query[$key] ?? $default;
        return filter_var($value, $filter, $options);
    }

    public function post(string $key, mixed $default = null, int $filter = FILTER_DEFAULT, mixed $options = 0): mixed
    {
        $value = $this->post[$key] ?? $default;
        return filter_var($value, $filter, $options);
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }
}
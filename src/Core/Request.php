<?php
namespace Parina\Core;

use Parina\Core\Interfaces\RequestInterface;

class Request implements RequestInterface
{
    private array $attributes = [];
    private ?array $parsedJson = null;

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

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Retrieve standard HTTP header
     */
    public function header(string $name, ?string $default = null): ?string
    {
        $normalized = strtoupper(str_replace('-', '_', $name));
        
        if (in_array($normalized, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
            $formatted = $normalized;
        } else {
            $formatted = 'HTTP_' . $normalized;
        }
        
        return $this->server[$formatted] ?? $default;
    }

    /**
     * Retrieve input value uniformly from JSON payloads, POST, or GET
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $contentType = $this->header('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            if ($this->parsedJson === null) {
                $body = file_get_contents('php://input');
                $this->parsedJson = json_decode($body, true) ?? [];
            }
            return $this->parsedJson[$key] ?? $default;
        }
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get or set request-scoped context attributes
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Retrieve Bearer token from authorization header
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization', '');
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Check if request was sent via AJAX (XMLHttpRequest)
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
}
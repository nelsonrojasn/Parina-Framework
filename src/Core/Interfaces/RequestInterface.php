<?php
namespace Parina\Core\Interfaces;

interface RequestInterface
{
    public function method(): string;
    public function path(): string;
    public function query(string $key, mixed $default = null, int $filter = FILTER_DEFAULT, mixed $options = 0): mixed;
    public function post(string $key, mixed $default = null, int $filter = FILTER_DEFAULT, mixed $options = 0): mixed;
    public function param(string $key, mixed $default = null): mixed;
    public function server(string $key, mixed $default = null): mixed;
    public function header(string $name, ?string $default = null): ?string;
    public function input(string $key, mixed $default = null): mixed;
    public function setAttribute(string $key, mixed $value): void;
    public function getAttribute(string $key, mixed $default = null): mixed;
    public function getParams(): array;
    public function setParams(array $params): void;
    public function setParam(string $key, mixed $value): void;
    public function bearerToken(): ?string;
    public function isAjax(): bool;
}

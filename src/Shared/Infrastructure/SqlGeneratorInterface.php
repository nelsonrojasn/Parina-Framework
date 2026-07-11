<?php
declare(strict_types=1);
namespace Parina\Shared\Infrastructure;

interface SqlGeneratorInterface
{
    public function selectAll(string $table, array|string $columns): string;

    public function selectById(string $table, array|string $columns, string $primaryKey = 'id'): string;

    public function selectFirst(string $table, string $condition, array|string $columns): string;

    public function insert(string $table, array $data): string;

    public function update(string $table, array $data, string $primaryKey = 'id'): string;

    public function delete(string $table, string $primaryKey = 'id'): string;
}

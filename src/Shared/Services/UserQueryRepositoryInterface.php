<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

interface UserQueryRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findByUsername(string $username): ?array;
}

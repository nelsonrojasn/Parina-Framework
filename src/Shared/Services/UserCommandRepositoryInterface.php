<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

interface UserCommandRepositoryInterface
{
    public function save(array $user): bool;
    public function delete(int $id): bool;
}

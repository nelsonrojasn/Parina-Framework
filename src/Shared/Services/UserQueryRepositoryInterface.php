<?php

namespace Parina\Shared\Services;

interface UserQueryRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findByUsername(string $username): ?array;
    public function checkCredentials(string $username, string $password): ?array;
}

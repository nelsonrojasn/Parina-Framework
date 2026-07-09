<?php

namespace Parina\Shared\Services;

use Parina\Shared\Infrastructure\Db;
use Parina\Shared\Infrastructure\SqlGenerator;
use Parina\Shared\Infrastructure\SqlGeneratorInterface;

class DbUserQueryRepository implements UserQueryRepositoryInterface
{
    private SqlGeneratorInterface $sqlGenerator;

    public function __construct(?SqlGeneratorInterface $sqlGenerator = null)
    {
        $this->sqlGenerator = $sqlGenerator ?? new SqlGenerator();
    }

    public function findById(int $id): ?array
    {
        $sql = $this->sqlGenerator->selectFirst('users', 'id = :id', '*') . Db::limit(1);
        $stmt = Db::query($sql, ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = $this->sqlGenerator->selectFirst('users', 'username = :username', '*') . Db::limit(1);
        $stmt = Db::query($sql, ['username' => $username]);
        return $stmt->fetch() ?: null;
    }
}

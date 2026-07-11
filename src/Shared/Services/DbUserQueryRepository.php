<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\SqlGenerator;
use Parina\Shared\Infrastructure\SqlGeneratorInterface;

class DbUserQueryRepository implements UserQueryRepositoryInterface
{
    private SqlGeneratorInterface $sqlGenerator;

    public function __construct(
        private DatabaseAdapter $db,
        ?SqlGeneratorInterface $sqlGenerator = null
    ) {
        $this->sqlGenerator = $sqlGenerator ?? new SqlGenerator();
    }

    public function findById(int $id): ?array
    {
        $sql = $this->sqlGenerator->selectFirst('usuario', 'id = :id', '*') . $this->db->getLimitSql(1);
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = $this->sqlGenerator->selectFirst('usuario', 'username = :username', '*') . $this->db->getLimitSql(1);
        $stmt = $this->db->query($sql, ['username' => $username]);
        return $stmt->fetch() ?: null;
    }
}

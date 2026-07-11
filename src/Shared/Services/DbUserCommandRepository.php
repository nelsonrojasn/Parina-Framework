<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\SqlGenerator;
use Parina\Shared\Infrastructure\SqlGeneratorInterface;

class DbUserCommandRepository implements UserCommandRepositoryInterface
{
    private SqlGeneratorInterface $sqlGenerator;

    public function __construct(
        private DatabaseAdapter $db,
        ?SqlGeneratorInterface $sqlGenerator = null
    ) {
        $this->sqlGenerator = $sqlGenerator ?? new SqlGenerator();
    }

    public function save(array $user): bool
    {
        if (isset($user['id'])) {
            $id = $user['id'];
            $data = $user;
            unset($data['id']);

            $sql = $this->sqlGenerator->update('users', $data, 'id');
            
            $params = $data;
            $params['_id_where'] = $id;

            $stmt = $this->db->query($sql, $params);
            return $stmt->rowCount() > 0;
        } else {
            $sql = $this->sqlGenerator->insert('users', $user);
            return (bool)$this->db->query($sql, $user);
        }
    }

    public function delete(int $id): bool
    {
        $sql = $this->sqlGenerator->delete('users', 'id');
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}

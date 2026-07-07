<?php

namespace Parina\Shared\Services;

use Parina\Shared\Infrastructure\Db;

class DbUserRepository implements UserQueryRepositoryInterface, UserCommandRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $stmt = Db::query("SELECT * FROM users WHERE id = :id" . Db::limit(1), ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = Db::query("SELECT * FROM users WHERE username = :username" . Db::limit(1), ['username' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function checkCredentials(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function save(array $user): bool
    {
        if (isset($user['id'])) {
            $fields = [];
            $params = ['_id' => $user['id']];
            foreach ($user as $key => $val) {
                if ($key !== 'id') {
                    $fields[] = "$key = :$key";
                    $params[$key] = $val;
                }
            }
            $fieldsStr = implode(', ', $fields);
            $stmt = Db::query("UPDATE users SET $fieldsStr WHERE id = :_id", $params);
            return $stmt->rowCount() > 0;
        } else {
            $columns = implode(', ', array_keys($user));
            $placeholders = ':' . implode(', :', array_keys($user));
            return (bool)Db::query("INSERT INTO users ($columns) VALUES ($placeholders)", $user);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = Db::query("DELETE FROM users WHERE id = :id", ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}

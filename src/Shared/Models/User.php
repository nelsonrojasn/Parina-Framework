<?php

namespace Parina\Shared\Models;
use Parina\Shared\Infrastructure\Db;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;
use Parina\Core\Config;
use Parina\Core\Session;

class User extends BaseModel
{
    protected static string $table = 'users';

    public function __construct()
    {
        $sqliteAdapter = new SqliteAdapter(Config::getDbConfig());
        Db::init($sqliteAdapter);
    }

    // Custom SQL operation which is not a generic CRUD
    public function findByLoginName(string $login): ?array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE username = :login";
        $stmt = Db::query($sql, ['login' => $login]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function checkAuth(string $login, string $pass ): bool 
    {
        $user = $this->findByLoginName($login);
        if ($user && password_verify($user['salt'] . $pass, $user['password'])) {
            Session::set('is_logged_in', true);
            Session::set('user_id', $user['id']);
            Session::set('active', true);            
            Session::set('company_id', $user['company_id']);
            Session::set('flash', 'Welcome back, ' . $user['username'] . '!');
            return true;
        }
        return false;
    }
}
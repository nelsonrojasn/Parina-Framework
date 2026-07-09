<?php

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Parina\Shared\Infrastructure\SqlGenerator;

class SqlGeneratorTest extends TestCase
{
    private SqlGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new SqlGenerator();
    }

    public function test_select_all_with_string_columns()
    {
        $sql = $this->generator->selectAll('users', '*');
        $this->assertEquals("SELECT * FROM users", $sql);
    }

    public function test_select_all_with_array_columns()
    {
        $sql = $this->generator->selectAll('users', ['id', 'username', 'email']);
        $this->assertEquals("SELECT id, username, email FROM users", $sql);
    }

    public function test_select_by_id_default_pk()
    {
        $sql = $this->generator->selectById('users', '*');
        $this->assertEquals("SELECT * FROM users WHERE id = :id", $sql);
    }

    public function test_select_by_id_custom_pk()
    {
        $sql = $this->generator->selectById('users', ['email'], 'user_id');
        $this->assertEquals("SELECT email FROM users WHERE user_id = :id", $sql);
    }

    public function test_select_first_with_condition()
    {
        $sql = $this->generator->selectFirst('users', 'status = :status AND role = :role', '*');
        $this->assertEquals("SELECT * FROM users WHERE status = :status AND role = :role", $sql);
    }

    public function test_insert()
    {
        $data = ['username' => 'nelson', 'email' => 'nelson@example.com'];
        $sql = $this->generator->insert('users', $data);
        $this->assertEquals("INSERT INTO users (username, email) VALUES (:username, :email)", $sql);
    }

    public function test_update_default_pk()
    {
        $data = ['username' => 'nelson_new', 'email' => 'nelson_new@example.com'];
        $sql = $this->generator->update('users', $data);
        $this->assertEquals("UPDATE users SET username = :username, email = :email WHERE id = :_id_where", $sql);
    }

    public function test_update_custom_pk()
    {
        $data = ['status' => 'active'];
        $sql = $this->generator->update('users', $data, 'user_id');
        $this->assertEquals("UPDATE users SET status = :status WHERE user_id = :_id_where", $sql);
    }

    public function test_delete_default_pk()
    {
        $sql = $this->generator->delete('users');
        $this->assertEquals("DELETE FROM users WHERE id = :id", $sql);
    }

    public function test_delete_custom_pk()
    {
        $sql = $this->generator->delete('users', 'uid');
        $this->assertEquals("DELETE FROM users WHERE uid = :id", $sql);
    }
}

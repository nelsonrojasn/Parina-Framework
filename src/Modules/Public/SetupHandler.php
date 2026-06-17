<?php

namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\ErrorResponse;
use Parina\Core\Responses\RedirectResponse;
use Parina\Core\Config;
use Parina\Core\FileLogger;
use Parina\Core\View;
use Parina\Shared\Infrastructure\Db;

use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;

class SetupHandler implements Handler
{
    public function handle(Request $request): Response
    {
        try {
            // Check if Db folder exists. If not, create it!
            $dbFile = Config::getDbPath();
            $dbDir = dirname($dbFile);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            $sqliteAdapter = new SqliteAdapter(Config::getDbConfig());
            Db::init($sqliteAdapter);

            // Creating tables
            // Just one long string for simplicity purpose
            $sqlTables = "
            PRAGMA foreign_keys = ON;
            
            CREATE TABLE IF NOT EXISTS company (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                dni TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                activity TEXT,
                address TEXT,
                deleted INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_id INTEGER NOT NULL,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                salt TEXT NOT NULL, 
                email TEXT NOT NULL,
                is_active INTEGER DEFAULT 1,
                deleted INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES company(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS profiles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT,
                deleted INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS profile_user (
                profile_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                deleted INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (profile_id, user_id),
                FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS resources (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT NOT NULL UNIQUE,
                description TEXT,
                deleted INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS resource_user (
                resource_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                is_allowed INTEGER DEFAULT 1,
                deleted INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (resource_id, user_id),
                FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
        ";
            
            // Bulk table creation
            Db::exec($sqlTables);

            // Seeding tables with basic content
            // Check exists to avoid duplications
            $check = Db::query("SELECT COUNT(*) as total FROM profiles")->fetch();
            
            if ($check['total'] == 0) {
                // Create a Demo Company
                Db::query("INSERT INTO company (dni, name, activity, address) VALUES ('766543211', 'Demo Company', 'Retail Selling Products', 'Central Avenue 123')");
                $companyId = 1; // En SQLite con la base limpia será el 1

                // Create Admin profile
                Db::query("INSERT INTO profiles (name, description) VALUES ('admin', 'Administrador Total del Sistema')");
                $profileId = 1;

                // Create 'admin' user (Password: 'admin123' using native password_hash with salt)
                $salt = bin2hex(random_bytes(16));
                $hashedPassword = password_hash( $salt . 'admin123', PASSWORD_BCRYPT);
                Db::query(
                    "INSERT INTO users (company_id, username, salt, password, email) VALUES (:company_id, :username, :salt, :password, :email)",
                    [
                        'company_id' => $companyId,
                        'username'   => 'admin',
                        'salt' => $salt,
                        'password'   => $hashedPassword,
                        'email'      => 'admin@democompany.org'
                    ]
                );
                $userId = 1;

                // Asociate User and Profile Admin in the relationship table
                Db::query("INSERT INTO profile_user (profile_id, user_id) VALUES (:profile_id, :user_id)", [
                    'profile_id' => $profileId,
                    'user_id'    => $userId
                ]);

                // Create a few resources to check then with ACL
                Db::query("INSERT INTO resources (slug, description) VALUES ('sales.create', 'Allows to create a new sale')");
                Db::query("INSERT INTO resources (slug, description) VALUES ('inventory.show', 'Allows to show the inventory')");
            }

            // instead of an ugly blank screen, redirect to home
            return (new RedirectResponse('/', 303));

        } catch (\Exception $e) {
            FileLogger::log("SetupHandler Error (" . $e->getCode() . "): " . $e->getMessage());
            return (new ErrorResponse("DB Setup Error: " . $e->getMessage(), 500));
        }
    }
}
<?php

namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\ErrorResponse;
use Parina\Core\Responses\RedirectResponse;
use Parina\Core\Config;
use Parina\Core\FileLogger;
use Parina\Shared\Infrastructure\Db;

class SetupHandler implements Handler
{
    public function handle(Request $request): Response
    {
        try {
            $dbConfig = Config::getDbConfig();
            $driver = $dbConfig['driver'] ?? 'sqlite';

            // If using SQLite, ensure target directory exists
            if ($driver === 'sqlite') {
                $dbFile = Config::getDbPath();
                $dbDir = dirname($dbFile);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
            }

            // Determine schema file path
            $projectRoot = dirname(dirname(dirname(__DIR__)));
            $schemaFile = $projectRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . "schema.{$driver}.sql";

            if (!file_exists($schemaFile)) {
                throw new \Exception("Schema file not found for driver '{$driver}' at: {$schemaFile}");
            }

            // Load and execute schema SQL
            $sqlTables = file_get_contents($schemaFile);
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
                $hashedPassword = password_hash( 'admin123', PASSWORD_BCRYPT);
                Db::query(
                    "INSERT INTO users (company_id, username, password, email) VALUES (:company_id, :username, :password, :email)",
                    [
                        'company_id' => $companyId,
                        'username'   => 'admin',
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
<?php

namespace Parina\Shared\Services;

use Parina\Core\Interfaces\ConfigInterface;
use Parina\Shared\Infrastructure\DatabaseAdapter;

class DatabaseSetupService implements DatabaseSetupServiceInterface
{
    public function __construct(
        private ConfigInterface $config,
        private DatabaseAdapter $db
    ) {}

    public function setupDatabase(): void
    {
        $dbConfig = $this->config->getDbConfig();
        $driver = $dbConfig['driver'] ?? 'sqlite';

        // Determinar la ruta al esquema SQL
        $schemaFile = dirname(dirname(dirname(__DIR__))) 
            . DIRECTORY_SEPARATOR . 'database' 
            . DIRECTORY_SEPARATOR . "schema.{$driver}.sql";

        if (!file_exists($schemaFile)) {
            throw new \RuntimeException("Schema file not found for driver: {$driver}");
        }

        $sql = file_get_contents($schemaFile);

        // Si es SQLite, asegurar el directorio
        if ($driver === 'sqlite') {
            $dbPath = $this->config->getDbPath();
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
        }

        // Ejecutar las sentencias DDL directamente
        $this->db->exec($sql);
    }
}

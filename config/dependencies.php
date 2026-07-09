<?php

// config/dependencies.php

use Parina\Core\Interfaces\ConfigInterface;
use Parina\Core\Config;
use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\Adapters\MySqlAdapter;
use Parina\Shared\Infrastructure\Adapters\PostgreSqlAdapter;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;

return [
    // Bindings (Transient: new instance resolved every time)
    'bindings' => [
        // Ej: \Parina\Shared\Repositories\PrecioRepositoryInterface::class => \Parina\Shared\Repositories\LocalPrecioRepository::class,
    ],

    // Singletons (Shared: resolved once and cached)
    'singletons' => [
        // Config interface resolves to the AppConfig implementation
        ConfigInterface::class => \Parina\Core\AppConfig::class,

        // Security / Auth / Log Services
        \Parina\Shared\Services\AclInterface::class => \Parina\Shared\Services\Acl::class,
        \Parina\Shared\Services\AuthInterface::class => \Parina\Shared\Services\SessionAuth::class,
        \Parina\Core\Interfaces\Logger::class => \Parina\Core\FileLogger::class,
        \Parina\Shared\Services\TokenServiceInterface::class => \Parina\Shared\Services\JwtTokenService::class,
        \Parina\Shared\Security\CipherInterface::class => \Parina\Shared\Security\AesCipherService::class,

        // Repositories (CQS)
        \Parina\Shared\Services\UserQueryRepositoryInterface::class => \Parina\Shared\Services\DbUserQueryRepository::class,
        \Parina\Shared\Services\UserCommandRepositoryInterface::class => \Parina\Shared\Services\DbUserCommandRepository::class,

        // DatabaseAdapter resolves dynamically via factory closure (OCP compliant)
        DatabaseAdapter::class => function (\Parina\Core\Container $container) {
            $config = $container->get(ConfigInterface::class);
            $dbConfig = $config->getDbConfig();
            $driver = $dbConfig['driver'] ?? 'sqlite';

            return match ($driver) {
                'mysql' => new MySqlAdapter($dbConfig),
                'pgsql', 'postgres', 'postgresql' => new PostgreSqlAdapter($dbConfig),
                'sqlite', 'default' => new SqliteAdapter($dbConfig),
                default => throw new \InvalidArgumentException("Database driver not supported: {$driver}")
            };
        }
    ],
];

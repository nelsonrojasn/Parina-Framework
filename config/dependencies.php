<?php

// config/dependencies.php

use Parina\Core\Interfaces\ConfigInterface;
use Parina\Core\Config;
use Parina\Shared\Infrastructure\DatabaseAdapter;
use Parina\Shared\Infrastructure\Adapters\MySqlAdapter;
use Parina\Shared\Infrastructure\Adapters\PostgreSqlAdapter;
use Parina\Shared\Infrastructure\Adapters\SqliteAdapter;
use Parina\Shared\Infrastructure\Adapters\SqlServerAdapter;
use Parina\Shared\Infrastructure\Adapters\OracleAdapter;

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
        \Parina\Shared\Infrastructure\SqlGeneratorInterface::class => \Parina\Shared\Infrastructure\SqlGenerator::class,

        // Repositories (CQS)
        \Parina\Shared\Services\UserQueryRepositoryInterface::class => \Parina\Shared\Services\DbUserQueryRepository::class,
        \Parina\Shared\Services\UserCommandRepositoryInterface::class => \Parina\Shared\Services\DbUserCommandRepository::class,

        // Database drivers registered dynamically
        'db.driver.mysql'  => fn($c) => new MySqlAdapter($c->get(ConfigInterface::class)->getDbConfig()),
        'db.driver.pgsql'  => fn($c) => new PostgreSqlAdapter($c->get(ConfigInterface::class)->getDbConfig()),
        'db.driver.sqlite' => fn($c) => new SqliteAdapter($c->get(ConfigInterface::class)->getDbConfig()),
        'db.driver.sqlsrv' => fn($c) => new SqlServerAdapter($c->get(ConfigInterface::class)->getDbConfig()),
        'db.driver.oci'    => fn($c) => new OracleAdapter($c->get(ConfigInterface::class)->getDbConfig()),

        // DatabaseAdapter resolves dynamically via factory closure (OCP compliant)
        DatabaseAdapter::class => function (\Parina\Core\Container $container) {
            $config = $container->get(ConfigInterface::class);
            $dbConfig = $config->getDbConfig();
            $driver = $dbConfig['driver'] ?? 'sqlite';

            $driverMap = [
                'postgres'   => 'pgsql',
                'postgresql' => 'pgsql',
                'default'    => 'sqlite',
                'mssql'      => 'sqlsrv',
                'sqlserver'  => 'sqlsrv',
                'oracle'     => 'oci'
            ];
            $driver = $driverMap[$driver] ?? $driver;

            $serviceId = "db.driver.{$driver}";
            if (!$container->has($serviceId)) {
                throw new \InvalidArgumentException("Database driver not supported: {$driver}");
            }

            return $container->get($serviceId);
        }
    ],
];

<?php
declare(strict_types=1);
namespace Parina\Features\Database\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Responses\ErrorResponse;
use Parina\Core\Interfaces\ConfigInterface;
use Parina\Shared\Services\DatabaseSetupServiceInterface;

/**
 * Description: Inicializar la base de datos
 */
class SetupHandler implements Handler
{
    public function __construct(
        private ConfigInterface $config,
        private DatabaseSetupServiceInterface $setupService
    ) {}

    public function handle(RequestInterface $request): Response
    {
        if (!$this->config->allowSetup()) {
            return new ErrorResponse("Setup is not allowed in this environment.", 403);
        }

        try {
            // Invocar el servicio de configuración desacoplado
            $this->setupService->setupDatabase();

            $dbConfig = $this->config->getDbConfig();
            $driver = $dbConfig['driver'] ?? 'sqlite';

            $content = "<h1>¡Base de Datos Inicializada!</h1>";
            $content .= "<p>El esquema <code>schema.{$driver}.sql</code> se ha inyectado con éxito en el motor de base de datos.</p>";
            $content .= "<p><a href='/'>Volver al Inicio</a></p>";

            return new HtmlResponse($content, 200);
        } catch (\Exception $e) {
            return new ErrorResponse("Error durante el setup de la base de datos: " . $e->getMessage(), 500);
        }
    }
}
<?php
declare(strict_types=1);
namespace Parina\Features\Marketing\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Interfaces\ConfigInterface;
use Parina\Core\View;

class HomeHandler implements Handler
{
    public function __construct(
        private ConfigInterface $config
    ) {}

    public function handle(RequestInterface $request): Response
    {
        $data = [
            'db_exists' => file_exists($this->config->getDbPath()),
            'setup_allowed' => $this->config->allowSetup(),
        ];
        $content = View::renderWithLayout("Marketing/Views/home", "default", $data);
        return (new HtmlResponse($content, 200));
    }
}

<?php
namespace Parina\Features\Marketing\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Config;
use Parina\Core\View;

class HomeHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {
        $data = [
            'db_exists' => file_exists(Config::getDbPath()),
            'setup_allowed' => Config::allowSetup(),
        ];
        $content = View::renderWithLayout("Marketing/Views/home", "default", $data);
        return (new HtmlResponse($content, 200));
    }
}

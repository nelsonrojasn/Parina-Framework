<?php
namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Config;
use Parina\Core\View;

class HomeHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $data = [
            'db_exists' => file_exists(Config::getDbPath()),
            'setup_allowed' => Config::allowSetup(),
        ];
        $content = View::renderWithLayout("Public/Views/home", "default", $data);
        return (new HtmlResponse($content, 200));
    }
}

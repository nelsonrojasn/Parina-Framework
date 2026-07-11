<?php
declare(strict_types=1);
namespace Parina\Features\Marketing\Handlers;

use Parina\Core\View;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;


class AboutHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {
        $content = View::renderWithLayout("Marketing/Views/about", "default");
        return (new HtmlResponse($content, 200));        
    }
}
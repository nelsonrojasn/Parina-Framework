<?php
declare(strict_types=1);
namespace Parina\Features\AutoPurchase\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Request;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\HtmlResponse;

/**
 * Description: Comprar auto
 */
class AutoPurchaseHandler implements Handler
{
    public function handle(RequestInterface $request): Response
    {
        return new HtmlResponse("<h1>Comprar auto</h1>");
    }
}
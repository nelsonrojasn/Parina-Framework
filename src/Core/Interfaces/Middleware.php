<?php
declare(strict_types=1);
namespace Parina\Core\Interfaces;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Response;


interface Middleware
{
    public function handle(RequestInterface $request): ?Response;
}

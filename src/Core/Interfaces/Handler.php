<?php
namespace Parina\Core\Interfaces;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Response;


interface Handler
{
    public function handle(RequestInterface $request): Response;
}

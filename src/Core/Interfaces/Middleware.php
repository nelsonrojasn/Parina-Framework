<?php
namespace Parina\Core\Interfaces;

use Parina\Core\Request;
use Parina\Core\Interfaces\Response;


interface Middleware
{
    public function handle(Request $request): ?Response;
}

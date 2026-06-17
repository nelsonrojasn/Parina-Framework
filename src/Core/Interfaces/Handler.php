<?php
namespace Parina\Core\Interfaces;

use Parina\Core\Request;
use Parina\Core\Interfaces\Response;


interface Handler
{
    public function handle(Request $request): Response;
}

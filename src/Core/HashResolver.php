<?php

namespace Parina\Core;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Request;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Security\Cipher;
use Parina\Core\FileLogger;
use Parina\Core\Responses\RedirectResponse;
use Parina\Core\Responses\NotFoundResponse;

/**
 * Match encrypted URL which comes from /do?r=encrypted-action-and-parameters
 */
class HashResolver implements Handler
{
    /**
     * Map for 'actions' (a list of Handlers) 
     */
    public function __construct(
        protected array $registry = []
    ) {}

    public function handle(Request $request): Response
    {
        $r = $request->query('r');

        if (!$r) {
            FileLogger::log("Doesn't have 'r' parameter in URL");
            return (new NotFoundResponse());
        }

        try {
            // Using Cipher to decript and parse the URL
            [$action, $extraParams] = Cipher::parseUrlHash($r);

            if (!isset($this->registry[$action])) {
                FileLogger::log("Unregistered action requested: $action");
                return (new NotFoundResponse());
            }

            $handlerClass = $this->registry[$action];
            $handler = new $handlerClass();

            // Hidrating params in request
            $request->params = array_merge($request->params, $extraParams, ['_action' => $action]);

            // Execute expected handler
            return $handler->handle($request);

        } catch (\Exception $e) {
            FileLogger::log("Error: " . $e->getMessage());
            return (new RedirectResponse("/"));
        }
    }
}
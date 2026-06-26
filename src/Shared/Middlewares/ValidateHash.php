<?php

namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Security\Cipher;
use Parina\Core\Responses\NotFoundResponse;

class ValidateHash implements Middleware
{
    /**
     * Handle middleware execution
     *
     * @param Request $request
     * @param array|null $route Passed dynamically by Kernel but optional in signature for interface compatibility
     * @return Response|null
     */
    public function handle(Request $request, ?array $route = null): ?Response
    {
        $hash = $request->param('hash');

        if (!$hash) {
            return new NotFoundResponse();
        }

        try {
            // Decrypt and parse the URL hash (verifies signature and TTL)
            [$action, $extraParams] = Cipher::parseUrlHash($hash);

            // Verify the encrypted action matches the current route path
            if ($route && isset($route['path'])) {
                // Strip the '/{hash}' from the route path to get the action prefix
                $pathWithoutHash = str_replace('/{hash}', '', $route['path']);
                $expectedAction = ltrim($pathWithoutHash, '/');

                if ($action !== $expectedAction) {
                    return new NotFoundResponse();
                }
            }

            // Hydrate extra params back into the request
            $request->params = array_merge($request->params, $extraParams, ['_action' => $action]);

            return null; // validation passed, proceed
        } catch (\Exception $e) {
            // Decryption failed or link expired
            return new NotFoundResponse();
        }
    }
}

<?php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Shared\Services\AclInterface;
use Parina\Core\Responses\ErrorResponse;

class Acl implements Middleware
{
    private AclInterface $acl;

    public function __construct(AclInterface $acl)
    {
        $this->acl = $acl;
    }

    public function handle(Request $request): ?Response
    {
        if (!$this->acl->hasPermissions($request->path())) {
            return (new ErrorResponse("Permission denied.", 403));
        }
        // it's all good, move to next Middleware
        return null; 
    }
}

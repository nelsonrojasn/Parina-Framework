<?php

namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\BasicRealmResponse;
use Parina\Shared\Services\UserQueryRepositoryInterface;

class BasicAuth implements Middleware
{
    private UserQueryRepositoryInterface $userRepository;

    public function __construct(?UserQueryRepositoryInterface $userRepository = null)
    {
        $this->userRepository = $userRepository ?? new \Parina\Shared\Services\DbUserQueryRepository();
    }

    public function handle(Request $request): ?Response
    {
        $username = $request->server('PHP_AUTH_USER');
        $password = $request->server('PHP_AUTH_PW');

        if (empty($username) || empty($password)) {
            return new BasicRealmResponse("Unauthorized", 401);
        }

        $user = $this->userRepository->findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            return new BasicRealmResponse("Unauthorized", 401);
        }

        return null;
    }
}

<?php
declare(strict_types=1);
namespace Parina\Shared\Middlewares;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\BasicRealmResponse;
use Parina\Shared\Services\UserQueryRepositoryInterface;

class BasicAuth implements Middleware
{
    private UserQueryRepositoryInterface $userRepository;

    public function __construct(UserQueryRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(RequestInterface $request): ?Response
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

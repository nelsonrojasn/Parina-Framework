<?php
declare(strict_types=1);
namespace Parina\Features\Authentication\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Responses\RedirectResponse;
use Parina\Core\View;
use Parina\Shared\Services\UserQueryRepositoryInterface;
use Parina\Shared\Services\AuthInterface;
use Parina\Core\Session;

class LoginCheckHandler implements Handler
{
    private UserQueryRepositoryInterface $userRepository;
    private AuthInterface $auth;

    public function __construct(
        UserQueryRepositoryInterface $userRepository,
        AuthInterface $auth
    ) {
        $this->userRepository = $userRepository;
        $this->auth = $auth;
    }

    public function handle(RequestInterface $request): Response
    {    
        $username = $request->post('user');
        $password = $request->post('password');

        $user = $this->userRepository->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $this->auth->login($user);
            Session::set('flash', 'Welcome back, ' . $user['username'] . '!');
            return new RedirectResponse('/', 302);
        }

        Session::set('flash', 'Credentials are not valid. Please check and try again!');
        $content = View::renderWithLayout("Authentication/Views/login", "default");
        return new HtmlResponse($content, 200);
    }
}
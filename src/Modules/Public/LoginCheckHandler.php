<?php

namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
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
        ?UserQueryRepositoryInterface $userRepository = null,
        ?AuthInterface $auth = null
    ) {
        $this->userRepository = $userRepository ?? new \Parina\Shared\Services\DbUserRepository();
        $this->auth = $auth ?? new \Parina\Shared\Services\SessionAuth();
    }

    public function handle(Request $request): Response
    {    
        $username = $request->post('user');
        $password = $request->post('password');

        $user = $this->userRepository->checkCredentials($username, $password);

        if ($user) {
            $this->auth->login($user);
            Session::set('flash', 'Welcome back, ' . $user['username'] . '!');
            return new RedirectResponse('/', 302);
        }

        Session::set('flash', 'Credentials are not valid. Please check and try again!');
        $content = View::renderWithLayout("Public/Views/login", "default");
        return new HtmlResponse($content, 200);
    }
}
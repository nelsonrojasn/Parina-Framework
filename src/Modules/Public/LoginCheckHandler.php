<?php

namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\Responses\RedirectResponse;
use Parina\Core\View;
use Parina\Shared\Models\User;
use Parina\Core\Session;

class LoginCheckHandler implements Handler
{
    public function handle(Request $request): Response
    {    
        $user = $request->post('user');
        $password = $request->post('password');

        $userModel = new User();

        $user = $userModel->findByLoginName($user);

        if ($user && password_verify($user['salt'] . $password, $user['password'])) {
            Session::set('is_logged_in', true);
            Session::set('user_id', $user['id']);
            Session::set('active', true);            
            Session::set('company_id', $user['company_id']);
            Session::set('flash', 'Welcome back, ' . $user['username'] . '!');
            return (new RedirectResponse('/', 302));
        }

        Session::set('flash', 'Credentials are not valid. Please check and try again!');
        $content = View::renderWithLayout("Public/Views/login", "default");
        return (new HtmlResponse($content, 200));
    }
}
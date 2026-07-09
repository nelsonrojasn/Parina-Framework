<?php

// Dynamically generated routes configuration via CLI Scaffolding tool.

return [
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => \Parina\Features\Marketing\Handlers\HomeHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/about',
        'handler' => \Parina\Features\Marketing\Handlers\AboutHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/login',
        'handler' => \Parina\Features\Authentication\Handlers\LoginFormHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => \Parina\Features\Authentication\Handlers\LoginCheckHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/comprar/credito/auto/{id}',
        'handler' => \Parina\Features\AutoPurchase\Handlers\AutoPurchaseHandler::class,
        'middleware' => [
            \Parina\Shared\Middlewares\Auth::class
        ]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/home/{hash}',
        'handler' => \Parina\Features\Dashboard\Handlers\AdminHandler::class,
        'middleware' => [
            \Parina\Shared\Middlewares\RateLimit::class,
            \Parina\Shared\Middlewares\RequestSize::class,
            \Parina\Shared\Middlewares\SameOrigin::class,
            \Parina\Shared\Middlewares\Csrf::class,
            \Parina\Shared\Middlewares\Auth::class,
            \Parina\Shared\Middlewares\Acl::class,
            \Parina\Shared\Middlewares\ValidateHash::class
        ]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users/{hash}',
        'handler' => \Parina\Features\UserManagement\Handlers\UsersListHandler::class,
        'middleware' => [
            \Parina\Shared\Middlewares\RateLimit::class,
            \Parina\Shared\Middlewares\RequestSize::class,
            \Parina\Shared\Middlewares\SameOrigin::class,
            \Parina\Shared\Middlewares\Csrf::class,
            \Parina\Shared\Middlewares\Auth::class,
            \Parina\Shared\Middlewares\Acl::class,
            \Parina\Shared\Middlewares\ValidateHash::class
        ]
    ],
    [
        'method' => 'GET',
        'path' => '/logout/{hash}',
        'handler' => \Parina\Features\Authentication\Handlers\LogoutHandler::class,
        'middleware' => [
            \Parina\Shared\Middlewares\RateLimit::class,
            \Parina\Shared\Middlewares\RequestSize::class,
            \Parina\Shared\Middlewares\SameOrigin::class,
            \Parina\Shared\Middlewares\Csrf::class,
            \Parina\Shared\Middlewares\Auth::class,
            \Parina\Shared\Middlewares\Acl::class,
            \Parina\Shared\Middlewares\ValidateHash::class
        ]
    ]
];

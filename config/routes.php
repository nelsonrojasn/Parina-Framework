<?php

// Dynamically generated routes configuration via CLI Scaffolding tool.

$privateMiddlewares = [
    \Parina\Shared\Middlewares\RateLimit::class,
    \Parina\Shared\Middlewares\RequestSize::class, 
    \Parina\Shared\Middlewares\SameOrigin::class,
    \Parina\Shared\Middlewares\Csrf::class,
    \Parina\Shared\Middlewares\Auth::class,
    \Parina\Shared\Middlewares\Acl::class,
    \Parina\Shared\Middlewares\ValidateHash::class,
];

return [
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => \Parina\Modules\Public\HomeHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/about',
        'handler' => \Parina\Modules\Public\AboutHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/setup',
        'handler' => \Parina\Modules\Public\SetupHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/login',
        'handler' => \Parina\Modules\Public\LoginFormHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => \Parina\Modules\Public\LoginCheckHandler::class,
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/comprar/credito/auto/{id}',
        'handler' => \Parina\Modules\Public\AutoPurchaseHandler::class,
        'middleware' => [
            \Parina\Shared\Middlewares\Auth::class
        ]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/home/{hash}',
        'handler' => \Parina\Modules\Admin\AdminHandler::class,
        'middleware' => $privateMiddlewares
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users/{hash}',
        'handler' => \Parina\Modules\Admin\UsersListHandler::class,
        'middleware' => $privateMiddlewares
    ],
    [
        'method' => 'GET',
        'path' => '/logout/{hash}',
        'handler' => \Parina\Modules\Private\LogoutHandler::class,
        'middleware' => $privateMiddlewares
    ]
];

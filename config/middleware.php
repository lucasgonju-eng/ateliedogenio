<?php

declare(strict_types=1);

use AtelieDoGenio\Http\Middleware\AuthenticationMiddleware;
use AtelieDoGenio\Http\Middleware\RoleMiddleware;

return [
    'aliases' => [
        'auth' => AuthenticationMiddleware::class,
        'role' => RoleMiddleware::class,
    ],
];


<?php

declare(strict_types=1);

use AtelieDoGenio\Http\Middleware\JsonBodyParserMiddleware;
use AtelieDoGenio\Http\Middleware\CorsMiddleware;
use AtelieDoGenio\Http\Middleware\CsrfMiddleware;

return [
    'middlewares' => [
        CorsMiddleware::class,
        JsonBodyParserMiddleware::class,
        // RateLimitMiddleware removido a pedido (desativado globalmente)
        CsrfMiddleware::class,
    ],
    'base_path' => '/vendas',
];

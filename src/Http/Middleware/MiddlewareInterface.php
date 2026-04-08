<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param callable(ServerRequestInterface): ResponseInterface $next
     */
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface;
}


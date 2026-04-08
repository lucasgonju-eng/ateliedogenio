<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RoleMiddleware implements MiddlewareInterface
{
    private ?string $requiredRole = null;

    /**
     * @param string|null $role
     */
    public function setParameter(?string $role): void
    {
        $this->requiredRole = $role;
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!is_array($user)) {
            return $this->forbiddenResponse();
        }

        if ($this->requiredRole === null) {
            return $next($request);
        }

        if (($user['role'] ?? null) !== $this->requiredRole) {
            return $this->forbiddenResponse();
        }

        return $next($request);
    }

    private function forbiddenResponse(): ResponseInterface
    {
        return new Response(403, ['Content-Type' => 'application/json'], json_encode([
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Acesso negado.',
            ],
        ], JSON_THROW_ON_ERROR));
    }
}


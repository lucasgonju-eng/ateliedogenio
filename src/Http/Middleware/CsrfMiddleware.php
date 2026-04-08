<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Middleware;

use AtelieDoGenio\Http\Response\JsonResponse;
use AtelieDoGenio\Infrastructure\Security\CsrfTokenManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const INTENTION = 'api';

    public function __construct(private readonly CsrfTokenManager $tokens)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // Only enforce CSRF when the session cookie is present (stateful interaction).
        if (!isset($_COOKIE['ADG_SESSION'])) {
            return $next($request);
        }

        $token = $request->getHeaderLine('X-CSRF-TOKEN');

        if ($token === '') {
            $body = $request->getParsedBody();
            if (is_array($body) && isset($body['_token'])) {
                $token = (string) $body['_token'];
            }
        }

        if (!$this->tokens->isValid(self::INTENTION, $token ?: null)) {
            return JsonResponse::error('CSRF_FAIL', 'Token CSRF inválido ou ausente.', 419);
        }

        return $next($request);
    }
}


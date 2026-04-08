<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Middleware;

use AtelieDoGenio\Http\Response\JsonResponse;
use AtelieDoGenio\Infrastructure\Security\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(
        private readonly SessionManager $session,
        ?int $maxAttempts = null,
        ?int $decaySeconds = null
    ) {
        $this->maxAttempts = $maxAttempts ?? (int) ($_ENV['RATE_LIMIT_MAX_ATTEMPTS'] ?? 5);
        $this->decaySeconds = $decaySeconds ?? (int) ($_ENV['RATE_LIMIT_DECAY_SECONDS'] ?? 900);
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        // Toggle global via env: RATE_LIMIT_ENABLED=0|false
        $enabled = (string) ($_ENV['RATE_LIMIT_ENABLED'] ?? '1');
        if ($enabled === '0' || strtolower($enabled) === 'false') {
            return $next($request);
        }

        // Only throttle stateful (cookie-based) requests.
        $hasSessionCookie = isset($_COOKIE['ADG_SESSION']);
        if (!$hasSessionCookie) {
            return $next($request);
        }

        // Throttle only non-idempotent methods to avoid bloquear GET/HEAD/OPTIONS
        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        // Whitelist em ambiente local para rotas do PDV (admin e vendedor)
        $appEnv = (string) ($_ENV['APP_ENV'] ?? '');
        if ($appEnv === 'local') {
            $path = $request->getUri()->getPath() ?: '/';
            // Ignora rate limit em operações de venda e estoque por tamanho
            if (
                preg_match('#^/sales$#', $path) ||
                preg_match('#^/sales/[A-Za-z0-9\-]+/checkout$#', $path) ||
                preg_match('#^/products/[A-Za-z0-9\-]+/sizes$#', $path)
            ) {
                return $next($request);
            }
        }

        $key = $this->resolveKey($request);
        $bucket = $_SESSION['_rate_limit'][$key] ?? ['attempts' => 0, 'expires_at' => time() + $this->decaySeconds];

        if (time() > $bucket['expires_at']) {
            $bucket = ['attempts' => 0, 'expires_at' => time() + $this->decaySeconds];
        }

        if ($bucket['attempts'] >= $this->maxAttempts) {
            $retryAfter = max(1, $bucket['expires_at'] - time());

            return JsonResponse::error('RATE_LIMITED', 'Limite de requisições excedido.', 429)
                ->withHeader('Retry-After', (string) $retryAfter);
        }

        $bucket['attempts']++;
        $_SESSION['_rate_limit'][$key] = $bucket;

        $response = $next($request);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->withHeader('X-RateLimit-Remaining', (string) max(0, $this->maxAttempts - $bucket['attempts']))
            ->withHeader('X-RateLimit-Reset', (string) $bucket['expires_at']);
    }

    private function resolveKey(ServerRequestInterface $request): string
    {
        $identity = null;
        $user = $request->getAttribute('user');
        if (is_array($user) && isset($user['id'])) {
            $identity = 'user:' . $user['id'];
        } else {
            $server = $request->getServerParams();
            $ip = (string) ($server['HTTP_X_FORWARDED_FOR'] ?? $server['REMOTE_ADDR'] ?? 'cli');
            $identity = 'ip:' . $ip;
        }

        $method = strtoupper($request->getMethod());
        $path = $request->getUri()->getPath() ?: '/';

        // Bucket por identidade + método + caminho, assim GETs não impactam POST /auth/login
        return $identity . '|' . $method . ' ' . $path;
    }
}

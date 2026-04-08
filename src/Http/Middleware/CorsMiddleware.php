<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @var array<string>
     */
    private array $allowedOrigins;

    public function __construct(array $allowedOrigins = ['*'])
    {
        $this->allowedOrigins = $allowedOrigins;
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-CSRF-TOKEN',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        if ($this->isOriginAllowed($origin)) {
            $headers['Access-Control-Allow-Origin'] = $origin !== '' ? $origin : '*';
        }

        if ($request->getMethod() === 'OPTIONS') {
            return new Response(204, $headers);
        }

        $response = $next($request);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    private function isOriginAllowed(string $origin): bool
    {
        if ($origin === '' || $this->allowedOrigins === ['*']) {
            return true;
        }

        return in_array($origin, $this->allowedOrigins, true);
    }
}


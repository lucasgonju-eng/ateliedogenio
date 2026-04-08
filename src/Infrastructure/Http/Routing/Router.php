<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Http\Routing;

use FastRoute\Dispatcher;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Router
{
    private Dispatcher $dispatcher;

    /**
     * @param callable(\FastRoute\RouteCollector): void $routes
     */
    public function __construct(callable $routes)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher($routes);
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        // Suporte a subpasta (ex.: /vendas): remove prefixo configurado/auto-detectado
        try {
            $configuredBase = '';
            $configPath = dirname(__DIR__, 3) . '/config/app.php';
            if (file_exists($configPath)) {
                $cfg = require $configPath;
                if (is_array($cfg) && isset($cfg['base_path']) && is_string($cfg['base_path'])) {
                    $configuredBase = trim($cfg['base_path']);
                }
            }

            $base = $configuredBase;
            if ($base === '' || $base === '/') {
                $scriptName = (string) ($request->getServerParams()['SCRIPT_NAME'] ?? '');
                if ($scriptName !== '') {
                    $dir = str_replace('\\', '/', dirname($scriptName));
                    if (str_ends_with($dir, '/public')) {
                        $base = substr($dir, 0, -7);
                    }
                }
            }

            // Remove o prefixo base e a variação com '/public' caso o host reescreva para a subpasta
            if ($base !== '' && $base !== '/') {
                $normalizedBase = rtrim($base, '/');
                $baseWithPublic = $normalizedBase . '/public';

                if (str_starts_with($path, $baseWithPublic)) {
                    $path = substr($path, strlen($baseWithPublic)) ?: '/';
                } elseif (str_starts_with($path, $normalizedBase)) {
                    $path = substr($path, strlen($normalizedBase)) ?: '/';
                }
            }
        } catch (\Throwable) {
            // ignore
        }

        $routeInfo = $this->dispatcher->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                /** @var callable $handler */
                $handler = $routeInfo[1];
                /** @var array<string, string> $vars */
                $vars = $routeInfo[2];

                return $handler($request, $vars);

            case Dispatcher::NOT_FOUND:
                return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Recurso não encontrado.',
                    ],
                ], JSON_THROW_ON_ERROR));

            case Dispatcher::METHOD_NOT_ALLOWED:
                /** @var array<int, string> $allowedMethods */
                $allowedMethods = $routeInfo[1];

                return new Response(405, [
                    'Content-Type' => 'application/json',
                    'Allow' => implode(', ', $allowedMethods),
                ], json_encode([
                    'error' => [
                        'code' => 'METHOD_NOT_ALLOWED',
                        'method' => $method,
                        'allowed' => $allowedMethods,
                        'path' => $path,
                        'message' => 'Método HTTP não permitido para este recurso.',
                    ],
                ], JSON_THROW_ON_ERROR));

            default:
                return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                    'error' => [
                        'code' => 'ROUTER_ERROR',
                        'message' => 'Erro de roteamento inesperado.',
                    ],
                ], JSON_THROW_ON_ERROR));
        }
    }
}

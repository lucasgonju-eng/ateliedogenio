<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Http\Routing;

use AtelieDoGenio\Infrastructure\Container\Container;
use AtelieDoGenio\Http\Middleware\MiddlewareInterface;
use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RouteGroup
{
    private array $middlewareAliases;

    public function __construct(
        private readonly RouteCollector $router,
        private readonly Container $container
    ) {
        $config = require dirname(__DIR__, 4) . '/config/middleware.php';
        $this->middlewareAliases = $config['aliases'] ?? [];
    }

    /**
     * @param array{0: class-string, 1: string}|callable $handler
     * @param array<int, string> $middlewares
     */
    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->register('GET', $path, $handler, $middlewares);
    }

    /**
     * @param array{0: class-string, 1: string}|callable $handler
     * @param array<int, string> $middlewares
     */
    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->register('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->register('PUT', $path, $handler, $middlewares);
    }

    public function patch(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->register('PATCH', $path, $handler, $middlewares);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->register('DELETE', $path, $handler, $middlewares);
    }

    /**
     * @param array{0: class-string, 1: string}|callable $handler
     * @param array<int, string> $middlewares
     */
    private function register(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        $resolvedHandler = $this->createHandler($handler, $middlewares);
        $this->router->addRoute($method, $path, $resolvedHandler);
    }

    /**
     * @param array{0: class-string, 1: string}|callable $handler
     * @param array<int, string> $middlewares
     * @return callable(ServerRequestInterface, array<string, string>): ResponseInterface
     */
    private function createHandler(array|callable $handler, array $middlewares): callable
    {
        return function (ServerRequestInterface $request, array $routeParameters) use ($handler, $middlewares): ResponseInterface {
            $controllerCallable = $this->resolveHandler($handler, $routeParameters);

            $pipeline = $this->buildMiddlewarePipeline($controllerCallable, $middlewares, $routeParameters);

            return $pipeline($request);
        };
    }

    /**
     * @param array{0: class-string, 1: string}|callable $handler
     * @return callable(ServerRequestInterface): ResponseInterface
     */
    private function resolveHandler(array|callable $handler, array $routeParameters): callable
    {
        if (is_callable($handler)) {
            return function (ServerRequestInterface $request) use ($handler, $routeParameters): ResponseInterface {
                return $handler($request, $routeParameters);
            };
        }

        [$class, $method] = $handler;
        $controller = $this->container->get($class);

        return function (ServerRequestInterface $request) use ($controller, $method, $routeParameters): ResponseInterface {
            return $controller->$method($request, $routeParameters);
        };
    }

    /**
     * @param callable(ServerRequestInterface): ResponseInterface $destination
     * @param array<int, string> $middlewares
     * @return callable(ServerRequestInterface): ResponseInterface
     */
    private function buildMiddlewarePipeline(callable $destination, array $middlewares, array $routeParameters): callable
    {
        $stack = array_reverse($middlewares);

        $next = $destination;

        foreach ($stack as $middlewareAlias) {
            [$alias, $parameter] = $this->parseMiddlewareAlias($middlewareAlias);
            $middlewareClass = $this->middlewareAliases[$alias] ?? null;

            if ($middlewareClass === null) {
                throw new \RuntimeException(sprintf('Middleware alias "%s" is not configured.', $alias));
            }

            /** @var MiddlewareInterface $middleware */
            $middleware = $this->container->get($middlewareClass);

            $nextMiddleware = $next;
            $next = function (ServerRequestInterface $request) use ($middleware, $nextMiddleware, $parameter, $routeParameters): ResponseInterface {
                if (method_exists($middleware, 'setParameter')) {
                    $middleware->setParameter($parameter);
                }

                if (method_exists($middleware, 'setRouteParameters')) {
                    $middleware->setRouteParameters($routeParameters);
                }

                return $middleware->process($request, $nextMiddleware);
            };
        }

        return $next;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function parseMiddlewareAlias(string $middleware): array
    {
        $parts = explode(':', $middleware, 2);

        $alias = $parts[0];
        $parameter = $parts[1] ?? null;

        return [$alias, $parameter];
    }
}

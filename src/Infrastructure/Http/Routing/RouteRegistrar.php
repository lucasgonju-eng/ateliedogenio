<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Http\Routing;

use AtelieDoGenio\Infrastructure\Container\Container;
use FastRoute\RouteCollector;

final class RouteRegistrar
{
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @return callable(RouteCollector): void
     */
    public function register(): callable
    {
        $routesFile = dirname(__DIR__, 4) . '/routes/api.php';

        if (!file_exists($routesFile)) {
            throw new \RuntimeException('Routes file not found: ' . $routesFile);
        }

        /** @var callable(RouteCollector, Container): void $routesDefinition */
        $routesDefinition = require $routesFile;

        return function (RouteCollector $router) use ($routesDefinition): void {
            $routesDefinition($router, $this->container);
        };
    }
}

<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Container;

use Closure;
use RuntimeException;

final class Container
{
    /**
     * @var array<string, Closure(self): mixed>
     */
    private array $factories = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @var array<string, bool>
     */
    private array $resolving = [];

    /**
     * @param array<string, Closure(self): mixed> $definitions
     */
    public function __construct(array $definitions = [])
    {
        foreach ($definitions as $id => $factory) {
            $this->set($id, $factory);
        }
    }

    /**
     * @template T
     * @param class-string<T>|string $id
     * @return T
     */
    public function get(string $id)
    {
        if (\array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (!\array_key_exists($id, $this->factories)) {
            throw new RuntimeException(sprintf('Dependency "%s" is not registered in the container.', $id));
        }

        if (isset($this->resolving[$id])) {
            throw new RuntimeException(sprintf('Circular dependency detected while resolving "%s".', $id));
        }

        $this->resolving[$id] = true;

        $factory = $this->factories[$id];
        $instance = $factory($this);
        $this->instances[$id] = $instance;

        unset($this->resolving[$id]);

        return $instance;
    }

    /**
     * @param string $id
     * @param Closure(self): mixed $factory
     */
    public function set(string $id, Closure $factory): void
    {
        $this->factories[$id] = $factory;
    }
}


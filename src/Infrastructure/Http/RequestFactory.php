<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;

final class RequestFactory
{
    private ServerRequestCreator $creator;

    public function __construct(Psr17Factory $factory)
    {
        $this->creator = new ServerRequestCreator(
            $factory,
            $factory,
            $factory,
            $factory
        );
    }

    public function fromGlobals(): ServerRequestInterface
    {
        return $this->creator->fromGlobals();
    }
}


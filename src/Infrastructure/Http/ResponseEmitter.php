<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Http;

use Psr\Http\Message\ResponseInterface;

final class ResponseEmitter
{
    public function __construct(private readonly \Nyholm\Psr7\Factory\Psr17Factory $factory)
    {
    }

    public function emit(ResponseInterface $response): void
    {
        if (!headers_sent()) {
            header(sprintf('HTTP/%s %d %s', '1.1', $response->getStatusCode(), $response->getReasonPhrase()), true, $response->getStatusCode());
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        echo $response->getBody();
    }
}


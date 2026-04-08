<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Middleware;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class JsonBodyParserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();

            if ($body !== '') {
                $parsed = json_decode($body, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    $request = $request->withParsedBody($parsed);
                } else {
                    $error = [
                        'error' => [
                            'code' => 'INVALID_JSON',
                            'message' => 'JSON mal formatado.',
                        ],
                    ];

                    return new \Nyholm\Psr7\Response(
                        400,
                        ['Content-Type' => 'application/json'],
                        Stream::create(json_encode($error, JSON_THROW_ON_ERROR))
                    );
                }
            }
        }

        return $next($request);
    }
}


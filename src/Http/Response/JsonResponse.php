<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Response;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final class JsonResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public static function success(array $data, int $status = 200): ResponseInterface
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data, JSON_THROW_ON_ERROR));
    }

    public static function error(string $code, string $message, int $status): ResponseInterface
    {
        $payload = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        return new Response($status, ['Content-Type' => 'application/json'], json_encode($payload, JSON_THROW_ON_ERROR));
    }
}


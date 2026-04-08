<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Http\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class BaseController
{
    protected function json(array $data, int $status = 200): ResponseInterface
    {
        return JsonResponse::success($data, $status);
    }

    protected function error(string $code, string $message, int $status): ResponseInterface
    {
        return JsonResponse::error($code, $message, $status);
    }

    protected function input(ServerRequestInterface $request): array
    {
        $payload = $request->getParsedBody();

        return is_array($payload) ? $payload : [];
    }

    protected function query(ServerRequestInterface $request): array
    {
        return $request->getQueryParams();
    }

    /**
     * @return array{id: string, role: string|null, claims: array<string, mixed>}
     */
    protected function user(ServerRequestInterface $request): array
    {
        /** @var array{id: string, role: string|null, claims: array<string, mixed>} $user */
        $user = $request->getAttribute('user', [
            'id' => '',
            'role' => null,
            'claims' => [],
        ]);

        return $user;
    }
}


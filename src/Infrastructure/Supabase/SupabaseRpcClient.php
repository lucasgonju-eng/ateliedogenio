<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Supabase;

use RuntimeException;

final class SupabaseRpcClient
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    /**
     * @param string $function
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return array<string, mixed>|array<int, mixed>|null
     */
    public function call(string $function, array $parameters = [], array $options = []): array|null
    {
        $prefer = $options['prefer'] ?? 'tx=commit';

        $payload = [
            'json' => $parameters,
            'headers' => [
                'Content-Type' => 'application/json',
                'Prefer' => $prefer,
            ],
        ];

        $path = sprintf('rest/v1/rpc/%s', $function);

        try {
            return $this->client->request('POST', $path, $payload);
        } catch (RuntimeException $exception) {
            throw $exception;
        }
    }
}


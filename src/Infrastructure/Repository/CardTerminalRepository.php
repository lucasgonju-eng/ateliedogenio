<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\CardTerminal;
use AtelieDoGenio\Domain\Repository\CardTerminalRepositoryInterface;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class CardTerminalRepository implements CardTerminalRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    /**
     * @return list<CardTerminal>
     */
    public function findAll(): array
    {
        $response = $this->client->request('GET', 'rest/v1/card_terminals', [
            'query' => ['select' => '*', 'order' => 'name.asc'],
        ]);

        if (!is_array($response)) {
            return [];
        }

        /** @var list<array<string,mixed>> $rows */
        $rows = $response;

        return array_map(fn (array $row) => $this->map($row), $rows);
    }

    public function upsert(?string $id, string $name, bool $active): CardTerminal
    {
        $payload = [
            'name' => $name,
            'active' => $active,
        ];

        if ($id !== null && $id !== '') {
            $resp = $this->client->runWithServiceRole(function () use ($id, $payload) {
                return $this->client->request('PATCH', 'rest/v1/card_terminals', [
                    'query' => ['id' => 'eq.' . $id, 'select' => '*'],
                    'json' => [$payload],
                    'headers' => ['Prefer' => 'return=representation'],
                ]);
            });

            /** @var array<string,mixed> $row */
            $row = is_array($resp) && isset($resp[0]) && is_array($resp[0]) ? $resp[0] : (is_array($resp) ? $resp : []);
            $row['id'] = $row['id'] ?? $id;
            return $this->map($row);
        }

        $resp = $this->client->runWithServiceRole(function () use ($payload) {
            return $this->client->request('POST', 'rest/v1/card_terminals', [
                'json' => [$payload],
                'headers' => ['Prefer' => 'return=representation'],
            ]);
        });

        /** @var array<string,mixed> $row */
        $row = is_array($resp) && isset($resp[0]) && is_array($resp[0]) ? $resp[0] : (is_array($resp) ? $resp : []);
        return $this->map($row);
    }

    /**
     * @param array<string,mixed> $row
     */
    private function map(array $row): CardTerminal
    {
        return new CardTerminal(
            id: (string)($row['id'] ?? ''),
            name: (string)($row['name'] ?? ''),
            active: (bool)($row['active'] ?? true)
        );
    }
}

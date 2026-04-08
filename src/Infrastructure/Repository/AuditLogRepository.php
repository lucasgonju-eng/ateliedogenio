<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\AuditLog;
use AtelieDoGenio\Domain\Repository\AuditLogRepositoryInterface;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<AuditLog>
     */
    public function search(array $filters = []): array
    {
        $query = [
            'select' => '*',
            'order' => 'created_at.desc',
        ];

        if (($filters['entity'] ?? null) !== null) {
            $query['entity'] = 'eq.' . $filters['entity'];
        }

        if (($filters['entity_id'] ?? null) !== null) {
            $query['entity_id'] = 'eq.' . $filters['entity_id'];
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        $response = $this->client->request('GET', 'rest/v1/audit_logs', [
            'query' => $query,
        ]);

        if (!is_array($response)) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        return array_map($this->hydrate(...), $rows);
    }

    public function record(AuditLog $entry): void
    {
        $payload = [[
            'id' => $entry->id(),
            'actor_id' => $entry->actorId(),
            'actor_role' => $entry->actorRole(),
            'entity' => $entry->entity(),
            'entity_id' => $entry->entityId(),
            'action' => $entry->action(),
            'payload' => $entry->payload(),
            'created_at' => $entry->createdAt()->format(DATE_ATOM),
        ]];

        $this->client->runWithServiceRole(function () use ($payload): void {
            $this->client->request('POST', 'rest/v1/audit_logs', [
                'json' => $payload,
                'headers' => ['Prefer' => 'return=minimal'],
            ]);
        });
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): AuditLog
    {
        $payload = $row['payload'] ?? [];

        if (is_string($payload)) {
            try {
                $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                $payload = is_array($decoded) ? $decoded : [];
            } catch (\Throwable) {
                $payload = [];
            }
        }

        return new AuditLog(
            id: $row['id'],
            actorId: $row['actor_id'] ?? null,
            actorRole: $row['actor_role'] ?? null,
            entity: $row['entity'],
            entityId: $row['entity_id'] ?? null,
            action: $row['action'],
            payload: is_array($payload) ? $payload : [],
            createdAt: new \DateTimeImmutable($row['created_at'])
        );
    }
}

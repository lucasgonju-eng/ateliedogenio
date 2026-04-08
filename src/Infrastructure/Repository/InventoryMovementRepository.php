<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\InventoryMovement;
use AtelieDoGenio\Domain\Enum\InventoryMovementType;
use AtelieDoGenio\Domain\Repository\InventoryMovementRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseRpcClient;

final class InventoryMovementRepository implements InventoryMovementRepositoryInterface
{
    public function __construct(
        private readonly SupabaseClient $client,
        private readonly SupabaseRpcClient $rpc
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<InventoryMovement>
     */
    public function search(array $filters = []): array
    {
        $query = [
            'select' => '*',
            'order' => 'created_at.desc',
        ];

        if (($filters['product_id'] ?? null) !== null) {
            $query['product_id'] = 'eq.' . $filters['product_id'];
        }

        if (($filters['type'] ?? null) !== null) {
            $query['type'] = 'eq.' . $filters['type'];
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        $response = $this->client->request('GET', 'rest/v1/inventory_movements', [
            'query' => $query,
        ]);

        if ($response === null) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        return array_map(fn (array $row): InventoryMovement => $this->mapMovement($row), $rows);
    }

    public function save(InventoryMovement $movement): void
    {
        $payload = [
            '_product_id' => $movement->productId(),
            '_qty' => $movement->quantity(),
            '_unit_cost' => $movement->unitCost()->toFloat(),
            '_type' => $movement->type()->value,
            '_reference' => $movement->reference(),
            '_user_id' => $movement->userId(),
        ];

        $this->rpc->call('fn_inventory_adjust', $payload, [
            'prefer' => 'tx=commit',
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapMovement(array $row): InventoryMovement
    {
        return new InventoryMovement(
            id: $row['id'],
            productId: $row['product_id'],
            userId: $row['user_id'],
            type: InventoryMovementType::from($row['type']),
            quantity: (int) $row['quantity'],
            unitCost: Money::fromFloat((float) $row['unit_cost']),
            reference: $row['reference'] ?? null,
            createdAt: new \DateTimeImmutable($row['created_at'])
        );
    }
}


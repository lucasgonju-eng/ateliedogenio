<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\Commission;
use AtelieDoGenio\Domain\Enum\CommissionStatus;
use AtelieDoGenio\Domain\Repository\CommissionRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class CommissionRepository implements CommissionRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    public function search(array $filters = []): array
    {
        $query = [
            'select' => '*',
            'order' => 'created_at.desc',
        ];

        if (($filters['vendor_id'] ?? null) !== null) {
            $query['vendor_id'] = 'eq.' . $filters['vendor_id'];
        }

        if (($filters['status'] ?? null) !== null) {
            $query['status'] = 'eq.' . $filters['status'];
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        $response = $this->client->request('GET', 'rest/v1/commissions', [
            'query' => $query,
        ]);

        if (!is_array($response)) {
            return [];
        }

        return array_map(fn (array $row): Commission => $this->hydrate($row), $response);
    }

    public function record(Commission $commission): Commission
    {
        $payload = [[
            'id' => $commission->id(),
            'sale_id' => $commission->saleId(),
            'vendor_id' => $commission->vendorId(),
            'amount' => $commission->amount()->toFloat(),
            'status' => $commission->status()->value,
            'created_at' => $commission->createdAt()->format(DATE_ATOM),
            'paid_at' => $commission->paidAt()?->format(DATE_ATOM),
        ]];

        $response = $this->client->request('POST', 'rest/v1/commissions', [
            'json' => $payload,
            'headers' => ['Prefer' => 'return=representation'],
        ]);

        if (!is_array($response) || !isset($response[0]) || !is_array($response[0])) {
            return $commission;
        }

        return $this->hydrate($response[0]);
    }

    public function updateStatus(string $commissionId, CommissionStatus $status, ?\DateTimeImmutable $paidAt): void
    {
        $payload = [
            'status' => $status->value,
            'paid_at' => $paidAt?->format(DATE_ATOM),
        ];

        $this->client->request('PATCH', 'rest/v1/commissions?id=eq.' . $commissionId, [
            'json' => $payload,
            'headers' => ['Prefer' => 'return=minimal'],
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Commission
    {
        $paidAt = null;
        if (isset($row['paid_at']) && $row['paid_at'] !== null) {
            $paidAt = new \DateTimeImmutable($row['paid_at']);
        }

        return new Commission(
            id: $row['id'],
            saleId: $row['sale_id'],
            vendorId: $row['vendor_id'],
            amount: Money::fromFloat((float) $row['amount']),
            status: CommissionStatus::from($row['status']),
            createdAt: new \DateTimeImmutable($row['created_at']),
            paidAt: $paidAt
        );
    }
}

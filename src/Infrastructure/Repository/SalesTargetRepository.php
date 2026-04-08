<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\SalesTarget;
use AtelieDoGenio\Domain\Repository\SalesTargetRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class SalesTargetRepository implements SalesTargetRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    public function findActive(string $vendorId, \DateTimeImmutable $date): ?SalesTarget
    {
        $query = [
            'select' => '*',
            'and' => sprintf('(vendor_id.eq.%s,period_start.lte.%s,period_end.gte.%s)',
                $vendorId,
                $date->format('Y-m-d'),
                $date->format('Y-m-d')
            ),
            'limit' => '1',
        ];

        try {
            $response = $this->client->request('GET', 'rest/v1/sales_targets', [
                'headers' => ['Prefer' => 'single-object'],
                'query' => $query,
            ]);
        } catch (\Throwable) {
            // Tabela inexistente ou indisponível: sem meta de comissão ativa
            return null;
        }

        if ($response === null) {
            return null;
        }

        $row = is_array($response) && isset($response[0]) && is_array($response[0]) ? $response[0] : $response;

        return $this->hydrate($row);
    }

    public function search(array $filters = []): array
    {
        $query = [
            'select' => '*',
            'order' => 'period_start.desc',
        ];

        if (($filters['vendor_id'] ?? null) !== null) {
            $query['vendor_id'] = 'eq.' . $filters['vendor_id'];
        }

        if (($filters['period_start'] ?? null) !== null) {
            $query['period_start'] = 'eq.' . $filters['period_start'];
        }

        if (($filters['period_end'] ?? null) !== null) {
            $query['period_end'] = 'eq.' . $filters['period_end'];
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        $response = $this->client->request('GET', 'rest/v1/sales_targets', [
            'query' => $query,
        ]);

        if (!is_array($response)) {
            return [];
        }

        return array_map(fn (array $row): SalesTarget => $this->hydrate($row), $response);
    }

    public function upsert(SalesTarget $target): SalesTarget
    {
        $payload = [[
            'id' => $target->id(),
            'vendor_id' => $target->vendorId(),
            'period_start' => $target->periodStart()->format('Y-m-d'),
            'period_end' => $target->periodEnd()->format('Y-m-d'),
            'goal_amount' => $target->goalAmount()->toFloat(),
            'commission_rate' => $target->commissionRate(),
            'created_at' => $target->createdAt()->format(DATE_ATOM),
        ]];

        $result = $this->client->request('POST', 'rest/v1/sales_targets', [
            'json' => $payload,
            'headers' => ['Prefer' => 'return=representation,resolution=merge-duplicates'],
        ]);

        if (!is_array($result) || !isset($result[0]) || !is_array($result[0])) {
            return $target;
        }

        return $this->hydrate($result[0]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): SalesTarget
    {
        return new SalesTarget(
            id: $row['id'],
            vendorId: $row['vendor_id'],
            periodStart: new \DateTimeImmutable($row['period_start']),
            periodEnd: new \DateTimeImmutable($row['period_end']),
            goalAmount: Money::fromFloat((float) $row['goal_amount']),
            commissionRate: (float) $row['commission_rate'],
            createdAt: new \DateTimeImmutable($row['created_at'])
        );
    }
}

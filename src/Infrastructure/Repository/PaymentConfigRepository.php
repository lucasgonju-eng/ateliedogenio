<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\PaymentConfig;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\PaymentConfigRepositoryInterface;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class PaymentConfigRepository implements PaymentConfigRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    public function findByMethod(PaymentMethod $method): ?PaymentConfig
    {
        $value = $method->value;
        $columns = ['payment_method', 'method', 'name'];

        foreach ($columns as $col) {
            try {
                $response = $this->client->request('GET', 'rest/v1/payments_config', [
                    'headers' => ['Prefer' => 'single-object'],
                    'query' => [
                        $col => 'eq.' . $value,
                        'select' => '*',
                    ],
                ]);

                if ($response !== null) {
                    /** @var array<string,mixed> $row */
                    $row = is_array($response) && isset($response[0]) && is_array($response[0]) ? $response[0] : $response;
                    return $this->mapConfig($row);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            $all = $this->client->request('GET', 'rest/v1/payments_config', [
                'query' => ['select' => '*'],
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($all)) {
            return null;
        }

        /** @var list<array<string,mixed>> $rows */
        $rows = $all;
        if (!array_is_list($rows)) {
            $rows = [$rows];
        }

        foreach ($rows as $row) {
            $m = $row['payment_method'] ?? $row['method'] ?? $row['name'] ?? null;
            if (is_string($m) && $m === $value) {
                return $this->mapConfig($row);
            }
        }

        return null;
    }

    /**
     * @return list<PaymentConfig>
     */
    public function findAll(): array
    {
        $response = $this->client->request('GET', 'rest/v1/payments_config', [
            'query' => ['select' => '*'],
        ]);

        if ($response === null) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        return array_map(fn (array $row): PaymentConfig => $this->mapConfig($row), $rows);
    }

    public function upsert(
        PaymentMethod $method,
        float $feePercentage,
        float $feeFixed,
        bool $allowDiscount,
        float $maxDiscountPercentage
    ): PaymentConfig {
        $payload = [
            'payment_method' => $method->value,
            'fee_percentage' => round($feePercentage, 4),
            'fee_fixed' => round($feeFixed, 2),
            'allow_discount' => $allowDiscount,
            'max_discount_percentage' => round($maxDiscountPercentage, 2),
        ];

        $response = $this->client->runWithServiceRole(function () use ($payload) {
            return $this->client->request('POST', 'rest/v1/payments_config', [
                'query' => ['on_conflict' => 'payment_method'],
                'json' => [$payload],
                'headers' => ['Prefer' => 'return=representation,resolution=merge-duplicates'],
            ]);
        });

        if (!is_array($response) || $response === []) {
            return new PaymentConfig(
                id: $payload['payment_method'],
                method: $method,
                feePercentage: $payload['fee_percentage'],
                feeFixed: $payload['fee_fixed'],
                allowDiscount: $payload['allow_discount'],
                maxDiscountPercentage: $payload['max_discount_percentage'],
            );
        }

        /** @var array<string,mixed> $row */
        $row = is_array($response[0] ?? null) ? $response[0] : $response;

        return $this->mapConfig($row);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapConfig(array $row): PaymentConfig
    {
        $methodRaw = $row['payment_method'] ?? $row['method'] ?? $row['name'] ?? null;
        $method = is_string($methodRaw) ? (PaymentMethod::tryFrom($methodRaw) ?? PaymentMethod::PIX) : PaymentMethod::PIX;

        $feePercentage = (float) ($row['fee_percentage'] ?? $row['fee_percent'] ?? $row['percentage'] ?? 0);
        $feeFixed = (float) ($row['fee_fixed'] ?? $row['fixed_fee'] ?? 0);
        $allowDiscount = (bool) ($row['allow_discount'] ?? $row['discount_allowed'] ?? false);
        $maxDiscount = (float) ($row['max_discount_percentage'] ?? $row['max_discount_percent'] ?? $row['max_discount'] ?? 0);

        return new PaymentConfig(
            id: (string) ($row['id'] ?? ''),
            method: $method,
            feePercentage: $feePercentage,
            feeFixed: $feeFixed,
            allowDiscount: $allowDiscount,
            maxDiscountPercentage: $maxDiscount,
        );
    }
}


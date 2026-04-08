<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\PaymentFee;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\PaymentFeeRepositoryInterface;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class PaymentFeeRepository implements PaymentFeeRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    /**
     * @return list<PaymentFee>
     */
    public function find(?string $terminalId = null, ?string $brandId = null): array
    {
        $query = ['select' => '*', 'order' => 'payment_method.asc,installments_min.asc'];

        if ($terminalId) {
            $query['terminal_id'] = 'eq.' . $terminalId;
        }
        if ($brandId) {
            $query['brand_id'] = 'eq.' . $brandId;
        }

        $response = $this->client->request('GET', 'rest/v1/payment_fees', [
            'query' => $query,
        ]);

        if (!is_array($response)) {
            return [];
        }

        /** @var list<array<string,mixed>> $rows */
        $rows = $response;

        return array_map(fn (array $row) => $this->map($row), $rows);
    }

    public function upsert(
        ?string $id,
        string $terminalId,
        string $brandId,
        PaymentMethod $method,
        float $feePercentage,
        float $feeFixed,
        int $installmentsMin,
        int $installmentsMax,
        float $perInstallmentPercentage,
        float $confirmationFixedFee
    ): PaymentFee {
        $payload = [
            'terminal_id' => $terminalId,
            'brand_id' => $brandId,
            'payment_method' => $method->value,
            'fee_percentage' => round($feePercentage, 4),
            'fee_fixed' => round($feeFixed, 2),
            'installments_min' => max(1, (int)$installmentsMin),
            'installments_max' => max(1, (int)$installmentsMax),
            'per_installment_percentage' => round($perInstallmentPercentage, 4),
            'confirmation_fixed_fee' => round($confirmationFixedFee, 2),
        ];

        if ($id !== null && $id !== '') {
            $resp = $this->client->runWithServiceRole(function () use ($id, $payload) {
                return $this->client->request('PATCH', 'rest/v1/payment_fees', [
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
            return $this->client->request('POST', 'rest/v1/payment_fees', [
                'query' => ['on_conflict' => 'terminal_id,brand_id,payment_method,installments_min,installments_max'],
                'json' => [$payload],
                'headers' => ['Prefer' => 'return=representation,resolution=merge-duplicates'],
            ]);
        });

        /** @var array<string,mixed> $row */
        $row = is_array($resp) && isset($resp[0]) && is_array($resp[0]) ? $resp[0] : (is_array($resp) ? $resp : []);
        return $this->map($row);
    }

    /**
     * @param array<string,mixed> $row
     */
    private function map(array $row): PaymentFee
    {
        $methodRaw = (string)($row['payment_method'] ?? 'pix');
        $method = PaymentMethod::tryFrom(strtolower($methodRaw)) ?? PaymentMethod::PIX;

        return new PaymentFee(
            id: (string)($row['id'] ?? ''),
            terminalId: (string)($row['terminal_id'] ?? ''),
            brandId: (string)($row['brand_id'] ?? ''),
            method: $method,
            feePercentage: (float)($row['fee_percentage'] ?? 0),
            feeFixed: (float)($row['fee_fixed'] ?? 0),
            installmentsMin: (int)($row['installments_min'] ?? 1),
            installmentsMax: (int)($row['installments_max'] ?? 1),
            perInstallmentPercentage: (float)($row['per_installment_percentage'] ?? 0),
            confirmationFixedFee: (float)($row['confirmation_fixed_fee'] ?? 0)
        );
    }
}

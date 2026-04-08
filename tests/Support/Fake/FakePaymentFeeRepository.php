<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\PaymentFee;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\PaymentFeeRepositoryInterface;

final class FakePaymentFeeRepository implements PaymentFeeRepositoryInterface
{
    /**
     * @var array<string, PaymentFee>
     */
    private array $fees = [];

    /**
     * @return list<PaymentFee>
     */
    public function find(?string $terminalId = null, ?string $brandId = null): array
    {
        $items = array_values($this->fees);

        if ($terminalId !== null) {
            $items = array_values(array_filter($items, static fn (PaymentFee $fee): bool => $fee->terminalId() === $terminalId));
        }

        if ($brandId !== null) {
            $items = array_values(array_filter($items, static fn (PaymentFee $fee): bool => $fee->brandId() === $brandId));
        }

        return $items;
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
        $feeId = $id ?: sprintf('fee-%d', count($this->fees) + 1);

        $fee = new PaymentFee(
            id: $feeId,
            terminalId: $terminalId,
            brandId: $brandId,
            method: $method,
            feePercentage: round($feePercentage, 4),
            feeFixed: round($feeFixed, 2),
            installmentsMin: $installmentsMin,
            installmentsMax: $installmentsMax,
            perInstallmentPercentage: round($perInstallmentPercentage, 4),
            confirmationFixedFee: round($confirmationFixedFee, 2)
        );

        $this->fees[$feeId] = $fee;

        return $fee;
    }
}

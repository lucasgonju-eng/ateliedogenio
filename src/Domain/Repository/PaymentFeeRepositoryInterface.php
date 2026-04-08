<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\PaymentFee;
use AtelieDoGenio\Domain\Enum\PaymentMethod;

interface PaymentFeeRepositoryInterface
{
    /**
     * @return list<PaymentFee>
     */
    public function find(?string $terminalId = null, ?string $brandId = null): array;

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
    ): PaymentFee;
}

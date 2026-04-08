<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\PaymentConfig;
use AtelieDoGenio\Domain\Enum\PaymentMethod;

interface PaymentConfigRepositoryInterface
{
    public function findByMethod(PaymentMethod $method): ?PaymentConfig;

    /**
     * @return list<PaymentConfig>
     */
    public function findAll(): array;

    public function upsert(
        PaymentMethod $method,
        float $feePercentage,
        float $feeFixed,
        bool $allowDiscount,
        float $maxDiscountPercentage
    ): PaymentConfig;
}

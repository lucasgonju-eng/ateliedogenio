<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\Enum\PaymentMethod;

final class PaymentConfig
{
    public function __construct(
        private readonly string $id,
        private readonly PaymentMethod $method,
        private readonly float $feePercentage,
        private readonly float $feeFixed,
        private readonly bool $allowDiscount,
        private readonly float $maxDiscountPercentage
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function method(): PaymentMethod
    {
        return $this->method;
    }

    public function feePercentage(): float
    {
        return $this->feePercentage;
    }

    public function feeFixed(): float
    {
        return $this->feeFixed;
    }

    public function allowDiscount(): bool
    {
        return $this->allowDiscount;
    }

    public function maxDiscountPercentage(): float
    {
        return $this->maxDiscountPercentage;
    }
}


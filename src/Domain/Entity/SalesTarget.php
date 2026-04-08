<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\ValueObject\Money;

final class SalesTarget
{
    public function __construct(
        private readonly string $id,
        private readonly string $vendorId,
        private readonly \DateTimeImmutable $periodStart,
        private readonly \DateTimeImmutable $periodEnd,
        private readonly Money $goalAmount,
        private readonly float $commissionRate,
        private readonly \DateTimeImmutable $createdAt
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function vendorId(): string
    {
        return $this->vendorId;
    }

    public function periodStart(): \DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function periodEnd(): \DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function goalAmount(): Money
    {
        return $this->goalAmount;
    }

    public function commissionRate(): float
    {
        return $this->commissionRate;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function contains(\DateTimeImmutable $date): bool
    {
        return $date >= $this->periodStart && $date <= $this->periodEnd;
    }
}


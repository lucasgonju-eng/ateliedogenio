<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\Enum\CommissionStatus;
use AtelieDoGenio\Domain\ValueObject\Money;

final class Commission
{
    public function __construct(
        private readonly string $id,
        private readonly string $saleId,
        private readonly string $vendorId,
        private readonly Money $amount,
        private CommissionStatus $status,
        private readonly \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $paidAt = null
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function saleId(): string
    {
        return $this->saleId;
    }

    public function vendorId(): string
    {
        return $this->vendorId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function status(): CommissionStatus
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function paidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function markAsPaid(\DateTimeImmutable $when): void
    {
        $this->status = CommissionStatus::PAGA;
        $this->paidAt = $when;
    }
}


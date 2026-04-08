<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\ValueObject\Money;

final class CashLedgerEntry
{
    public function __construct(
        private readonly string $id,
        private readonly ?string $saleId,
        private readonly string $userId,
        private readonly PaymentMethod $method,
        private readonly Money $grossAmount,
        private readonly Money $feeAmount,
        private readonly Money $netAmount,
        private readonly string $entryType,
        private readonly \DateTimeImmutable $createdAt,
        private readonly ?string $note = null
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function saleId(): ?string
    {
        return $this->saleId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function method(): PaymentMethod
    {
        return $this->method;
    }

    public function grossAmount(): Money
    {
        return $this->grossAmount;
    }

    public function feeAmount(): Money
    {
        return $this->feeAmount;
    }

    public function netAmount(): Money
    {
        return $this->netAmount;
    }

    public function entryType(): string
    {
        return $this->entryType;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function note(): ?string
    {
        return $this->note;
    }
}

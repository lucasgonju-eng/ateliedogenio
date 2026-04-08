<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\Enum\InventoryMovementType;
use AtelieDoGenio\Domain\ValueObject\Money;

final class InventoryMovement
{
    public function __construct(
        private readonly string $id,
        private readonly string $productId,
        private readonly string $userId,
        private readonly InventoryMovementType $type,
        private readonly int $quantity,
        private readonly Money $unitCost,
        private readonly ?string $reference,
        private readonly \DateTimeImmutable $createdAt
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function type(): InventoryMovementType
    {
        return $this->type;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitCost(): Money
    {
        return $this->unitCost;
    }

    public function reference(): ?string
    {
        return $this->reference;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}


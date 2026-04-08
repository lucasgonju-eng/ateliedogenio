<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\ValueObject\Money;

final class SaleItem
{
    public function __construct(
        private readonly string $id,
        private readonly string $saleId,
        private readonly string $productId,
        private readonly int $quantity,
        private readonly Money $unitPrice,
        private readonly Money $unitCost,
        private readonly ?string $size = null,
        private readonly ?string $productName = null,
        private readonly ?string $productSku = null
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

    public function productId(): string
    {
        return $this->productId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function unitCost(): Money
    {
        return $this->unitCost;
    }

    public function size(): ?string
    {
        return $this->size;
    }

    public function productName(): ?string
    {
        return $this->productName;
    }

    public function productSku(): ?string
    {
        return $this->productSku;
    }

    public function lineTotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function lineCostTotal(): Money
    {
        return $this->unitCost->multiply($this->quantity);
    }
}



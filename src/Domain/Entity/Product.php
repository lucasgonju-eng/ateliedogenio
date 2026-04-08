<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\ValueObject\Money;

final class Product
{
    public function __construct(
        private readonly string $id,
        private readonly string $sku,
        private string $name,
        private ?string $description,
        private Money $supplierCost,
        private Money $salePrice,
        private int $stock,
        private int $minStockAlert,
        private bool $active = true
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function supplierCost(): Money
    {
        return $this->supplierCost;
    }

    public function salePrice(): Money
    {
        return $this->salePrice;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function minStockAlert(): int
    {
        return $this->minStockAlert;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function decreaseStock(int $quantity): void
    {
        $this->stock -= $quantity;
    }

    public function increaseStock(int $quantity): void
    {
        $this->stock += $quantity;
    }

    public function disable(): void
    {
        $this->active = false;
    }

    public function enable(): void
    {
        $this->active = true;
    }
}


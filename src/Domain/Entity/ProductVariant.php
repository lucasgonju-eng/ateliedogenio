<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

final class ProductVariant
{
    public function __construct(
        private readonly string $productId,
        private readonly string $size,
        private int $quantity
    ) {
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function size(): string
    {
        return $this->size;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = max(0, $quantity);
    }

    public function increase(int $amount): void
    {
        $this->setQuantity($this->quantity + max(0, $amount));
    }

    public function decrease(int $amount): void
    {
        $this->setQuantity($this->quantity - max(0, $amount));
    }
}

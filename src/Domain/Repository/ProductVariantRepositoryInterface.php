<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\ProductVariant;

interface ProductVariantRepositoryInterface
{
    /**
     * @return list<ProductVariant>
     */
    public function listByProduct(string $productId): array;

    public function getQuantity(string $productId, string $size): ?int;

    public function setQuantity(string $productId, string $size, int $quantity): void;

    public function increment(string $productId, string $size, int $delta): int;

    public function sumForProduct(string $productId): int;
}

<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\Product;

interface ProductRepositoryInterface
{
    public function findById(string $id): ?Product;

    public function findBySku(string $sku): ?Product;

    /**
     * @param array<string, mixed> $filters
     * @return list<Product>
     */
    public function search(array $filters = []): array;

    public function save(Product $product): void;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Product;
}

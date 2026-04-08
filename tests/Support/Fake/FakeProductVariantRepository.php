<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\ProductVariant;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;

final class FakeProductVariantRepository implements ProductVariantRepositoryInterface
{
    /**
     * @var array<string, array<string, int>>
     */
    private array $quantities = [];

    public function __construct(array $seed = [])
    {
        foreach ($seed as $productId => $sizes) {
            foreach ($sizes as $size => $quantity) {
                $this->setQuantity((string) $productId, (string) $size, (int) $quantity);
            }
        }

        if ($this->quantities === []) {
            $this->quantities['prod-1'] = [
                'P' => 5,
                'M' => 5,
                'G' => 5,
            ];
        }
    }

    public function listByProduct(string $productId): array
    {
        $sizes = $this->quantities[$productId] ?? [];

        return array_map(
            static fn (string $size, int $quantity) => new ProductVariant(
                productId: $productId,
                size: $size,
                quantity: $quantity
            ),
            array_keys($sizes),
            $sizes
        );
    }

    public function getQuantity(string $productId, string $size): int
    {
        return $this->quantities[$productId][$size] ?? 0;
    }

    public function setQuantity(string $productId, string $size, int $quantity): void
    {
        $this->quantities[$productId][$size] = max(0, $quantity);
    }

    public function increment(string $productId, string $size, int $delta): int
    {
        $current = $this->getQuantity($productId, $size);
        $new = max(0, $current + $delta);
        $this->quantities[$productId][$size] = $new;

        return $new;
    }

    public function sumForProduct(string $productId): int
    {
        return array_sum($this->quantities[$productId] ?? []);
    }
}

<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\ProductVariant;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class ProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    /**
     * @return list<ProductVariant>
     */
    public function listByProduct(string $productId): array
    {
        $response = $this->client->request('GET', 'rest/v1/product_variants', [
            'query' => [
                'product_id' => 'eq.' . $productId,
                'select' => '*',
                'order' => 'size.asc',
            ],
        ]);

        if (!is_array($response)) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        return array_map(
            static fn (array $row): ProductVariant => new ProductVariant(
                productId: $row['product_id'],
                size: $row['size'],
                quantity: (int) $row['quantity']
            ),
            $rows
        );
    }

    private function fetchVariantRow(string $productId, string $size): ?array
    {
        $response = $this->client->request('GET', 'rest/v1/product_variants', [
            'headers' => ['Prefer' => 'single-object'],
            'query' => [
                'product_id' => 'eq.' . $productId,
                'size' => 'eq.' . $size,
                'select' => '*',
            ],
        ]);

        if (!is_array($response)) {
            return null;
        }

        if ($response === [] || $response === [0 => null]) {
            return null;
        }

        if (isset($response[0]) && is_array($response[0])) {
            return $response[0];
        }

        return $response;
    }

    public function getQuantity(string $productId, string $size): ?int
    {
        $row = $this->fetchVariantRow($productId, $size);

        if (!is_array($row) || !array_key_exists('quantity', $row)) {
            return null;
        }

        return (int) $row['quantity'];
    }

    public function setQuantity(string $productId, string $size, int $quantity): void
    {
        $quantity = max(0, $quantity);

        $row = $this->fetchVariantRow($productId, $size);

        if ($row === null) {
            $this->client->request('POST', 'rest/v1/product_variants', [
                'json' => [[
                    'product_id' => $productId,
                    'size' => $size,
                    'quantity' => $quantity,
                ]],
                'headers' => [
                    'Prefer' => 'return=minimal',
                ],
            ]);

            return;
        }

        $this->client->request(
            'PATCH',
            sprintf(
                'rest/v1/product_variants?product_id=eq.%s&size=eq.%s',
                $productId,
                $size
            ),
            [
                'json' => ['quantity' => $quantity],
                'headers' => ['Prefer' => 'return=minimal'],
            ]
        );
    }

    public function increment(string $productId, string $size, int $delta): int
    {
        $current = $this->getQuantity($productId, $size) ?? 0;
        $newQuantity = max(0, $current + $delta);
        $this->setQuantity($productId, $size, $newQuantity);

        return $newQuantity;
    }

    public function sumForProduct(string $productId): int
    {
        $variants = $this->listByProduct($productId);

        return array_reduce(
            $variants,
            static fn (int $carry, ProductVariant $variant): int => $carry + $variant->quantity(),
            0
        );
    }
}

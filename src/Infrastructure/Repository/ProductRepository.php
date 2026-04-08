<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\Product;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    public function findById(string $id): ?Product
    {
        $response = $this->client->request('GET', 'rest/v1/products', [
            'headers' => ['Prefer' => 'single-object'],
            'query' => [
                'id' => 'eq.' . $id,
                'select' => '*',
            ],
        ]);

        if ($response === null) {
            return null;
        }

        $payload = is_array($response) && isset($response[0]) && is_array($response[0])
            ? $response[0]
            : $response;

        return $this->mapProduct($payload);
    }

    public function findBySku(string $sku): ?Product
    {
        $response = $this->client->request('GET', 'rest/v1/products', [
            'headers' => ['Prefer' => 'single-object'],
            'query' => [
                'sku' => 'eq.' . $sku,
                'select' => '*',
            ],
        ]);

        if ($response === null) {
            return null;
        }

        $payload = is_array($response) && isset($response[0]) && is_array($response[0])
            ? $response[0]
            : $response;

        return $this->mapProduct($payload);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<Product>
     */
    public function search(array $filters = []): array
    {
        $query = [
            'select' => '*',
            'order' => 'name.asc',
        ];

        if (($filters['search'] ?? null) !== null) {
            $search = '%' . $filters['search'] . '%';
            $query['or'] = sprintf('name.ilike.%s,sku.ilike.%s', $search, $search);
        }

        if (($filters['active'] ?? null) !== null) {
            $query['active'] = 'eq.' . ($filters['active'] ? 'true' : 'false');
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        $response = $this->client->request('GET', 'rest/v1/products', [
            'query' => $query,
        ]);

        if ($response === null) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        return array_map(fn (array $row): Product => $this->mapProduct($row), $rows);
    }

    public function save(Product $product): void
    {
        $payload = [
            'id' => $product->id(),
            'sku' => $product->sku(),
            'name' => $product->name(),
            'description' => $product->description(),
            'supplier_cost' => $product->supplierCost()->toFloat(),
            'sale_price' => $product->salePrice()->toFloat(),
            'stock' => $product->stock(),
            'min_stock_alert' => $product->minStockAlert(),
            'active' => $product->isActive(),
        ];

        try {
            $this->client->request('PATCH', 'rest/v1/products?id=eq.' . $product->id(), [
                'json' => $payload,
                'headers' => ['Prefer' => 'return=minimal'],
            ]);
        } catch (\RuntimeException $exception) {
            if ($this->isDuplicateSkuViolation($exception->getMessage())) {
                throw new BusinessRuleException(
                    'PRODUCT_SKU_ALREADY_EXISTS',
                    sprintf('SKU %s ja cadastrado. Edite o produto existente.', $product->sku())
                );
            }

            throw $exception;
        }
    }

    public function create(array $data): Product
    {
        $sku = trim((string) ($data['sku'] ?? ''));
        if ($sku !== '' && $this->findBySku($sku) !== null) {
            throw new BusinessRuleException(
                'PRODUCT_SKU_ALREADY_EXISTS',
                sprintf('SKU %s ja cadastrado. Edite o produto existente.', $sku)
            );
        }

        $payload = [
            'sku' => $sku,
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'supplier_cost' => $data['supplier_cost'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'stock' => $data['stock'] ?? 0,
            'min_stock_alert' => $data['min_stock_alert'] ?? 0,
            'active' => $data['active'] ?? true,
        ];

        try {
            $response = $this->client->request('POST', 'rest/v1/products', [
                'json' => $payload,
                'headers' => ['Prefer' => 'return=representation'],
            ]);
        } catch (\RuntimeException $exception) {
            if ($this->isDuplicateSkuViolation($exception->getMessage())) {
                throw new BusinessRuleException(
                    'PRODUCT_SKU_ALREADY_EXISTS',
                    sprintf('SKU %s ja cadastrado. Edite o produto existente.', $sku)
                );
            }

            throw $exception;
        }

        if ($response === null || !isset($response[0]) || !is_array($response[0])) {
            throw new \RuntimeException('Falha ao criar produto.');
        }

        /** @var array<string, mixed> $row */
        $row = $response[0];

        return $this->mapProduct($row);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapProduct(array $row): Product
    {
        return new Product(
            id: $row['id'],
            sku: $row['sku'],
            name: $row['name'],
            description: $row['description'] ?? null,
            supplierCost: Money::fromFloat((float) $row['supplier_cost']),
            salePrice: Money::fromFloat((float) $row['sale_price']),
            stock: (int) $row['stock'],
            minStockAlert: (int) $row['min_stock_alert'],
            active: (bool) $row['active']
        );
    }

    private function isDuplicateSkuViolation(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, '"code":"23505"')
            && str_contains($normalized, '(sku)');
    }
}

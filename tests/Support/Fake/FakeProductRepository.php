<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\Product;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;

final class FakeProductRepository implements ProductRepositoryInterface
{
    /**
     * @var array<string, Product>
     */
    private array $products = [];

    public function __construct()
    {
        $this->create([
            'id' => 'prod-1',
            'sku' => 'LAB-001',
            'name' => 'Kit de Experimentos',
            'description' => 'Produto fictício para testes.',
            'supplier_cost' => 50,
            'sale_price' => 100,
            'stock' => 10,
            'min_stock_alert' => 2,
            'active' => true,
        ]);
    }

    public function findById(string $id): ?Product
    {
        return $this->products[$id] ?? null;
    }

    public function findBySku(string $sku): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->sku() === $sku) {
                return $product;
            }
        }

        return null;
    }

    public function search(array $filters = []): array
    {
        return array_values($this->products);
    }

    public function save(Product $product): void
    {
        $this->products[$product->id()] = $product;
    }

    public function create(array $data): Product
    {
        $id = $data['id'] ?? uniqid('prod_', true);

        $product = new Product(
            id: $id,
            sku: (string) $data['sku'],
            name: (string) $data['name'],
            description: $data['description'] ?? null,
            supplierCost: Money::fromFloat((float) $data['supplier_cost']),
            salePrice: Money::fromFloat((float) $data['sale_price']),
            stock: (int) ($data['stock'] ?? 0),
            minStockAlert: (int) ($data['min_stock_alert'] ?? 0),
            active: isset($data['active']) ? (bool) $data['active'] : true
        );

        $this->products[$id] = $product;

        return $product;
    }
}


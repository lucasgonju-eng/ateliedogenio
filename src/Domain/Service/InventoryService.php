<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Entity\InventoryMovement;
use AtelieDoGenio\Domain\Enum\InventoryMovementType;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\InventoryMovementRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;

final class InventoryService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly InventoryMovementRepositoryInterface $movements
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<InventoryMovement>
     */
    public function listMovements(array $filters = []): array
    {
        return $this->movements->search($filters);
    }

    public function adjustStock(
        string $productId,
        InventoryMovementType $type,
        int $qty,
        float $unitCost,
        string $reference,
        string $userId
    ): InventoryMovement {
        if ($qty <= 0) {
            throw new BusinessRuleException('INVALID_QUANTITY', 'Quantidade precisa ser positiva.');
        }

        $product = $this->products->findById($productId);

        if ($product === null) {
            throw new BusinessRuleException('PRODUCT_NOT_FOUND', 'Produto nao encontrado.');
        }

        if ($type === InventoryMovementType::SAIDA && $product->stock() < $qty) {
            throw new BusinessRuleException('STOCK_CONFLICT', 'Nao ha estoque suficiente para realizar a saida.');
        }

        $movement = new InventoryMovement(
            id: self::generateUuid(),
            productId: $productId,
            userId: $userId,
            type: $type,
            quantity: $qty,
            unitCost: Money::fromFloat($unitCost),
            reference: $reference !== '' ? $reference : null,
            createdAt: new \DateTimeImmutable()
        );

        $this->movements->save($movement);

        if ($type === InventoryMovementType::ENTRADA) {
            $product->increaseStock($qty);
        } else {
            $product->decreaseStock($qty);
        }

        $this->products->save($product);

        return $movement;
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}


<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Entity\InventoryMovement;
use AtelieDoGenio\Domain\Enum\InventoryMovementType;
use AtelieDoGenio\Domain\Repository\InventoryMovementRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;

final class StockReconciliationService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductVariantRepositoryInterface $variants,
        private readonly InventoryMovementRepositoryInterface $movements,
        private readonly AuditLogger $auditLogger
    ) {
    }

    /**
     * @return array{
     *     checked:int,
     *     adjusted:int,
     *     adjustments:list<array{product_id:string, delta:int, variant_total:int, previous_stock:int}>
     * }
     */
    public function reconcileAll(?string $actorId = null, ?string $actorRole = 'system'): array
    {
        $limit = 200;
        $offset = 0;
        $checked = 0;
        $adjusted = 0;
        $adjustments = [];

        do {
            $batch = $this->products->search([
                'limit' => $limit,
                'offset' => $offset,
            ]);

            $batchCount = count($batch);
            $checked += $batchCount;

            foreach ($batch as $product) {
                $variantTotal = $this->variants->sumForProduct($product->id());
                $currentStock = $product->stock();
                $delta = $variantTotal - $currentStock;

                if ($delta === 0) {
                    continue;
                }

                if ($delta > 0) {
                    $product->increaseStock($delta);
                } else {
                    $product->decreaseStock(abs($delta));
                }

                $this->products->save($product);

                if ($actorId !== null) {
                    $movement = new InventoryMovement(
                        id: self::generateUuid(),
                        productId: $product->id(),
                        userId: $actorId,
                        type: InventoryMovementType::AJUSTE,
                        quantity: abs($delta),
                        unitCost: Money::fromFloat($product->supplierCost()->toFloat()),
                        reference: 'stock_reconciliation',
                        createdAt: new \DateTimeImmutable()
                    );

                    $this->movements->save($movement);
                }

                $this->auditLogger->record(
                    action: 'stock.reconciled',
                    entity: 'product',
                    entityId: $product->id(),
                    actorId: $actorId,
                    actorRole: $actorRole,
                    payload: [
                        'delta' => $delta,
                        'previous_stock' => $currentStock,
                        'variant_total' => $variantTotal,
                    ]
                );

                $adjustments[] = [
                    'product_id' => $product->id(),
                    'delta' => $delta,
                    'variant_total' => $variantTotal,
                    'previous_stock' => $currentStock,
                ];

                ++$adjusted;
            }

            $offset += $limit;
        } while ($batchCount === $limit);

        return [
            'checked' => $checked,
            'adjusted' => $adjusted,
            'adjustments' => $adjustments,
        ];
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

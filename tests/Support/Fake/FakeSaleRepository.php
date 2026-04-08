<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Entity\SaleItem;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Enum\SaleStatus;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;

final class FakeSaleRepository implements SaleRepositoryInterface
{
    /**
     * @var array<string, Sale>
     */
    private array $sales = [];

    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function findById(string $id): ?Sale
    {
        return $this->sales[$id] ?? null;
    }

    public function search(array $filters = []): array
    {
        $results = array_values($this->sales);

        if (isset($filters['user_id'])) {
            $results = array_filter($results, fn (Sale $sale) => $sale->userId() === $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $results = array_filter(
                $results,
                fn (Sale $sale) => $sale->status()->value === $filters['status']
            );
        }

        return array_values($results);
    }

    public function createDraft(string $userId, ?string $customerId, array $payload): Sale
    {
        $items = $payload['items'] ?? [];

        if (!is_array($items) || $items === []) {
            throw new \RuntimeException('Draft items payload is required for fake repository.');
        }

        $saleItems = [];
        $subtotalValue = (float) ($payload['subtotal'] ?? 0.0);
        $profitValue = (float) ($payload['profit_estimated'] ?? 0.0);

        $calculatedSubtotal = 0.0;
        $calculatedCost = 0.0;

        foreach ($items as $itemData) {
            $productId = $itemData['product_id'] ?? null;
            if (!is_string($productId) || $productId === '') {
                continue;
            }

            $product = $this->products->findById($productId);
            if ($product === null) {
                continue;
            }

            $quantity = (int) $itemData['qty'];
            $unitPrice = $itemData['unit_price'] ?? $product->salePrice()->toFloat();
            $unitCost = $itemData['unit_cost'] ?? $product->supplierCost()->toFloat();

            $saleItems[] = new SaleItem(
                id: uniqid('item_', true),
                saleId: 'pending',
                productId: $product->id(),
                quantity: $quantity,
                unitPrice: Money::fromFloat((float) $unitPrice),
                unitCost: Money::fromFloat((float) $unitCost)
            );

            $calculatedSubtotal += ((float) $unitPrice) * $quantity;
            $calculatedCost += ((float) $unitCost) * $quantity;
        }

        if ($subtotalValue <= 0.0) {
            $subtotalValue = $calculatedSubtotal;
        }

        if ($profitValue === 0.0) {
            $profitValue = $calculatedSubtotal - $calculatedCost;
        }

        $saleId = uniqid('sale_', true);
        $sale = new Sale(
            id: $saleId,
            userId: $userId,
            customerId: $customerId,
            status: SaleStatus::ABERTA,
            paymentMethod: null,
            items: $saleItems,
            subtotal: Money::fromFloat($subtotalValue),
            discountTotal: Money::fromFloat(0),
            feeTotal: Money::fromFloat(0),
            total: Money::fromFloat($subtotalValue),
            profitEstimated: Money::fromFloat($profitValue),
            createdAt: new \DateTimeImmutable()
        );

        $this->sales[$saleId] = $sale;

        return $sale;
    }

    public function updateStatus(string $saleId, SaleStatus $status): void
    {
        if (isset($this->sales[$saleId])) {
            $this->sales[$saleId]->applyStatus($status);
        }
    }

    public function registerPayment(string $saleId, array $paymentData): void
    {
        $sale = $this->sales[$saleId] ?? null;
        if ($sale === null) {
            return;
        }

        $paymentMethod = $paymentData['payment_method'];
        if (!$paymentMethod instanceof PaymentMethod && is_string($paymentMethod)) {
            $paymentMethod = PaymentMethod::from($paymentMethod);
        }

        $sale->assignPayment(
            $paymentMethod,
            Money::fromFloat((float) $paymentData['fee_total']),
            Money::fromFloat((float) $paymentData['total']),
            Money::fromFloat((float) ($paymentData['profit_estimated'] ?? $sale->profitEstimated()->toFloat()))
        );

        if (($paymentData['status'] ?? null) instanceof SaleStatus) {
            $sale->applyStatus($paymentData['status']);
        } elseif (isset($paymentData['status']) && is_string($paymentData['status'])) {
            $sale->applyStatus(SaleStatus::from($paymentData['status']));
        } else {
            $sale->applyStatus(SaleStatus::PAGA);
        }

        $this->sales[$saleId] = $sale;
    }

    public function saveItems(array $items): void
    {
        // No-op for fake repository.
    }
}

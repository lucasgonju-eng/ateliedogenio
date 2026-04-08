<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Entity\SaleItem;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Enum\SaleStatus;

interface SaleRepositoryInterface
{
    public function findById(string $id): ?Sale;

    /**
     * @param array<string, mixed> $filters
     * @return list<Sale>
     */
    public function search(array $filters = []): array;

    /**
     * @param array{
     *     items: list<array{product_id: string, qty: int, unit_price: float, unit_cost: float, line_total: float, line_cost: float}>,
     *     subtotal: float,
     *     profit_estimated: float
     * } $payload
     */
    public function createDraft(string $userId, ?string $customerId, array $payload): Sale;

    public function updateStatus(string $saleId, SaleStatus $status): void;

    /**
     * @param array{
     *     payment_method: PaymentMethod,
     *     subtotal: float,
     *     total: float,
     *     discount_total?: float,
     *     fee_total: float,
     *     profit_estimated?: float,
     *     status?: SaleStatus
     * } $paymentData
     */
    public function registerPayment(string $saleId, array $paymentData): void;

    /**
     * @param list<SaleItem> $items
     */
    public function saveItems(array $items): void;
}

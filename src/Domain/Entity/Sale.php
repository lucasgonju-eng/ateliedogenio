<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Enum\SaleStatus;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\ValueObject\Money;

final class Sale
{
    /**
     * @param list<SaleItem> $items
     */
    public function __construct(
        private readonly string $id,
        private readonly string $userId,
        private readonly ?string $customerId,
        private SaleStatus $status,
        private ?PaymentMethod $paymentMethod,
        private array $items,
        private Money $subtotal,
        private Money $discountTotal,
        private Money $feeTotal,
        private Money $total,
        private Money $profitEstimated,
        private readonly \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt = null
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function customerId(): ?string
    {
        return $this->customerId;
    }

    public function status(): SaleStatus
    {
        return $this->status;
    }

    public function paymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    /**
     * @return list<SaleItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function subtotal(): Money
    {
        return $this->subtotal;
    }

    public function discountTotal(): Money
    {
        return $this->discountTotal;
    }

    public function feeTotal(): Money
    {
        return $this->feeTotal;
    }

    public function total(): Money
    {
        return $this->total;
    }

    public function profitEstimated(): Money
    {
        return $this->profitEstimated;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function applyStatus(SaleStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function assignPayment(PaymentMethod $method, Money $fees, Money $total, Money $profit): void
    {
        $this->paymentMethod = $method;
        $this->feeTotal = $fees;
        $this->total = $total;
        $this->profitEstimated = $profit;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function ensureStatus(SaleStatus ...$allowedStatuses): void
    {
        if (!in_array($this->status, $allowedStatuses, true)) {
            throw new BusinessRuleException('INVALID_STATUS_TRANSITION', 'Operação não permitida para o status atual.');
        }
    }
}


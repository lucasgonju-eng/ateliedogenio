<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Entity\Commission;
use AtelieDoGenio\Domain\Entity\SalesTarget;
use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\CommissionStatus;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\CommissionRepositoryInterface;
use AtelieDoGenio\Domain\Repository\SalesTargetRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;

final class CommissionService
{
    public function __construct(
        private readonly CommissionRepositoryInterface $commissions,
        private readonly SalesTargetRepositoryInterface $targets,
        private readonly CashLedgerService $cashLedger
    ) {
    }

    public function registerSaleCommission(Sale $sale): void
    {
        $target = $this->targets->findActive($sale->userId(), $sale->createdAt());
        $rate = $target?->commissionRate() ?? 0.02; // fallback 2%

        if ($rate <= 0) {
            return;
        }

        // Comissao sobre o subtotal (valor bruto dos produtos)
        $amount = $sale->subtotal()->multiply($rate);

        if ($amount->toInt() <= 0) {
            return;
        }

        $commission = new Commission(
            id: self::generateUuid(),
            saleId: $sale->id(),
            vendorId: $sale->userId(),
            amount: $amount,
            status: CommissionStatus::PENDENTE,
            createdAt: new \DateTimeImmutable(),
        );

        $this->commissions->record($commission);

        // Lanca deducao no fluxo de caixa como ajuste negativo (nao interrompe a venda se falhar)
        try {
            $method = $sale->paymentMethod() ?? PaymentMethod::DINHEIRO;
            // Registra comissao como deducao: gross=0, fee=valor da comissao (net negativo)
            $this->cashLedger->registerAdjustment(
                method: $method,
                grossAmount: 0.0,
                feeAmount: $amount->toFloat(),
                note: 'commission',
                userId: $sale->userId(),
                saleId: $sale->id()
            );
        } catch (\Throwable) {
            // Ignora erro de ajuste no caixa para nao quebrar o checkout
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<Commission>
     */
    public function listCommissions(array $filters = []): array
    {
        return $this->commissions->search($filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<SalesTarget>
     */
    public function listTargets(array $filters = []): array
    {
        return $this->targets->search($filters);
    }

    public function upsertTarget(
        string $vendorId,
        \DateTimeImmutable $periodStart,
        \DateTimeImmutable $periodEnd,
        Money $goalAmount,
        float $commissionRate,
        ?SalesTarget $existing = null
    ): SalesTarget {
        $target = new SalesTarget(
            id: $existing?->id() ?? self::generateUuid(),
            vendorId: $vendorId,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            goalAmount: $goalAmount,
            commissionRate: $commissionRate,
            createdAt: $existing?->createdAt() ?? new \DateTimeImmutable()
        );

        return $this->targets->upsert($target);
    }

    public function markCommissionAsPaid(string $commissionId): void
    {
        $this->commissions->updateStatus($commissionId, CommissionStatus::PAGA, new \DateTimeImmutable());
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

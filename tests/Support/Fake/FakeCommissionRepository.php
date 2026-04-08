<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\Commission;
use AtelieDoGenio\Domain\Enum\CommissionStatus;
use AtelieDoGenio\Domain\Repository\CommissionRepositoryInterface;

final class FakeCommissionRepository implements CommissionRepositoryInterface
{
    /**
     * @var array<string, Commission>
     */
    private array $commissions = [];

    public function search(array $filters = []): array
    {
        $items = array_values($this->commissions);

        if (isset($filters['vendor_id'])) {
            $items = array_filter(
                $items,
                static fn (Commission $commission): bool => $commission->vendorId() === $filters['vendor_id']
            );
        }

        if (isset($filters['status'])) {
            $items = array_filter(
                $items,
                static fn (Commission $commission): bool => $commission->status()->value === $filters['status']
            );
        }

        return array_values($items);
    }

    public function record(Commission $commission): Commission
    {
        $this->commissions[$commission->id()] = $commission;

        return $commission;
    }

    public function updateStatus(string $commissionId, CommissionStatus $status, ?\DateTimeImmutable $paidAt): void
    {
        if (!isset($this->commissions[$commissionId])) {
            return;
        }

        $commission = $this->commissions[$commissionId];

        if ($status === CommissionStatus::PAGA && $paidAt !== null) {
            $commission->markAsPaid($paidAt);
        }

        $this->commissions[$commissionId] = $commission;
    }

    /**
     * @return list<Commission>
     */
    public function all(): array
    {
        return array_values($this->commissions);
    }
}

<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\Commission;
use AtelieDoGenio\Domain\Enum\CommissionStatus;

interface CommissionRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return list<Commission>
     */
    public function search(array $filters = []): array;

    public function record(Commission $commission): Commission;

    public function updateStatus(string $commissionId, CommissionStatus $status, ?\DateTimeImmutable $paidAt): void;
}

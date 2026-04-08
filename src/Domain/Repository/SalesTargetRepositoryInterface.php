<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\SalesTarget;

interface SalesTargetRepositoryInterface
{
    public function findActive(string $vendorId, \DateTimeImmutable $date): ?SalesTarget;

    /**
     * @param array<string, mixed> $filters
     * @return list<SalesTarget>
     */
    public function search(array $filters = []): array;

    public function upsert(SalesTarget $target): SalesTarget;
}

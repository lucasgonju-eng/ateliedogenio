<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\SalesTarget;
use AtelieDoGenio\Domain\Repository\SalesTargetRepositoryInterface;

final class FakeSalesTargetRepository implements SalesTargetRepositoryInterface
{
    /**
     * @var array<string, SalesTarget>
     */
    private array $targets = [];

    public function findActive(string $vendorId, \DateTimeImmutable $date): ?SalesTarget
    {
        $target = $this->targets[$vendorId] ?? null;

        if ($target === null) {
            return null;
        }

        return $target->contains($date) ? $target : null;
    }

    public function search(array $filters = []): array
    {
        $items = array_values($this->targets);

        if (isset($filters['vendor_id'])) {
            $items = array_filter(
                $items,
                static fn (SalesTarget $target): bool => $target->vendorId() === $filters['vendor_id']
            );
        }

        return array_values($items);
    }

    public function upsert(SalesTarget $target): SalesTarget
    {
        $this->targets[$target->vendorId()] = $target;

        return $target;
    }

    public function setActiveTarget(SalesTarget $target): void
    {
        $this->upsert($target);
    }
}

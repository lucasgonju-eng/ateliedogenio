<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\CashLedgerEntry;
use AtelieDoGenio\Domain\Repository\CashLedgerRepositoryInterface;

final class FakeCashLedgerRepository implements CashLedgerRepositoryInterface
{
    /**
     * @var array<string, CashLedgerEntry>
     */
    private array $entries = [];

    public function search(array $filters = []): array
    {
        return array_values($this->entries);
    }

    public function save(CashLedgerEntry $entry): void
    {
        $this->entries[$entry->id()] = $entry;
    }

    /**
     * @return list<CashLedgerEntry>
     */
    public function entries(): array
    {
        return array_values($this->entries);
    }
}

<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\CashLedgerEntry;

interface CashLedgerRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return list<CashLedgerEntry>
     */
    public function search(array $filters = []): array;

    public function save(CashLedgerEntry $entry): void;
}


<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\InventoryMovement;

interface InventoryMovementRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return list<InventoryMovement>
     */
    public function search(array $filters = []): array;

    public function save(InventoryMovement $movement): void;
}


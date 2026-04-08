<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\AuditLog;

interface AuditLogRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return list<AuditLog>
     */
    public function search(array $filters = []): array;

    public function record(AuditLog $entry): void;
}


<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Entity\AuditLog;
use AtelieDoGenio\Domain\Repository\AuditLogRepositoryInterface;

final class AuditLogger
{
    public function __construct(private readonly AuditLogRepositoryInterface $repository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function record(
        string $action,
        string $entity,
        ?string $entityId = null,
        ?string $actorId = null,
        ?string $actorRole = null,
        array $payload = []
    ): void {
        $entry = new AuditLog(
            id: self::generateUuid(),
            actorId: $actorId,
            actorRole: $actorRole,
            entity: $entity,
            entityId: $entityId,
            action: $action,
            payload: $payload,
            createdAt: new \DateTimeImmutable()
        );

        try {
            $this->repository->record($entry);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                'AUDIT_LOG_FAILURE action=%s entity=%s entity_id=%s reason=%s',
                $action,
                $entity,
                $entityId ?? 'null',
                $exception->getMessage()
            ));
        }
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

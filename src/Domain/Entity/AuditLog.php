<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

final class AuditLog
{
    public function __construct(
        private readonly string $id,
        private readonly ?string $actorId,
        private readonly ?string $actorRole,
        private readonly string $entity,
        private readonly ?string $entityId,
        private readonly string $action,
        private readonly array $payload,
        private readonly \DateTimeImmutable $createdAt
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function actorId(): ?string
    {
        return $this->actorId;
    }

    public function actorRole(): ?string
    {
        return $this->actorRole;
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function entityId(): ?string
    {
        return $this->entityId;
    }

    public function action(): string
    {
        return $this->action;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}


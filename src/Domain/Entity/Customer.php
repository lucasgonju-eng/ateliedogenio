<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

final class Customer
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private ?string $email,
        private ?string $phone,
        private ?string $document,
        private bool $isWalkIn
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function document(): ?string
    {
        return $this->document;
    }

    public function isWalkIn(): bool
    {
        return $this->isWalkIn;
    }
}


<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

final class User
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private string $email,
        private readonly string $role,
        private bool $active = true
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

    public function email(): string
    {
        return $this->email;
    }

    public function role(): string
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function activate(): void
    {
        $this->active = true;
    }
}


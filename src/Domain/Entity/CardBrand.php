<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

final class CardBrand
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly bool $active
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

    public function active(): bool
    {
        return $this->active;
    }
}


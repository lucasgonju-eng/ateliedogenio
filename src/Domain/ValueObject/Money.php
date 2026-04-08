<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\ValueObject;

use JsonSerializable;

final class Money implements JsonSerializable
{
    private function __construct(private readonly int $amountInCents)
    {
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public static function fromInt(int $amountInCents): self
    {
        return new self($amountInCents);
    }

    public function add(self $other): self
    {
        return new self($this->amountInCents + $other->amountInCents);
    }

    public function subtract(self $other): self
    {
        return new self($this->amountInCents - $other->amountInCents);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amountInCents * $factor));
    }

    public function percentage(float $percent): self
    {
        return $this->multiply($percent / 100);
    }

    public function compare(self $other): int
    {
        return $this->amountInCents <=> $other->amountInCents;
    }

    public function isNegative(): bool
    {
        return $this->amountInCents < 0;
    }

    public function toFloat(): float
    {
        return $this->amountInCents / 100;
    }

    public function toInt(): int
    {
        return $this->amountInCents;
    }

    public function jsonSerialize(): float
    {
        return $this->toFloat();
    }
}


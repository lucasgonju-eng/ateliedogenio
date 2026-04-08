<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Entity;

use AtelieDoGenio\Domain\Enum\PaymentMethod;

final class PaymentFee
{
    public function __construct(
        private readonly string $id,
        private readonly string $terminalId,
        private readonly string $brandId,
        private readonly PaymentMethod $method,
        private readonly float $feePercentage,
        private readonly float $feeFixed,
        private readonly int $installmentsMin,
        private readonly int $installmentsMax,
        private readonly float $perInstallmentPercentage,
        private readonly float $confirmationFixedFee
    ) {
    }

    public function id(): string { return $this->id; }
    public function terminalId(): string { return $this->terminalId; }
    public function brandId(): string { return $this->brandId; }
    public function method(): PaymentMethod { return $this->method; }
    public function feePercentage(): float { return $this->feePercentage; }
    public function feeFixed(): float { return $this->feeFixed; }
    public function installmentsMin(): int { return $this->installmentsMin; }
    public function installmentsMax(): int { return $this->installmentsMax; }
    public function perInstallmentPercentage(): float { return $this->perInstallmentPercentage; }
    public function confirmationFixedFee(): float { return $this->confirmationFixedFee; }
}

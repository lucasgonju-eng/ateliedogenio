<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\PaymentConfig;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\PaymentConfigRepositoryInterface;

final class FakePaymentConfigRepository implements PaymentConfigRepositoryInterface
{
    /**
     * @var array<string, PaymentConfig>
     */
    private array $configs = [];

    public function __construct()
    {
        foreach (PaymentMethod::cases() as $method) {
            $this->configs[$method->value] = new PaymentConfig(
                id: 'cfg-' . $method->value,
                method: $method,
                feePercentage: 2.0,
                feeFixed: 0.0,
                allowDiscount: true,
                maxDiscountPercentage: 10.0
            );
        }
    }

    public function findByMethod(PaymentMethod $method): ?PaymentConfig
    {
        return $this->configs[$method->value] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->configs);
    }

    public function upsert(
        PaymentMethod $method,
        float $feePercentage,
        float $feeFixed,
        bool $allowDiscount,
        float $maxDiscountPercentage
    ): PaymentConfig {
        $existing = $this->configs[$method->value] ?? null;
        $id = $existing?->id() ?? ('cfg-' . $method->value);

        $config = new PaymentConfig(
            id: $id,
            method: $method,
            feePercentage: round($feePercentage, 4),
            feeFixed: round($feeFixed, 2),
            allowDiscount: $allowDiscount,
            maxDiscountPercentage: round($maxDiscountPercentage, 2)
        );

        $this->configs[$method->value] = $config;

        return $config;
    }
}

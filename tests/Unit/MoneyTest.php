<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Unit;

use AtelieDoGenio\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testAddition(): void
    {
        $a = Money::fromFloat(10.50);
        $b = Money::fromFloat(5.25);

        $result = $a->add($b);

        $this->assertSame(1575, $result->toInt());
        $this->assertSame(15.75, $result->toFloat());
    }

    public function testPercentageCalculation(): void
    {
        $value = Money::fromFloat(200.00);
        $percentage = $value->percentage(2.5);

        $this->assertSame(500, $percentage->toInt());
        $this->assertSame(5.00, $percentage->toFloat());
    }

    public function testComparison(): void
    {
        $value = Money::fromFloat(100);
        $greater = Money::fromFloat(150);

        $this->assertSame(-1, $value->compare($greater));
        $this->assertSame(1, $greater->compare($value));
        $this->assertSame(0, $value->compare(Money::fromFloat(100)));
    }
}


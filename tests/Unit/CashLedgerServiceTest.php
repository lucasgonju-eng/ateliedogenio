<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Unit;

use AtelieDoGenio\Domain\Entity\CashLedgerEntry;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Service\CashLedgerService;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Tests\Support\Fake\FakeCashLedgerRepository;
use PHPUnit\Framework\TestCase;

final class CashLedgerServiceTest extends TestCase
{
    public function testSummarizeAggregatesTotalsByMethod(): void
    {
        $repository = new FakeCashLedgerRepository();
        $service = new CashLedgerService($repository);

        $now = new \DateTimeImmutable();

        $repository->save(new CashLedgerEntry(
            id: 'ledger-1',
            saleId: 'sale-1',
            userId: 'user-1',
            method: PaymentMethod::PIX,
            grossAmount: Money::fromFloat(100),
            feeAmount: Money::fromFloat(2),
            netAmount: Money::fromFloat(98),
            entryType: 'sale',
            createdAt: $now
        ));

        $repository->save(new CashLedgerEntry(
            id: 'ledger-2',
            saleId: 'sale-2',
            userId: 'user-1',
            method: PaymentMethod::PIX,
            grossAmount: Money::fromFloat(200),
            feeAmount: Money::fromFloat(4),
            netAmount: Money::fromFloat(196),
            entryType: 'sale',
            createdAt: $now
        ));

        $repository->save(new CashLedgerEntry(
            id: 'ledger-3',
            saleId: 'sale-3',
            userId: 'user-2',
            method: PaymentMethod::CREDITO,
            grossAmount: Money::fromFloat(150),
            feeAmount: Money::fromFloat(5),
            netAmount: Money::fromFloat(145),
            entryType: 'sale',
            createdAt: $now
        ));

        $summary = $service->summarize();

        $this->assertSame(3, $summary['count']);

        $this->assertEqualsWithDelta(450.0, $summary['totals']['gross'], 0.0001);
        $this->assertEqualsWithDelta(11.0, $summary['totals']['fee'], 0.0001);
        $this->assertEqualsWithDelta(439.0, $summary['totals']['net'], 0.0001);

        $this->assertArrayHasKey('pix', $summary['by_method']);
        $this->assertArrayHasKey('credito', $summary['by_method']);

        $pix = $summary['by_method']['pix'];
        $this->assertSame(2, $pix['count']);
        $this->assertEqualsWithDelta(300.0, $pix['gross'], 0.0001);
        $this->assertEqualsWithDelta(6.0, $pix['fee'], 0.0001);
        $this->assertEqualsWithDelta(294.0, $pix['net'], 0.0001);

        $credito = $summary['by_method']['credito'];
        $this->assertSame(1, $credito['count']);
        $this->assertEqualsWithDelta(150.0, $credito['gross'], 0.0001);
        $this->assertEqualsWithDelta(5.0, $credito['fee'], 0.0001);
        $this->assertEqualsWithDelta(145.0, $credito['net'], 0.0001);
    }
}

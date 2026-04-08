<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Integration;

use AtelieDoGenio\Domain\Entity\SalesTarget;
use AtelieDoGenio\Domain\Enum\CommissionStatus;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Service\CashLedgerService;
use AtelieDoGenio\Domain\Service\CommissionService;
use AtelieDoGenio\Domain\Service\SaleService;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Tests\Support\Fake\FakeCashLedgerRepository;
use AtelieDoGenio\Tests\Support\Fake\FakeCommissionRepository;
use AtelieDoGenio\Tests\Support\Fake\FakePaymentConfigRepository;
use AtelieDoGenio\Tests\Support\Fake\FakePaymentFeeRepository;
use AtelieDoGenio\Tests\Support\Fake\FakeProductRepository;
use AtelieDoGenio\Tests\Support\Fake\FakeProductVariantRepository;
use AtelieDoGenio\Tests\Support\Fake\FakeReceiptNotifier;
use AtelieDoGenio\Tests\Support\Fake\FakeSaleCheckoutGateway;
use AtelieDoGenio\Tests\Support\Fake\FakeSaleRepository;
use AtelieDoGenio\Tests\Support\Fake\FakeSalesTargetRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
final class SaleServiceTest extends TestCase
{
    private SaleService $service;
    private FakeSaleRepository $sales;
    private FakeCashLedgerRepository $ledger;
    private FakeCommissionRepository $commissions;
    private FakeSalesTargetRepository $targets;

    protected function setUp(): void
    {
        parent::setUp();

        $products = new FakeProductRepository();
        $this->sales = new FakeSaleRepository($products);
        $variants = new FakeProductVariantRepository();
        $paymentConfigs = new FakePaymentConfigRepository();
        $paymentFees = new FakePaymentFeeRepository();
        $notifier = new FakeReceiptNotifier();
        $checkout = new FakeSaleCheckoutGateway();
        $this->ledger = new FakeCashLedgerRepository();
        $this->commissions = new FakeCommissionRepository();
        $this->targets = new FakeSalesTargetRepository();

        $cashLedgerService = new CashLedgerService($this->ledger);
        $commissionService = new CommissionService($this->commissions, $this->targets, $cashLedgerService);

        $this->targets->setActiveTarget(new SalesTarget(
            id: 'target-1',
            vendorId: 'user-1',
            periodStart: new \DateTimeImmutable('-1 day'),
            periodEnd: new \DateTimeImmutable('+1 day'),
            goalAmount: Money::fromFloat(1000),
            commissionRate: 0.1,
            createdAt: new \DateTimeImmutable('-1 day')
        ));

        $this->service = new SaleService(
            $this->sales,
            $products,
            $variants,
            $paymentConfigs,
            $paymentFees,
            $notifier,
            $checkout,
            $commissionService,
            $cashLedgerService
        );
    }

    public function testFinalizeSaleCalculatesTotals(): void
    {
        $sale = $this->service->createDraft('user-1', null, [
            ['product_id' => 'prod-1', 'size' => 'M', 'qty' => 2],
        ]);

        $result = $this->service->finalizeSale($sale->id(), PaymentMethod::PIX, 0);

        $this->assertArrayHasKey('total', $result);
        $this->assertSame('paga', $this->sales->findById($sale->id())?->status()->value);
        $this->assertArrayHasKey('ledger_entry_id', $result);

        $entries = $this->ledger->entries();
        $this->assertCount(2, $entries);
        $saleEntry = null;
        $adjustmentEntry = null;
        foreach ($entries as $entry) {
            if ($entry->entryType() === 'sale') {
                $saleEntry = $entry;
            } elseif ($entry->entryType() === 'adjustment') {
                $adjustmentEntry = $entry;
            }
        }
        $this->assertNotNull($saleEntry);
        $this->assertSame($sale->id(), $saleEntry->saleId());
        $this->assertSame($result['total'], $saleEntry->netAmount()->toFloat());
        $this->assertNotNull($adjustmentEntry);

        $commissions = $this->commissions->all();
        $this->assertCount(1, $commissions);
        $commission = $commissions[0];
        $this->assertSame($sale->id(), $commission->saleId());
        $this->assertSame(CommissionStatus::PENDENTE->value, $commission->status()->value);
        $this->assertSame(
            round($result['total'] * 0.1, 2),
            $commission->amount()->toFloat()
        );
    }
}

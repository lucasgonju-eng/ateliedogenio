<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Enum\SaleStatus;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\PaymentConfigRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use AtelieDoGenio\Domain\Repository\PaymentFeeRepositoryInterface;
use AtelieDoGenio\Domain\Entity\PaymentFee;
use AtelieDoGenio\Domain\Service\CashLedgerService;
use AtelieDoGenio\Domain\Service\CommissionService;
use AtelieDoGenio\Domain\Service\ReceiptNotifierInterface;
use AtelieDoGenio\Domain\Service\SaleCheckoutGatewayInterface;
use AtelieDoGenio\Domain\ValueObject\Money;

final class SaleService
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly ProductRepositoryInterface $products,
        private readonly ProductVariantRepositoryInterface $productVariants,
        private readonly PaymentConfigRepositoryInterface $paymentConfigs,
        private readonly PaymentFeeRepositoryInterface $paymentFees,
        private readonly ReceiptNotifierInterface $receiptNotifier,
        private readonly SaleCheckoutGatewayInterface $checkoutGateway,
        private readonly CommissionService $commissionService,
        private readonly CashLedgerService $cashLedger,
    ) {
    }

    /**
     * @param list<array{product_id: string, size: string, qty: int}> $items
     */
    public function createDraft(string $userId, ?string $customerId, array $items): Sale
    {
        if ($items === []) {
            throw new BusinessRuleException('EMPTY_ITEMS', 'A venda precisa ter pelo menos um item.');
        }

        $preparedItems = [];
        $subtotal = Money::fromFloat(0);
        $totalCost = Money::fromFloat(0);

        /** @var array<string, \AtelieDoGenio\Domain\Entity\Product> $productCache */
        $productCache = [];
        $deductions = [];

        foreach ($items as $item) {
            $quantity = (int) ($item['qty'] ?? 0);
            if ($quantity < 1) {
                throw new BusinessRuleException('INVALID_QUANTITY', 'Quantidade de item deve ser positiva.');
            }

            $productId = (string) ($item['product_id'] ?? '');
            $size = isset($item['size']) ? trim((string) $item['size']) : '';

            if ($productId === '' || $size === '') {
                throw new BusinessRuleException('INVALID_ITEM', 'Produto e tamanho sao obrigatorios.');
            }

            $product = $productCache[$productId] ?? $this->products->findById($productId);
            if ($product === null) {
                throw new BusinessRuleException('PRODUCT_NOT_FOUND', 'Produto informado nao existe.');
            }

            $productCache[$productId] = $product;

            $availableForSize = $this->productVariants->getQuantity($productId, $size);
            if ($availableForSize < $quantity) {
                throw new BusinessRuleException(
                    'STOCK_CONFLICT',
                    sprintf('Estoque insuficiente para o tamanho %s do produto %s.', $size, $product->sku())
                );
            }

            if ($product->stock() < $quantity) {
                throw new BusinessRuleException(
                    'STOCK_CONFLICT',
                    'Estoque total insuficiente para o produto ' . $product->sku() . '.'
                );
            }

            $lineTotal = $product->salePrice()->multiply((float) $quantity);
            $lineCost = $product->supplierCost()->multiply((float) $quantity);

            $subtotal = $subtotal->add($lineTotal);
            $totalCost = $totalCost->add($lineCost);

            $preparedItems[] = [
                'product_id' => $product->id(),
                'size' => $size,
                'qty' => $quantity,
                'unit_price' => $product->salePrice()->toFloat(),
                'unit_cost' => $product->supplierCost()->toFloat(),
            ];

            $deductions[] = [
                'product_id' => $productId,
                'size' => $size,
                'qty' => $quantity,
            ];
        }

        $profit = $subtotal->subtract($totalCost);

        $sale = $this->sales->createDraft($userId, $customerId, [
            'items' => $preparedItems,
            'subtotal' => $subtotal->toFloat(),
            'profit_estimated' => $profit->toFloat(),
        ]);

        foreach ($deductions as $deduction) {
            $product = $productCache[$deduction['product_id']] ?? null;

            if ($product !== null) {
                $product->decreaseStock($deduction['qty']);
                $this->products->save($product);
            }

            $this->productVariants->increment($deduction['product_id'], $deduction['size'], -$deduction['qty']);
        }

        return $sale;
    }

        public function finalizeSale(string $saleId, PaymentMethod $method, float $discountPercent, string $actorRole = '', int $installments = 1, ?string $terminalId = null, ?string $brandId = null): array
    {
        $sale = $this->sales->findById($saleId);

        if ($sale === null) {
            throw new BusinessRuleException('SALE_NOT_FOUND', 'Venda nao encontrada.');
        }

        $sale->ensureStatus(SaleStatus::ABERTA, SaleStatus::PAGAMENTO_PENDENTE);

        // Modo simples temporário: trata todos os métodos como dinheiro no caixa (sem taxas)
        $appEnv = (string) ($_ENV['APP_ENV'] ?? '');
        $simpleMode = $appEnv === 'local' || ((string) ($_ENV['CHECKOUT_SIMPLE_MODE'] ?? '1')) === '1';

        // Tenta aplicar tabela por parcelas/maquininha/bandeira (se existir)
        $appliedByCatalog = false;
        $installments = max(1, (int) $installments);
        try {
            $rules = $this->paymentFees->find($terminalId, $brandId);
        } catch (\Throwable) {
            $rules = [];
        }

        $matched = $this->pickFeeRule($rules, $method, $installments);

        if ($simpleMode && $matched === null) {
            $subtotalValue = $sale->subtotal()->toFloat();
            $feeValue = 0.0;
            $discountPercent = max(0.0, min(100.0, $discountPercent));
            $discountValue = $discountPercent > 0 ? round($subtotalValue * ($discountPercent / 100), 2) : 0.0;
            $totalValue = max(0.0, $subtotalValue - $discountValue - $feeValue);

            // Ajusta lucro estimado considerando o desconto
            $profitOriginal = $sale->profitEstimated()->toFloat();
            $profitValue = max(0.0, $profitOriginal - $discountValue);

            $feeMoney = Money::fromFloat($feeValue);
            $totalMoney = Money::fromFloat($totalValue);
            $profitMoney = Money::fromFloat($profitValue);

            $sale->assignPayment($method, $feeMoney, $totalMoney, $profitMoney);
            $sale->applyStatus(SaleStatus::PAGA);

                        $this->sales->registerPayment($saleId, [
                'payment_method' => $method,
                'subtotal' => $subtotalValue,
                'total' => $totalValue,
                'discount_total' => $discountValue,
                'discount_percent' => $discountPercent,
                'fee_total' => $feeValue,
                'profit_estimated' => $profitValue,
                'status' => SaleStatus::PAGA,
            ]);


            $grossValue = max($totalValue + $feeValue, $subtotalValue - $discountValue);
            $grossMoney = Money::fromFloat($grossValue);
            $ledgerEntry = $this->cashLedger->registerSalePayment($sale, $method, $grossMoney, $feeMoney);

            if ($actorRole !== 'admin') {
                $this->commissionService->registerSaleCommission($sale);
            }

            return [
                'sale_id' => $sale->id(),
                'status' => SaleStatus::PAGA->value,
                'subtotal' => $subtotalValue,
                'discount_total' => $discountValue,
                'fees' => $feeValue,
                'total' => $totalValue,
                'profit_estimated' => $profitValue,
                'ledger_entry_id' => $ledgerEntry->id(),
                'payment_method' => $method->value,
            ];
        }

        // Caminho com tabela de parcelas/maquininha quando houver regra compatível
        if ($matched !== null) {
            $appliedByCatalog = true;
            $subtotalValue = $sale->subtotal()->toFloat();
            $discountPercent = max(0.0, min(100.0, $discountPercent));
            $discountValue = $discountPercent > 0 ? round($subtotalValue * ($discountPercent / 100), 2) : 0.0;

            $effectivePercent = $matched->feePercentage() + ($matched->perInstallmentPercentage() * $installments);
            $percentFee = round($subtotalValue * ($effectivePercent / 100.0), 2);
            $fixedFee = $matched->feeFixed() + $matched->confirmationFixedFee();
            $feeValue = round($percentFee + $fixedFee, 2);

            $totalValue = max(0.0, $subtotalValue - $discountValue - $feeValue);
            // Lucro estimado: desconta desconto e taxas
            $profitValue = max(0.0, $sale->profitEstimated()->toFloat() - $discountValue - $feeValue);

            $feeMoney = Money::fromFloat($feeValue);
            $totalMoney = Money::fromFloat($totalValue);
            $profitMoney = Money::fromFloat($profitValue);

            $sale->assignPayment($method, $feeMoney, $totalMoney, $profitMoney);
            $sale->applyStatus(SaleStatus::PAGA);

                        $this->sales->registerPayment($saleId, [
                'payment_method' => $method,
                'subtotal' => $subtotalValue,
                'total' => $totalValue,
                'discount_total' => $discountValue,
                'discount_percent' => $discountPercent,
                'fee_total' => $feeValue,
                'profit_estimated' => $profitValue,
                'status' => SaleStatus::PAGA,
            ]);


            $grossValue = max($totalValue + $feeValue, $subtotalValue - $discountValue);
            $grossMoney = Money::fromFloat($grossValue);
            $note = null;
            if ($method === PaymentMethod::CREDITO) {
                $note = 'installments=' . max(1, (int) $installments);
                if (isset($matched)) {
                    // opcional: anexa terminal/bandeira
                    $note .= ';terminal=' . $matched->terminalId() . ';brand=' . $matched->brandId();
                }
            }
            $ledgerEntry = $this->cashLedger->registerSalePayment($sale, $method, $grossMoney, $feeMoney, $note);

            if ($actorRole !== 'admin') {
                $this->commissionService->registerSaleCommission($sale);
            }

            return [
                'sale_id' => $sale->id(),
                'status' => SaleStatus::PAGA->value,
                'subtotal' => $subtotalValue,
                'discount_total' => $discountValue,
                'fees' => $feeValue,
                'total' => $totalValue,
                'profit_estimated' => $profitValue,
                'ledger_entry_id' => $ledgerEntry->id(),
                'payment_method' => $method->value,
                'applied_catalog_rule' => [
                    'installments' => $installments,
                    'terminal_id' => $matched->terminalId(),
                    'brand_id' => $matched->brandId(),
                    'fee_percentage' => $matched->feePercentage(),
                    'per_installment_percentage' => $matched->perInstallmentPercentage(),
                    'fee_fixed' => $matched->feeFixed(),
                    'confirmation_fixed_fee' => $matched->confirmationFixedFee(),
                ],
            ];
        }

        // Fluxo normal (com config e RPC) – mantido para quando ativarmos pagamentos reais
        $config = $this->paymentConfigs->findByMethod($method);
        $allowConfigFallback = $appEnv === 'local' || ((string) ($_ENV['ALLOW_PAYMENT_CONFIG_FALLBACK'] ?? '0')) === '1';

        if ($config === null && !$allowConfigFallback) {
            throw new BusinessRuleException('PAYMENT_CONFIG_NOT_FOUND', 'Configuracao de pagamento nao encontrada para o metodo selecionado.');
        }

        if ($discountPercent > 0) {
            if ($config !== null) {
                if (!$config->allowDiscount() || $discountPercent > $config->maxDiscountPercentage()) {
                    throw new BusinessRuleException('DISCOUNT_NOT_ALLOWED', 'Desconto acima do limite permitido.');
                }
            }
        }

        $subtotalValue = $sale->subtotal()->toFloat();
        $feeValue = 0.0;
        $discountValue = 0.0;
        $totalValue = 0.0;
        $profitValue = $sale->profitEstimated()->toFloat();
        $result = [];

        if ($config === null && $allowConfigFallback) {
            $discountValue = $discountPercent > 0 ? round($subtotalValue * ($discountPercent / 100), 2) : 0.0;
            $totalValue = max(0.0, $subtotalValue - $discountValue - $feeValue);
        } else {
            $payload = [
                '_sale_id' => $saleId,
                '_payment_method' => $method->value,
                '_discount_percent' => $discountPercent,
            ];
            try {
                $result = $this->checkoutGateway->finalizeSale($sale, $method, $payload);
                $subtotalValue = (float) ($result['subtotal'] ?? $sale->subtotal()->toFloat());
                $feeValue = (float) ($result['fees'] ?? $result['fee_total'] ?? 0.0);
                $discountValue = isset($result['discount_total']) ? (float) $result['discount_total'] : 0.0;
                if ($discountValue <= 0 && $discountPercent > 0) {
                    $discountValue = round($subtotalValue * ($discountPercent / 100), 2);
                }
                $totalValue = (float) ($result['total'] ?? ($subtotalValue - $discountValue - $feeValue));
                if ($totalValue < 0) { $totalValue = 0.0; }
                $profitValue = isset($result['profit_estimated']) ? (float) $result['profit_estimated'] : $sale->profitEstimated()->toFloat();
            } catch (\Throwable) {
                $result = [];
                $subtotalValue = $sale->subtotal()->toFloat();
                $feeValue = 0.0;
                $discountValue = $discountPercent > 0 ? round($subtotalValue * ($discountPercent / 100), 2) : 0.0;
                $totalValue = max(0.0, $subtotalValue - $discountValue - $feeValue);
                $profitValue = $sale->profitEstimated()->toFloat();
            }
        }

        $feeMoney = Money::fromFloat($feeValue);
        $totalMoney = Money::fromFloat($totalValue);
        $profitMoney = Money::fromFloat($profitValue);

        $sale->assignPayment($method, $feeMoney, $totalMoney, $profitMoney);
        $sale->applyStatus(SaleStatus::PAGA);

                    $this->sales->registerPayment($saleId, [
                'payment_method' => $method,
                'subtotal' => $subtotalValue,
                'total' => $totalValue,
                'discount_total' => $discountValue,
                'discount_percent' => $discountPercent,
                'fee_total' => $feeValue,
                'profit_estimated' => $profitValue,
                'status' => SaleStatus::PAGA,
            ]);


        $grossValue = isset($result['gross_amount'])
            ? (float) $result['gross_amount']
            : max($totalValue + $feeValue, $subtotalValue - $discountValue);

        $grossMoney = Money::fromFloat($grossValue);
        $note = null;
        if ($method === PaymentMethod::CREDITO) {
            // Usa o numero de parcelas informado na finalizacao
            $note = 'installments=' . max(1, (int) $installments);
        }
        $ledgerEntry = $this->cashLedger->registerSalePayment($sale, $method, $grossMoney, $feeMoney, $note);

        if ($actorRole !== 'admin') {
            $this->commissionService->registerSaleCommission($sale);
        }

        if (($result['receipt_email'] ?? null) !== null) {
            $this->receiptNotifier->sendReceipt($sale, $result['receipt_email']);
        }

        return array_merge($result, [
            'sale_id' => $sale->id(),
            'status' => SaleStatus::PAGA->value,
            'subtotal' => $subtotalValue,
            'discount_total' => $discountValue,
            'fees' => $feeValue,
            'total' => $totalValue,
            'profit_estimated' => $profitValue,
            'ledger_entry_id' => $ledgerEntry->id(),
            'payment_method' => $method->value,
        ]);
    }

    /**
     * @param list<PaymentFee> $rules
     */
    private function pickFeeRule(array $rules, PaymentMethod $method, int $installments): ?PaymentFee
    {
        if ($rules === []) {
            return null;
        }

        $filtered = array_values(array_filter($rules, function (PaymentFee $r) use ($method, $installments): bool {
            if ($r->method() !== $method) {
                return false;
            }
            return $installments >= $r->installmentsMin() && $installments <= $r->installmentsMax();
        }));

        if ($filtered !== []) {
            usort($filtered, fn (PaymentFee $a, PaymentFee $b) => $a->installmentsMin() <=> $b->installmentsMin());
            return $filtered[0];
        }

        // fallback: tenta achar 1x
        $fallback = array_values(array_filter($rules, function (PaymentFee $r) use ($method): bool {
            return $r->method() === $method && $r->installmentsMin() === 1 && $r->installmentsMax() === 1;
        }));
        if ($fallback !== []) {
            usort($fallback, fn (PaymentFee $a, PaymentFee $b) => $a->feePercentage() <=> $b->feePercentage());
            return $fallback[0];
        }

        return null;
    }
}

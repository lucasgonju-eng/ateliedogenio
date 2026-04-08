<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Service\SaleCheckoutGatewayInterface;

final class FakeSaleCheckoutGateway implements SaleCheckoutGatewayInterface
{
    public function finalizeSale(Sale $sale, PaymentMethod $method, array $payload): array
    {
        $subtotal = 0.0;
        $cost = 0.0;

        foreach ($sale->items() as $item) {
            $subtotal += $item->lineTotal()->toFloat();
            $cost += $item->lineCostTotal()->toFloat();
        }

        $fee = $subtotal * 0.02;
        $total = $subtotal - $fee;
        $profit = $total - $cost;

        return [
            'sale_id' => $sale->id(),
            'status' => 'paga',
            'subtotal' => $subtotal,
            'fees' => $fee,
            'total' => $total,
            'profit_estimated' => $profit,
            'receipt_sent' => true,
            'payment_method' => $method->value,
        ];
    }
}


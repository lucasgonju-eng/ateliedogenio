<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Email;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Service\ReceiptNotifierInterface;

final class SaleReceiptNotifier implements ReceiptNotifierInterface
{
    public function __construct(private readonly Mailer $mailer)
    {
    }

    public function sendReceipt(Sale $sale, string $recipientEmail): void
    {
        $subject = sprintf('Recibo da venda %s', $sale->id());
        $html = $this->buildHtmlReceipt($sale);
        $text = strip_tags($html);

        $this->mailer->send($recipientEmail, $subject, $html, $text);
    }

    private function buildHtmlReceipt(Sale $sale): string
    {
        $itemsRows = '';
        foreach ($sale->items() as $item) {
            $itemsRows .= sprintf(
                '<tr><td>%s</td><td class="text-right">%d</td><td class="text-right">R$ %0.2f</td><td class="text-right">R$ %0.2f</td></tr>',
                htmlspecialchars($item->productId(), ENT_QUOTES, 'UTF-8'),
                $item->quantity(),
                $item->unitPrice()->toFloat(),
                $item->lineTotal()->toFloat()
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Recibo</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1e293b; background: #f8fafc; padding: 24px; }
        h1 { color: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        th { text-align: left; background: #eff6ff; }
        .text-right { text-align: right; }
        .totals { margin-top: 16px; }
    </style>
</head>
<body>
    <h1>Ateliê do Gênio</h1>
    <p>Recibo da venda <strong>{$sale->id()}</strong></p>
    <p>Status: <strong>{$sale->status()->value}</strong></p>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th class="text-right">Qtd.</th>
                <th class="text-right">Preço Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            {$itemsRows}
        </tbody>
    </table>
    <div class="totals">
        <p>Subtotal: <strong>R$ {$sale->subtotal()->toFloat()}</strong></p>
        <p>Descontos: <strong>R$ {$sale->discountTotal()->toFloat()}</strong></p>
        <p>Taxas: <strong>R$ {$sale->feeTotal()->toFloat()}</strong></p>
        <p>Total: <strong>R$ {$sale->total()->toFloat()}</strong></p>
    </div>
</body>
</html>
HTML;
    }
}


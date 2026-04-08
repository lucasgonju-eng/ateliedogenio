<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;

final class ReportService
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly ProductRepositoryInterface $products,
        private readonly ProductVariantRepositoryInterface $variants
    ) {
    }

    /**
     * @return array{title:string, headers:list<string>, rows:list<array<int, string>>}
     */
    public function buildSalesReport(?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array
    {
        $filters = [
            'limit' => 1000,
        ];

        if ($from !== null) {
            $filters['from'] = $from->format(DATE_ATOM);
        }

        if ($to !== null) {
            $filters['to'] = $to->format(DATE_ATOM);
        }

        // Inclui itens para contar quantidades corretamente
        $filters['with_items'] = true;
        $sales = $this->sales->search($filters);

        $headers = [
            'Venda',
            'Data',
            'Status',
            'Método',
            'Subtotal (R$)',
            'Descontos (R$)',
            'Taxas (R$)',
            'Total (R$)',
            'Lucro Estimado (R$)',
            'Qtd. Itens',
        ];

        $rows = [];

        foreach ($sales as $sale) {
            $rows[] = [
                $sale->id(),
                $sale->createdAt()->format('Y-m-d H:i'),
                $this->statusLabel($sale->status()->value),
                $this->methodLabel($sale->paymentMethod()?->value ?? null),
                $this->formatCurrency($sale->subtotal()->toFloat()),
                $this->formatCurrency($this->safeDiscountValue($sale)),
                $this->formatCurrency($sale->feeTotal()->toFloat()),
                $this->formatCurrency($sale->total()->toFloat()),
                $this->formatCurrency($sale->profitEstimated()->toFloat()),
                (string) $this->countItems($sale),
            ];
        }

        return [
            'title' => 'Relatorio de Vendas',
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title:string, headers:list<string>, rows:list<array<int, string>>}
     */
    public function buildInventoryReport(?string $skuFilter = null, ?bool $onlyActive = null): array
    {
        $filters = [
            'limit' => 1000,
        ];
        if ($skuFilter !== null && trim($skuFilter) !== '') {
            $filters['search'] = $skuFilter;
        }
        if ($onlyActive !== null) {
            $filters['active'] = $onlyActive;
        }

        $products = $this->products->search($filters);

        $headers = [
            'Produto',
            'SKU',
            'Preço (R$)',
            'Custo (R$)',
            'Estoque Total',
            'Alerta Mínimo',
            'Ativo',
            'Variantes (tam=quantidade)',
        ];

        $rows = [];

        foreach ($products as $product) {
            $variants = $this->variants->listByProduct($product->id());
            $variantStrings = [];

            foreach ($variants as $variant) {
                $variantStrings[] = sprintf('%s=%d', $variant->size(), $variant->quantity());
            }

            $rows[] = [
                $product->name(),
                $product->sku(),
                $this->formatCurrency($product->salePrice()->toFloat()),
                $this->formatCurrency($product->supplierCost()->toFloat()),
                (string) $product->stock(),
                (string) $product->minStockAlert(),
                $product->isActive() ? 'Sim' : 'Não',
                $variantStrings === [] ? '-' : implode(' | ', $variantStrings),
            ];
        }

        return [
            'title' => 'Relatorio de Estoque',
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private function formatCurrency(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'aberta' => 'Aberta',
            'pagamento_pendente' => 'Pagamento pendente',
            'paga' => 'Paga',
            'entregue' => 'Entregue',
            'cancelada' => 'Cancelada',
            default => $status,
        };
    }

    private function methodLabel(?string $method): string
    {
        return match ($method) {
            'pix' => 'PIX',
            'credito' => 'Crédito',
            'debito' => 'Débito',
            'dinheiro' => 'Dinheiro',
            'transferencia' => 'Transferência',
            null => '-',
            default => $method,
        };
    }

    private function countItems(\AtelieDoGenio\Domain\Entity\Sale $sale): int
    {
        $items = $sale->items();
        if ($items === []) {
            return 0;
        }
        $sum = 0;
        foreach ($items as $item) { $sum += $item->quantity(); }
        return $sum;
    }

    private function safeDiscountValue(\AtelieDoGenio\Domain\Entity\Sale $sale): float
    {
        $discount = $sale->discountTotal()->toFloat();
        if ($discount > 0) {
            return $discount;
        }
        // Se o repositório não trouxe discount_total, estima a partir de subtotal, fees e total
        $subtotal = $sale->subtotal()->toFloat();
        $fees = $sale->feeTotal()->toFloat();
        $total = $sale->total()->toFloat();
        $estimated = max(0, $subtotal - $fees - $total);
        return $estimated;
    }
}


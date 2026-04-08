<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\Product;
use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\SaleStatus;
use AtelieDoGenio\Domain\Inventory\ProductSizeCatalog;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DashboardController extends BaseController
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly ProductRepositoryInterface $products
    ) {
    }

    public function vendorSummary(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->user($request);
        $sales = $this->sales->search(['user_id' => $user['id'], 'with_items' => true]);

        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $month = (new \DateTimeImmutable('first day of this month'))->format('Y-m');

        $todayTotal = 0.0;
        $monthTotal = 0.0;
        $openCount = 0;
        $kanbanItems = [];

        foreach ($sales as $sale) {
            $createdDate = $sale->createdAt()->format('Y-m-d');
            $createdMonth = $sale->createdAt()->format('Y-m');

            if ($createdDate === $today && $this->isSaleCompleted($sale)) {
                $todayTotal += $sale->total()->toFloat();
            }

            if ($createdMonth === $month && $this->isSaleCompleted($sale)) {
                $monthTotal += $sale->total()->toFloat();
            }

            if (in_array($sale->status(), [SaleStatus::ABERTA, SaleStatus::PAGAMENTO_PENDENTE], true)) {
                $openCount++;
            }

            $kanbanItems[] = [
                'id' => $sale->id(),
                'status' => $sale->status()->value,
                'subtotal' => $sale->subtotal()->toFloat(),
                'total' => $sale->total()->toFloat(),
                'created_at' => $sale->createdAt()->format(DATE_ATOM),
                'status_label' => $this->statusLabel($sale->status()),
                'label' => $this->humanLabel($sale),
                'items_summary' => $this->itemsSummary($sale),
                'short_code' => null,
            ];
        }

        return $this->json([
            'today_total' => $todayTotal,
            'month_total' => $monthTotal,
            'open_count' => $openCount,
            'kanban' => array_slice($kanbanItems, 0, 50),
        ]);
    }

    public function adminSummary(ServerRequestInterface $request): ResponseInterface
    {
        $sales = $this->sales->search();
        $grossRevenue = 0.0;
        $profit = 0.0;

        foreach ($sales as $sale) {
            $grossRevenue += $sale->total()->toFloat();
            $profit += $sale->profitEstimated()->toFloat();
        }

        $products = $this->products->search(['active' => true]);
        $lowStock = array_values(array_filter(array_map($this->presentLowStock(...), $products)));

        return $this->json([
            'gross_revenue' => $grossRevenue,
            'estimated_profit' => $profit,
            'low_stock' => $lowStock,
        ]);
    }

    private function isSaleCompleted(Sale $sale): bool
    {
        return in_array($sale->status(), [SaleStatus::PAGA, SaleStatus::ENTREGUE], true);
    }

    private function presentLowStock(Product $product): ?array
    {
        if ($product->stock() > $product->minStockAlert()) {
            return null;
        }

        return [
            'sku' => $product->sku(),
            'stock' => $product->stock(),
        ];
    }

    private function statusLabel(SaleStatus $status): string
    {
        return match ($status) {
            SaleStatus::ABERTA => 'Aberta',
            SaleStatus::PAGAMENTO_PENDENTE => 'Pagamento pendente',
            SaleStatus::PAGA => 'Paga',
            SaleStatus::ENTREGUE => 'Entregue',
            SaleStatus::CANCELADA => 'Cancelada',
        };
    }

    /**
     * @return array<int, string>
     */
    private function itemsSummary(Sale $sale): array
    {
        $lines = [];
        foreach ($sale->items() as $item) {
            $name = $item->productName() ?: ($item->productSku() ? ('SKU ' . $item->productSku()) : 'Produto');
            $size = $item->size() ?? '';
            $qty = $item->quantity();

            $group = $this->resolveSizeGroup($size);

            if ($group !== '' && $size !== '') {
                $lines[] = sprintf('%s — %s %s x%d', $name, $group, $size, $qty);
            } elseif ($size !== '') {
                $lines[] = sprintf('%s — %s x%d', $name, $size, $qty);
            } else {
                $lines[] = sprintf('%s — x%d', $name, $qty);
            }
        }

        return $lines;
    }

    private function humanLabel(Sale $sale): string
    {
        $items = $sale->items();
        if ($items === []) {
            return sprintf('Venda %s', $sale->createdAt()->format('d/m H:i'));
        }

        // Agrupa por produto e consolida quantidades por tamanho
        $groups = [];
        foreach ($items as $item) {
            $productId = $item->productId();
            if (!isset($groups[$productId])) {
                $model = $item->productName() ?: ($item->productSku() ? ('SKU ' . $item->productSku()) : 'Modelo');
                $groups[$productId] = [
                    'model' => $model,
                    'sizes' => [], // [ [group, size, qty] ]
                ];
            }

            $size = $item->size() ?? '';
            $qty = $item->quantity();

            $group = $this->resolveSizeGroup($size);

            // agrega por chave (group+size)
            $key = ($group !== '' ? ($group . '|') : '|') . $size;
            $found = false;
            foreach ($groups[$productId]['sizes'] as &$entry) {
                if ($entry['key'] === $key) {
                    $entry['qty'] += $qty;
                    $found = true;
                    break;
                }
            }
            unset($entry);
            if (!$found) {
                $groups[$productId]['sizes'][] = [
                    'key' => $key,
                    'group' => $group,
                    'size' => $size,
                    'qty' => $qty,
                ];
            }
        }

        // Constrói segmentos por produto, com lista compacta de tamanhos
        $segments = [];
        foreach ($groups as $product) {
            $model = $product['model'];
            $sizes = $product['sizes'];

            // ordena tamanhos alfabeticamente para consistência
            usort($sizes, static function ($a, $b) {
                return strcmp(($a['group'] . $a['size']), ($b['group'] . $b['size']));
            });

            $sizeTokens = [];
            foreach ($sizes as $idx => $s) {
                $token = '';
                if ($s['group'] !== '' && $s['size'] !== '') {
                    $token = sprintf('%s %s %dUn', $s['group'], $s['size'], $s['qty']);
                } elseif ($s['size'] !== '') {
                    $token = sprintf('%s %dUn', $s['size'], $s['qty']);
                } else {
                    $token = sprintf('%dUn', $s['qty']);
                }
                $sizeTokens[] = $token;
            }

            $displaySizes = array_slice($sizeTokens, 0, 2);
            $remainingSizes = max(0, count($sizeTokens) - count($displaySizes));
            $segment = $model . ' ' . implode(', ', $displaySizes);
            if ($remainingSizes > 0) {
                $segment .= sprintf(' +%d itens', $remainingSizes);
            }

            $segments[] = $segment;
        }

        // Limita exibição a dois produtos; agrega o restante
        $display = array_slice($segments, 0, 2);
        $remaining = max(0, count($segments) - count($display));
        $label = implode(' + ', $display);
        if ($remaining > 0) {
            $label .= sprintf(' +%d itens', $remaining);
        }

        return $label !== '' ? $label : sprintf('Venda %s (%d it.)', $sale->createdAt()->format('d/m H:i'), count($items));
    }

    private function resolveSizeGroup(string $size): string
    {
        if ($size === '') {
            return '';
        }

        if (in_array($size, ProductSizeCatalog::feminine(), true)) {
            return 'Fem';
        }

        if (in_array($size, ProductSizeCatalog::masculine(), true) || in_array($size, ProductSizeCatalog::legacy(), true)) {
            return 'Masc';
        }

        return '';
    }
}

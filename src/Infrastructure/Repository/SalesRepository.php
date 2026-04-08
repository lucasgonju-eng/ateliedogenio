<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Entity\SaleItem;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Enum\SaleStatus;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class SalesRepository implements SaleRepositoryInterface
{
    private const DEFAULT_CUSTOMER_ID = '00000000-0000-0000-0000-000000000001';

    public function __construct(
        private readonly SupabaseClient $client,
    ) {
    }

    public function findById(string $id): ?Sale
    {
        $response = $this->client->request('GET', 'rest/v1/sales', [
            'headers' => ['Prefer' => 'single-object'],
            'query' => [
                'id' => 'eq.' . $id,
                'select' => '*,sale_items(id,sale_id,product_id,qty,size,unit_price,supplier_cost,product:products(name,sku))',
            ],
        ]);

        if ($response === null) {
            return null;
        }

        $payload = is_array($response) && isset($response[0]) && is_array($response[0])
            ? $response[0]
            : $response;

        return $this->mapSale($payload);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<Sale>
     */
    public function search(array $filters = []): array
    {
        $withItems = (bool) ($filters['with_items'] ?? false);

        $select = 'id,vendor_id,status,payment_method,subtotal,discount_total,total,fees,profit_estimated,created_at';
        if ($withItems) {
            $select .= ',sale_items(id,sale_id,product_id,qty,size,unit_price,supplier_cost,product:products(name,sku))';
        }

        $query = [
            'select' => $select,
            'order' => 'created_at.desc',
        ];

        if (!empty($filters['ids']) && is_array($filters['ids'])) {
            $ids = array_values(array_unique(array_filter($filters['ids'], static fn ($v) => is_string($v) && $v !== '')));
            if ($ids !== []) {
                $quoted = array_map(static fn (string $v): string => sprintf('"%s"', $v), $ids);
                $query['id'] = 'in.(' . implode(',', $quoted) . ')';
            }
        }

        if (($filters['user_id'] ?? null) !== null) {
            $query['vendor_id'] = 'eq.' . $filters['user_id'];
        }

        if (($filters['status'] ?? null) !== null) {
            $query['status'] = 'eq.' . $filters['status'];
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        $andFilters = [];

        if (($filters['from'] ?? null) !== null) {
            $andFilters[] = sprintf('created_at.gte.%s', $filters['from']);
        }

        if (($filters['to'] ?? null) !== null) {
            $andFilters[] = sprintf('created_at.lte.%s', $filters['to']);
        }

        if ($andFilters !== []) {
            $query['and'] = '(' . implode(',', $andFilters) . ')';
        }

        try {
            $response = $this->client->request('GET', 'rest/v1/sales', [
                'query' => $query,
            ]);
        } catch (\RuntimeException $exception) {
            if (stripos($exception->getMessage(), 'discount_total') === false) {
                throw $exception;
            }

            $query['select'] = $this->removeColumnFromSelect($query['select'], 'discount_total');

            $response = $this->client->request('GET', 'rest/v1/sales', [
                'query' => $query,
            ]);
        }

        if ($response === null) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        $includeItems = $withItems;

        return array_map(fn (array $row): Sale => $this->mapSale($row, includeItems: $includeItems), $rows);
    }

    public function createDraft(string $userId, ?string $customerId, array $payload): Sale
    {
        $items = $payload['items'] ?? [];

        if (!is_array($items) || $items === []) {
            throw new \RuntimeException('Draft items payload is invalid.');
        }

        $subtotalValue = (float) ($payload['subtotal'] ?? 0);
        $profitValue = (float) ($payload['profit_estimated'] ?? 0);

        if ($subtotalValue <= 0) {
            foreach ($items as $item) {
                $subtotalValue += (float) ($item['unit_price'] ?? 0) * (int) ($item['qty'] ?? 0);
            }
        }

        if ($profitValue === 0.0) {
            foreach ($items as $item) {
                $profitValue += ((float) ($item['unit_price'] ?? 0) - (float) ($item['unit_cost'] ?? 0)) * (int) ($item['qty'] ?? 0);
            }
        }

        $resolvedCustomerId = $customerId ?? self::DEFAULT_CUSTOMER_ID;

        $salePayload = [
            'vendor_id' => $userId,
            'customer_id' => $resolvedCustomerId,
            'status' => SaleStatus::ABERTA->value,
            'payment_method' => null,
            'discount_percent' => 0,
            'subtotal' => $subtotalValue,
            'total' => $subtotalValue,
            'fees' => 0,
            'profit_estimated' => $profitValue,
        ];

        $insertResponse = $this->client->runWithServiceRole(function () use ($salePayload) {
            return $this->client->request('POST', 'rest/v1/sales', [
                'json' => [$salePayload],
                'headers' => ['Prefer' => 'return=representation'],
            ]);
        });

        if (!is_array($insertResponse) || !isset($insertResponse[0]) || !is_array($insertResponse[0])) {
            throw new \RuntimeException('Failed to persist sale draft.');
        }

        /** @var array<string, mixed> $saleRow */
        $saleRow = $insertResponse[0];

        $saleId = $saleRow['id'] ?? null;

        if (!is_string($saleId) || $saleId === '') {
            throw new \RuntimeException('Sale draft created without identifier.');
        }

        $itemPayload = [];
        foreach ($items as $item) {
            $itemPayload[] = [
                'sale_id' => $saleId,
                'product_id' => $item['product_id'],
                'size' => $item['size'] ?? null,
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'supplier_cost' => $item['unit_cost'],
            ];
        }

        if ($itemPayload !== []) {
            $this->client->runWithServiceRole(function () use ($itemPayload) {
                $this->client->request('POST', 'rest/v1/sale_items', [
                    'json' => $itemPayload,
                    'headers' => ['Prefer' => 'return=minimal'],
                ]);
            });
        }

        return $this->findById($saleId) ?? $this->mapSale($saleRow);
    }

    public function updateStatus(string $saleId, SaleStatus $status): void
    {
        $this->client->request('PATCH', 'rest/v1/sales?id=eq.' . $saleId, [
            'json' => ['status' => $status->value],
            'headers' => ['Prefer' => 'return=minimal'],
        ]);
    }

    /**
     * @param array<string, mixed> $paymentData
     */
    public function registerPayment(string $saleId, array $paymentData): void
    {
        // Tenta enviar discount_total (se a coluna existir). Se o Supabase retornar 42703, faz retry sem este campo.
        $basePayload = [
            'payment_method' => $paymentData['payment_method'] instanceof PaymentMethod
                ? $paymentData['payment_method']->value
                : $paymentData['payment_method'],
            'total' => $paymentData['total'],
            'fees' => $paymentData['fee_total'],
            'profit_estimated' => $paymentData['profit_estimated'] ?? null,
        ];

        if (($paymentData['status'] ?? null) instanceof SaleStatus) {
            $basePayload['status'] = $paymentData['status']->value;
        } elseif (isset($paymentData['status']) && is_string($paymentData['status'])) {
            $basePayload['status'] = $paymentData['status'];
        }

        $withDiscount = $basePayload;
        if (isset($paymentData['discount_total'])) {
            $withDiscount['discount_total'] = $paymentData['discount_total'];
        }

        $payload = array_filter(
            $withDiscount,
            static fn ($value) => $value !== null
        );

        try {
            $this->client->request('PATCH', 'rest/v1/sales?id=eq.' . $saleId, [
                'json' => $payload,
                'headers' => ['Prefer' => 'return=minimal'],
            ]);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            // Se a resposta indicar que a coluna discount_total não existe (PGRST204, 42703 ou texto), reenvia sem ela
            if (stripos($message, 'discount_total') !== false) {
                $fallback = array_filter(
                    $basePayload,
                    static fn ($value) => $value !== null
                );

                $this->client->request('PATCH', 'rest/v1/sales?id=eq.' . $saleId, [
                    'json' => $fallback,
                    'headers' => ['Prefer' => 'return=minimal'],
                ]);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param list<SaleItem> $items
     */
    public function saveItems(array $items): void
    {
        $payload = array_map(static function (SaleItem $item): array {
            return [
                'id' => $item->id(),
                'sale_id' => $item->saleId(),
                'product_id' => $item->productId(),
                'size' => $item->size(),
                'qty' => $item->quantity(),
                'unit_price' => $item->unitPrice()->toFloat(),
                'supplier_cost' => $item->unitCost()->toFloat(),
            ];
        }, $items);

        $this->client->request('POST', 'rest/v1/sale_items', [
            'json' => $payload,
            'headers' => ['Prefer' => 'return=minimal'],
        ]);
    }

    private function removeColumnFromSelect(string $select, string $column): string
    {
        $pattern = sprintf('/(^|,)%s(?=,|$)/', preg_quote($column, '/'));
        $clean = preg_replace($pattern, '$1', $select);
        if ($clean === null) {
            return $select;
        }

        $clean = preg_replace('/,{2,}/', ',', $clean) ?? $clean;

        return trim($clean, ',');
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapSale(array $row, bool $includeItems = true): Sale
    {
        $status = SaleStatus::from($row['status']);
        $paymentMethod = isset($row['payment_method']) && $row['payment_method'] !== null
            ? PaymentMethod::from($row['payment_method'])
            : null;

        $items = [];

        if ($includeItems && isset($row['sale_items']) && is_array($row['sale_items'])) {
            /** @var list<array<string, mixed>> $itemRows */
            $itemRows = $row['sale_items'];

            $items = array_map(function (array $itemRow): SaleItem {
                $product = is_array($itemRow['product'] ?? null) ? $itemRow['product'] : null;
                $productName = is_array($product) ? ($product['name'] ?? null) : null;
                $productSku = is_array($product) ? ($product['sku'] ?? null) : null;

                return new SaleItem(
                    id: $itemRow['id'],
                    saleId: $itemRow['sale_id'],
                    productId: $itemRow['product_id'],
                    quantity: (int) ($itemRow['qty'] ?? $itemRow['quantity']),
                    unitPrice: Money::fromFloat((float) $itemRow['unit_price']),
                    unitCost: Money::fromFloat((float) ($itemRow['supplier_cost'] ?? $itemRow['unit_cost'])),
                    size: $itemRow['size'] ?? null,
                    productName: is_string($productName) ? $productName : null,
                    productSku: is_string($productSku) ? $productSku : null
                );
            }, $itemRows);
        }

        $feeValue = $row['fee_total'] ?? $row['fees'] ?? 0;
        $discountValue = $row['discount_total'] ?? 0;

        $userId = $row['vendor_id'] ?? $row['user_id'] ?? '';

        return new Sale(
            id: $row['id'],
            userId: $userId,
            customerId: $row['customer_id'] ?? null,
            status: $status,
            paymentMethod: $paymentMethod,
            items: $items,
            subtotal: Money::fromFloat((float) ($row['subtotal'] ?? 0)),
            discountTotal: Money::fromFloat((float) $discountValue),
            feeTotal: Money::fromFloat((float) $feeValue),
            total: Money::fromFloat((float) ($row['total'] ?? 0)),
            profitEstimated: Money::fromFloat((float) ($row['profit_estimated'] ?? 0)),
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: isset($row['updated_at']) ? new \DateTimeImmutable($row['updated_at']) : null
        );
    }
}

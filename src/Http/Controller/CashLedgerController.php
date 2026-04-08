<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\CashLedgerEntry;
use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use AtelieDoGenio\Domain\Service\CashLedgerService;
use AtelieDoGenio\Infrastructure\Report\ReportExporter;
use DateTimeImmutable;
use DateTimeZone;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CashLedgerController extends BaseController
{
    public function __construct(
        private readonly CashLedgerService $ledger,
        private readonly SaleRepositoryInterface $sales,
        private readonly ReportExporter $exporter,
        private readonly Psr17Factory $responseFactory
    )
    {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $filters = [];

        foreach (['from', 'to', 'payment_method', 'entry_type', 'limit', 'offset'] as $filter) {
            if (isset($query[$filter])) {
                $filters[$filter] = $query[$filter];
            }
        }

        $entries = $this->ledger->search($filters);
        $saleLabels = $this->buildSaleLabels($entries);

        $items = array_map(function (CashLedgerEntry $entry) use ($saleLabels) {
            $label = null;
            if ($entry->saleId() !== null) {
                $label = $saleLabels[$entry->saleId()] ?? null;
            }

            return $this->presentEntry($entry, $label);
        }, $entries);

        $response = $this->json([
            'items' => $items,
            'count' => count($items),
        ]);

        return $response->withHeader('X-Total-Count', (string) count($items));
    }

    public function adjust(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $user = $this->user($request);

        foreach (['payment_method', 'gross_amount'] as $field) {
            if (!isset($payload[$field])) {
                return $this->error('VALIDATION_ERROR', sprintf('Campo %s é obrigatório.', $field), 422);
            }
        }

        try {
            $method = PaymentMethod::from((string) $payload['payment_method']);
        } catch (\ValueError) {
            return $this->error('VALIDATION_ERROR', 'Método de pagamento inválido.', 422);
        }

        $createdAt = null;
        try {
            if (isset($payload['created_at'])) {
                $createdAt = $this->parseDate($payload['created_at']);
            } elseif (isset($payload['date'])) {
                $createdAt = $this->parseDate($payload['date']);
            }
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        }

        try {
            $entry = $this->ledger->registerAdjustment(
                method: $method,
                grossAmount: (float) $payload['gross_amount'],
                feeAmount: isset($payload['fee_amount']) ? (float) $payload['fee_amount'] : 0.0,
                note: $payload['note'] ?? null,
                userId: $user['id'],
                createdAt: $createdAt
            );
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('LEDGER_ERROR', $exception->getMessage(), 500);
        }

        return $this->json($this->presentEntry($entry), 201);
    }

    public function summary(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $filters = [];

        foreach (['from', 'to', 'payment_method', 'entry_type'] as $filter) {
            if (isset($query[$filter]) && $query[$filter] !== '') {
                $filters[$filter] = $query[$filter];
            }
        }

        try {
            $summary = $this->ledger->summarize($filters);
        } catch (\Throwable $exception) {
            return $this->error('LEDGER_ERROR', $exception->getMessage(), 500);
        }

        return $this->json([
            'filters' => $filters,
            'count' => $summary['count'],
            'totals' => $summary['totals'],
            'by_method' => $summary['by_method'],
        ]);
    }

    public function export(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $filters = [];

        foreach (['from', 'to', 'payment_method', 'entry_type'] as $filter) {
            if (isset($query[$filter]) && $query[$filter] !== '') {
                $filters[$filter] = $query[$filter];
            }
        }

        $entries = $this->ledger->search($filters);
        $saleLabels = $this->buildSaleLabels($entries);

        // Mapeia comissao por sale_id a partir dos ajustes (gross=0, fee>0, entry_type=adjustment)
        $commissionBySale = [];
        foreach ($entries as $e) {
            $isCommissionAdj = strtolower($e->entryType()) === 'adjustment'
                && $e->saleId() !== null
                && $e->grossAmount()->toFloat() == 0.0
                && $e->feeAmount()->toFloat() > 0.0;
            if ($isCommissionAdj) {
                $saleId = (string) $e->saleId();
                $val = abs($e->netAmount()->toFloat());
                $commissionBySale[$saleId] = ($commissionBySale[$saleId] ?? 0.0) + $val;
            }
        }

        $headers = ['Data', 'Metodo', 'Parcelas', 'Vendedor', 'Bruto', 'TaxaCartao', 'Comissao', 'Liquido', 'Origem'];
        $rows = [];

        foreach ($entries as $entry) {
            $isCommissionAdj = strtolower($entry->entryType()) === 'adjustment'
                && $entry->saleId() !== null
                && $entry->grossAmount()->toFloat() == 0.0
                && $entry->feeAmount()->toFloat() > 0.0;

            if ($isCommissionAdj) {
                // Oculta linha de ajuste de comissao (vai ser consolidada na venda)
                continue;
            }

            $created = $entry->createdAt()->format('Y-m-d H:i:s');
            $method = $entry->method()->value;
            $installments = 1;
            $note = $entry->note();
            if (is_string($note)) {
                if (preg_match('/installments=(\d+)/i', $note, $mm) === 1) {
                    $installments = max(1, (int) $mm[1]);
                }
            }
            $gross = number_format($entry->grossAmount()->toFloat(), 2, '.', '');
            $fee = number_format($entry->feeAmount()->toFloat(), 2, '.', '');
            $commission = 0.0;
            if (strtolower($entry->entryType()) === 'sale' && $entry->saleId() !== null) {
                $commission = (float) ($commissionBySale[(string) $entry->saleId()] ?? 0.0);
            }
            $netAdjusted = $entry->netAmount()->toFloat() - $commission;
            $net = number_format($netAdjusted, 2, '.', '');
            $label = $entry->saleId() !== null
                ? ($saleLabels[(string) $entry->saleId()] ?? null)
                : null;
            $origin = $entry->saleId() !== null
                ? ('Venda ' . ($label ?? (string) $entry->saleId()))
                : ('Ajuste por ' . $entry->userId());

            $rows[] = [
                $created,
                strtoupper($method),
                (string) $installments,
                $entry->userId(),
                $gross,
                $fee,
                number_format($commission, 2, '.', ''),
                $net,
                $origin,
            ];
        }

        $csv = $this->exporter->toCsv($headers, $rows);

        $filename = 'fluxo_caixa_' . date('Ymd_His') . '.csv';
        $response = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response->withBody(Stream::create($csv));
    }

    private function presentEntry(CashLedgerEntry $entry, ?string $saleLabel = null): array
    {
        return [
            'id' => $entry->id(),
            'sale_id' => $entry->saleId(),
            'sale_label' => $saleLabel,
            'user_id' => $entry->userId(),
            'payment_method' => $entry->method()->value,
            'gross_amount' => $entry->grossAmount()->toFloat(),
            'fee_amount' => $entry->feeAmount()->toFloat(),
            'net_amount' => $entry->netAmount()->toFloat(),
            'entry_type' => $entry->entryType(),
            'created_at' => $entry->createdAt()->format(DATE_ATOM),
            'note' => $entry->note(),
        ];
    }

    private function parseDate(mixed $raw): ?DateTimeImmutable
    {
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $value = trim($raw);
        $timezone = new DateTimeZone('America/Sao_Paulo');

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                $value .= ' 00:00:00';
            }

            return new DateTimeImmutable($value, $timezone);
        } catch (\Exception) {
            throw new BusinessRuleException('INVALID_DATE', 'Data invalida.');
        }
    }

    /**
     * @param list<CashLedgerEntry> $entries
     * @return array<string, string>
     */
    private function buildSaleLabels(array $entries): array
    {
        $ids = [];
        foreach ($entries as $entry) {
            $saleId = $entry->saleId();
            if ($saleId !== null) {
                $ids[$saleId] = $saleId;
            }
        }

        if ($ids === []) {
            return [];
        }

        try {
            $sales = $this->sales->search([
                'ids' => array_values($ids),
                'with_items' => true,
                'limit' => count($ids),
            ]);
        } catch (\Throwable) {
            return [];
        }

        $labels = [];
        foreach ($sales as $sale) {
            $label = $this->summarizeSale($sale);
            if ($label !== null) {
                $labels[$sale->id()] = $label;
            }
        }

        return $labels;
    }

    private function summarizeSale(Sale $sale): ?string
    {
        $items = $sale->items();
        if ($items === []) {
            return null;
        }

        $labels = [];
        $totalPieces = 0;
        foreach ($items as $item) {
            $qty = max(1, $item->quantity());
            $totalPieces += $qty;

            $name = $item->productName() ?? $item->productSku() ?? $item->productId();
            $size = $item->size();
            $parts = [$name];
            if ($size) {
                $parts[] = 'tamanho ' . $size;
            }
            $parts[] = 'x' . $qty;

            $labels[] = implode(' ', $parts);
        }

        $summary = implode(' + ', $labels);
        if ($totalPieces > 1) {
            $summary .= sprintf(' (%d peças)', $totalPieces);
        }

        return $summary;
    }
}

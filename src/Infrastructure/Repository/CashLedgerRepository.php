<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\CashLedgerEntry;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\CashLedgerRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class CashLedgerRepository implements CashLedgerRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<CashLedgerEntry>
     */
    public function search(array $filters = []): array
    {
        $query = [
            'select' => 'id,sale_id,user_id,payment_method,gross_amount,fee_amount,net_amount,entry_type,created_at,note',
            'order' => 'created_at.desc',
        ];

        if (($filters['payment_method'] ?? null) !== null) {
            $query['payment_method'] = 'eq.' . $filters['payment_method'];
        }

        if (($filters['entry_type'] ?? null) !== null) {
            $query['entry_type'] = 'eq.' . $filters['entry_type'];
        }

        $andFilters = [];

        if (($filters['from'] ?? null) !== null) {
            $andFilters[] = sprintf('created_at.gte.%s', $this->normalizeDateFilter((string) $filters['from'], false));
        }

        if (($filters['to'] ?? null) !== null) {
            $andFilters[] = sprintf('created_at.lte.%s', $this->normalizeDateFilter((string) $filters['to'], true));
        }

        if ($andFilters !== []) {
            $query['and'] = '(' . implode(',', $andFilters) . ')';
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        try {
            $response = $this->client->request('GET', 'rest/v1/cash_ledger', [
                'query' => $query,
            ]);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            if (stripos($message, 'note') !== false || stripos($message, '42703') !== false) {
                $query['select'] = $this->removeColumnFromSelect($query['select'], 'note');
                $response = $this->client->request('GET', 'rest/v1/cash_ledger', [
                    'query' => $query,
                ]);
            } else {
                throw $e;
            }
        }

        if ($response === null) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        return array_map(fn (array $row): CashLedgerEntry => $this->mapEntry($row), $rows);
    }

    public function save(CashLedgerEntry $entry): void
    {
        $payload = [
            'id' => $entry->id(),
            'sale_id' => $entry->saleId(),
            'user_id' => $entry->userId(),
            'payment_method' => $entry->method()->value,
            'gross_amount' => $entry->grossAmount()->toFloat(),
            'fee_amount' => $entry->feeAmount()->toFloat(),
            'net_amount' => $entry->netAmount()->toFloat(),
            'entry_type' => $entry->entryType(),
            'created_at' => $entry->createdAt()->format(DATE_ATOM),
            'note' => $entry->note(),
        ];
        // Remove chaves nulas, em especial 'note' quando a coluna nao existir
        $payload = array_filter(
            $payload,
            static fn ($v) => $v !== null
        );

        try {
            $this->client->request('POST', 'rest/v1/cash_ledger', [
                'json' => $payload,
                'headers' => ['Prefer' => 'return=minimal'],
            ]);
            return;
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            $lower = strtolower($message);

            // Se a tabela nao tem a coluna 'note', reenvia sem 'note'
            if (isset($payload['note']) && (
                str_contains($lower, "'note' column") ||
                str_contains($lower, 'note') && (str_contains($lower, 'could not find') || str_contains($lower, 'undefined column'))
            )) {
                $payloadNoNote = $payload;
                unset($payloadNoNote['note']);
                $this->client->request('POST', 'rest/v1/cash_ledger', [
                    'json' => $payloadNoNote,
                    'headers' => ['Prefer' => 'return=minimal'],
                ]);
                return;
            }
            // Fallback para esquema legado com colunas amount/fees/type/method
            $legacy = [
                'id' => $entry->id(),
                'sale_id' => $entry->saleId(),
                'user_id' => $entry->userId(),
                'method' => $entry->method()->value,
                'amount' => $entry->grossAmount()->toFloat(),
                'fees' => $entry->feeAmount()->toFloat(),
                'type' => $entry->entryType(),
                'created_at' => $entry->createdAt()->format(DATE_ATOM),
                // note ignored in legacy fallback
            ];

            // Só tenta fallback se erro indicar claramente que as COLUNAS NOVAS não existem (schema legado de fato)
            $shouldFallback = (
                str_contains($lower, 'gross_amount') ||
                str_contains($lower, 'fee_amount') ||
                str_contains($lower, 'net_amount') ||
                str_contains($lower, 'payment_method') ||
                str_contains($lower, 'entry_type') ||
                str_contains($lower, '42703') ||
                str_contains($lower, 'undefined column')
            );

            if (!$shouldFallback) {
                throw $e;
            }

            $this->client->request('POST', 'rest/v1/cash_ledger', [
                'json' => $legacy,
                'headers' => ['Prefer' => 'return=minimal'],
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapEntry(array $row): CashLedgerEntry
    {
        $methodValue = $row['payment_method'] ?? $row['method'] ?? null;
        if (!is_string($methodValue) || trim($methodValue) === '') {
            throw new \RuntimeException('Cash ledger entry missing payment method.');
        }

        $method = PaymentMethod::tryFrom(strtolower(trim($methodValue)));
        if ($method === null) {
            throw new \RuntimeException(sprintf('Unsupported payment method "%s".', $methodValue));
        }

        $grossValue = $row['gross_amount'] ?? $row['amount'] ?? $row['total'] ?? 0;
        $feeValue = $row['fee_amount'] ?? $row['fees'] ?? 0;
        $netValue = $row['net_amount'] ?? ($grossValue - $feeValue);

        $entryTypeRaw = $row['entry_type'] ?? $row['type'] ?? 'sale';
        $entryType = is_string($entryTypeRaw) && $entryTypeRaw !== '' ? strtolower($entryTypeRaw) : 'sale';

        $userIdRaw = $row['user_id'] ?? $row['created_by'] ?? null;
        $userId = is_string($userIdRaw) && $userIdRaw !== '' ? $userIdRaw : '00000000-0000-0000-0000-000000000000';

        $saleIdRaw = $row['sale_id'] ?? null;
        $saleId = is_string($saleIdRaw) && $saleIdRaw !== '' ? $saleIdRaw : null;

        $createdAtRaw = $row['created_at'] ?? null;
        $createdAt = is_string($createdAtRaw) && $createdAtRaw !== ''
            ? new \DateTimeImmutable($createdAtRaw)
            : new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));

        return new CashLedgerEntry(
            id: (string) $row['id'],
            saleId: $saleId,
            userId: $userId,
            method: $method,
            grossAmount: Money::fromFloat((float) $grossValue),
            feeAmount: Money::fromFloat((float) $feeValue),
            netAmount: Money::fromFloat((float) $netValue),
            entryType: $entryType,
            createdAt: $createdAt,
            note: isset($row['note']) && is_string($row['note']) ? $row['note'] : null
        );
    }

    private function normalizeDateFilter(string $value, bool $isEnd): string
    {
        $trimmed = trim($value);
        $timezone = new \DateTimeZone('America/Sao_Paulo');

        if ($trimmed === '') {
            return (new \DateTimeImmutable('now', $timezone))->format(DATE_ATOM);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed) === 1) {
            $time = $isEnd ? '23:59:59' : '00:00:00';
            $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $trimmed . ' ' . $time, $timezone);

            if ($date instanceof \DateTimeImmutable) {
                return $date->format(DATE_ATOM);
            }
        }

        try {
            $date = new \DateTimeImmutable($trimmed, $timezone);

            if ($isEnd && $date->format('H:i:s') === '00:00:00') {
                $date = $date->setTime(23, 59, 59);
            }

            return $date->format(DATE_ATOM);
        } catch (\Exception) {
            return $trimmed;
        }
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
}


<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Entity\CashLedgerEntry;
use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\CashLedgerRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use DateTimeImmutable;
use DateTimeZone;

final class CashLedgerService
{
    public function __construct(private readonly CashLedgerRepositoryInterface $ledger)
    {
    }

    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<CashLedgerEntry>
     */
    public function search(array $filters = []): array
    {
        return $this->ledger->search($filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     totals: array{gross: float, fee: float, net: float},
     *     by_method: array<string, array{gross: float, fee: float, net: float, count: int}>,
     *     count: int
     * }
     */
    public function summarize(array $filters = []): array
    {
        $entries = $this->ledger->search($filters);

        $totals = [
            'gross' => Money::fromFloat(0),
            'fee' => Money::fromFloat(0),
            'net' => Money::fromFloat(0),
        ];

        /** @var array<string, array{gross: Money, fee: Money, net: Money, count: int}> $byMethod */
        $byMethod = [];

        foreach ($entries as $entry) {
            $methodKey = $entry->method()->value;

            if (!isset($byMethod[$methodKey])) {
                $byMethod[$methodKey] = [
                    'gross' => Money::fromFloat(0),
                    'fee' => Money::fromFloat(0),
                    'net' => Money::fromFloat(0),
                    'count' => 0,
                ];
            }

            $byMethod[$methodKey]['gross'] = $byMethod[$methodKey]['gross']->add($entry->grossAmount());
            $byMethod[$methodKey]['fee'] = $byMethod[$methodKey]['fee']->add($entry->feeAmount());
            $byMethod[$methodKey]['net'] = $byMethod[$methodKey]['net']->add($entry->netAmount());
            $byMethod[$methodKey]['count']++;

            $totals['gross'] = $totals['gross']->add($entry->grossAmount());
            $totals['fee'] = $totals['fee']->add($entry->feeAmount());
            $totals['net'] = $totals['net']->add($entry->netAmount());
        }

        $serializedByMethod = [];

        foreach ($byMethod as $method => $accumulator) {
            $serializedByMethod[$method] = [
                'gross' => $accumulator['gross']->toFloat(),
                'fee' => $accumulator['fee']->toFloat(),
                'net' => $accumulator['net']->toFloat(),
                'count' => $accumulator['count'],
            ];
        }

        ksort($serializedByMethod);

        return [
            'totals' => [
                'gross' => $totals['gross']->toFloat(),
                'fee' => $totals['fee']->toFloat(),
                'net' => $totals['net']->toFloat(),
            ],
            'by_method' => $serializedByMethod,
            'count' => count($entries),
        ];
    }

    public function registerSalePayment(Sale $sale, PaymentMethod $method, Money $grossAmount, Money $feeAmount, ?string $note = null): CashLedgerEntry
    {
        if ($grossAmount->toInt() <= 0) {
            throw new BusinessRuleException('INVALID_AMOUNT', 'Valor bruto nao pode ser zero.');
        }

        if ($feeAmount->isNegative()) {
            throw new BusinessRuleException('INVALID_FEE', 'Taxa nao pode ser negativa.');
        }

        $netAmount = $grossAmount->subtract($feeAmount);

        $entry = new CashLedgerEntry(
            id: $this->generateUuidV4(),
            saleId: $sale->id(),
            userId: $sale->userId(),
            method: $method,
            grossAmount: $grossAmount,
            feeAmount: $feeAmount,
            netAmount: $netAmount,
            entryType: 'sale',
            createdAt: new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')),
            note: $note
        );

        $this->ledger->save($entry);

        return $entry;
    }

    public function registerRefund(
        PaymentMethod $method,
        float $amount,
        string $userId,
        ?string $note = null,
        ?DateTimeImmutable $createdAt = null
    ): CashLedgerEntry {
        if (!\is_finite($amount) || $amount <= 0) {
            throw new BusinessRuleException('INVALID_AMOUNT', 'Valor do estorno deve ser positivo.');
        }

        $timestamp = $createdAt ?? new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
        $gross = Money::fromFloat(0);
        $fee = Money::fromFloat(abs($amount));
        $net = $gross->subtract($fee);

        $entry = new CashLedgerEntry(
            id: $this->generateUuidV4(),
            saleId: null,
            userId: $userId,
            method: $method,
            grossAmount: $gross,
            feeAmount: $fee,
            netAmount: $net,
            // Usa 'adjustment' para compatibilidade com esquemas legados (evita restriÇõÇæes de enum)
            entryType: 'adjustment',
            createdAt: $timestamp,
            note: $note
        );

        $this->ledger->save($entry);

        return $entry;
    }

    public function registerAdjustment(
        PaymentMethod $method,
        float $grossAmount,
        float $feeAmount,
        ?string $note,
        string $userId,
        ?string $saleId = null,
        ?DateTimeImmutable $createdAt = null
    ): CashLedgerEntry
    {
        // Permite grossAmount == 0 para ajustes de deducao (ex.: comissao)
        if (!\is_finite($grossAmount)) {
            throw new BusinessRuleException('INVALID_AMOUNT', 'Valor bruto invalido.');
        }

        if ($feeAmount < 0) {
            throw new BusinessRuleException('INVALID_FEE', 'Taxa nao pode ser negativa.');
        }

        $timestamp = $createdAt ?? new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));

        $entry = new CashLedgerEntry(
            id: $this->generateUuidV4(),
            saleId: $saleId,
            userId: $userId,
            method: $method,
            grossAmount: Money::fromFloat($grossAmount),
            feeAmount: Money::fromFloat($feeAmount),
            netAmount: Money::fromFloat($grossAmount - $feeAmount),
            entryType: 'adjustment',
            createdAt: $timestamp
        );

        $this->ledger->save($entry);

        return $entry;
    }
}

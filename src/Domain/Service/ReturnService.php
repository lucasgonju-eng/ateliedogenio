<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;
use DateTimeImmutable;
use DateTimeZone;

final class ReturnService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductVariantRepositoryInterface $variants,
        private readonly CashLedgerService $ledger
    ) {
    }

    /**
     * @return array{ledger_id: string, movement_id: string|null}
     */
    public function registerReturn(
        string $productId,
        string $size,
        int $quantity,
        float $refundAmount,
        PaymentMethod $method,
        string $userId,
        ?DateTimeImmutable $returnedAt,
        ?string $note = null
    ): array {
        if ($quantity < 1) {
            throw new BusinessRuleException('INVALID_QUANTITY', 'Quantidade da devolucao deve ser positiva.');
        }

        if (!\is_finite($refundAmount) || $refundAmount <= 0) {
            throw new BusinessRuleException('INVALID_AMOUNT', 'Valor estornado deve ser maior que zero.');
        }

        if (trim($size) === '') {
            throw new BusinessRuleException('INVALID_SIZE', 'Informe o tamanho da peca devolvida.');
        }

        $product = $this->products->findById($productId);
        if ($product === null) {
            throw new BusinessRuleException('PRODUCT_NOT_FOUND', 'Produto nao encontrado.');
        }

        $returnedAt = $returnedAt ?? new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
        $noteClean = $note !== null && trim($note) !== '' ? trim($note) : null;

        $movementId = null;
        $variantsUpdated = false;

        try {
            $this->variants->increment($productId, $size, $quantity);
            $variantsUpdated = true;
        } catch (\Throwable) {
            $variantsUpdated = false;
        }

        if ($variantsUpdated) {
            try {
                $total = $this->variants->sumForProduct($productId);
                $currentStock = $product->stock();
                if ($total > $currentStock) {
                    $product->increaseStock($total - $currentStock);
                } elseif ($total < $currentStock) {
                    $product->decreaseStock($currentStock - $total);
                }
            } catch (\Throwable) {
                $product->increaseStock($quantity);
            }
        } else {
            $product->increaseStock($quantity);
        }

        $this->products->save($product);

        $ledgerNote = $noteClean ?? sprintf('Devolucao %s tam %s', $product->sku(), $size);
        $ledgerNote = sprintf('[refund] %s', $ledgerNote);
        $ledger = $this->ledger->registerRefund(
            method: $method,
            amount: $refundAmount,
            userId: $userId,
            note: $ledgerNote,
            createdAt: $returnedAt
        );

        return [
            'ledger_id' => $ledger->id(),
            'movement_id' => $movementId,
        ];
    }
}

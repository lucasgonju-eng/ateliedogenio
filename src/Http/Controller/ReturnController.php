<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Service\ReturnService;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ReturnController extends BaseController
{
    public function __construct(private readonly ReturnService $returns)
    {
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->user($request);
        $payload = $this->input($request);
        if ($payload === []) {
            $payload = $this->query($request);
        }

        $productId = isset($payload['product_id']) ? (string) $payload['product_id'] : '';
        $size = isset($payload['size']) ? trim((string) $payload['size']) : '';
        $quantity = isset($payload['quantity']) ? (int) $payload['quantity'] : 1;
        $refundAmount = isset($payload['refund_amount']) ? (float) $payload['refund_amount'] : 0.0;
        $note = isset($payload['note']) ? (string) $payload['note'] : null;
        $methodRaw = isset($payload['payment_method']) ? (string) $payload['payment_method'] : 'pix';

        try {
            $returnedAt = $this->parseDate($payload['returned_at'] ?? null);
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        }

        if ($productId === '') {
            return $this->error('VALIDATION_ERROR', 'Produto e obrigatorio.', 422);
        }

        if ($size === '') {
            return $this->error('VALIDATION_ERROR', 'Informe o tamanho devolvido.', 422);
        }

        try {
            $method = PaymentMethod::from($methodRaw);
        } catch (\ValueError) {
            $method = PaymentMethod::PIX;
        }

        try {
            $result = $this->returns->registerReturn(
                productId: $productId,
                size: $size,
                quantity: $quantity,
                refundAmount: $refundAmount,
                method: $method,
                userId: $user['id'],
                returnedAt: $returnedAt,
                note: $note
            );
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('RETURN_ERROR', $exception->getMessage(), 500);
        }

        return $this->json([
            'ledger_id' => $result['ledger_id'],
            'movement_id' => $result['movement_id'],
            'payment_method' => $method->value,
        ], 201);
    }

    private function parseDate(mixed $raw): ?DateTimeImmutable
    {
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $value = trim($raw);
        $timezone = new DateTimeZone('America/Sao_Paulo');

        try {
            // Se vier apenas a data (YYYY-MM-DD), completa com 00:00:00
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                $value .= ' 00:00:00';
            }

            return new DateTimeImmutable($value, $timezone);
        } catch (\Exception) {
            throw new BusinessRuleException('INVALID_DATE', 'Data de devolucao invalida.');
        }
    }
}

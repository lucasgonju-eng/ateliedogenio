<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Enum\SaleStatus;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use AtelieDoGenio\Domain\Service\SaleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SaleController extends BaseController
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly SaleService $saleService,
    ) {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->user($request);
        $query = $this->query($request);

        $filters = [
            'user_id' => $user['id'],
        ];

        if (isset($query['status'])) {
            $filters['status'] = (string) $query['status'];
        }

        if (isset($query['limit'])) {
            $filters['limit'] = max(1, (int) $query['limit']);
        }

        if (isset($query['offset'])) {
            $filters['offset'] = max(0, (int) $query['offset']);
        }

        $sales = $this->sales->search($filters);
        $items = array_map($this->presentSale(...), $sales);

        $response = $this->json([
            'items' => $items,
            'count' => count($items),
        ]);

        return $response->withHeader('X-Total-Count', (string) count($items));
    }

    public function createDraft(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->user($request);
        $payload = $this->input($request);

        $items = $payload['items'] ?? [];
        if (!is_array($items) || $items === []) {
            return $this->error('VALIDATION_ERROR', 'Itens da venda são obrigatórios.', 422);
        }

        $normalizedItems = [];
        foreach ($items as $item) {
            if (!isset($item['product_id'], $item['qty'])) {
                return $this->error('VALIDATION_ERROR', 'Cada item precisa de product_id e qty.', 422);
            }

            if (!isset($item['size']) || trim((string) $item['size']) === '') {
                return $this->error('VALIDATION_ERROR', 'Cada item precisa informar o tamanho.', 422);
            }

            $normalizedItems[] = [
                'product_id' => (string) $item['product_id'],
                'size' => trim((string) $item['size']),
                'qty' => (int) $item['qty'],
            ];
        }

        try {
            $sale = $this->saleService->createDraft(
                $user['id'],
                isset($payload['customer_id']) ? (string) $payload['customer_id'] : null,
                $normalizedItems
            );
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('SALE_ERROR', $exception->getMessage(), 500);
        }

        return $this->json([
            'sale_id' => $sale->id(),
            'status' => $sale->status()->value,
            'total' => $sale->total()->toFloat(),
            'subtotal' => $sale->subtotal()->toFloat(),
        ], 201);
    }

    public function updateStatus(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $saleId = $params['id'] ?? null;
        if ($saleId === null) {
            return $this->error('VALIDATION_ERROR', 'ID da venda � obrigat�rio.', 422);
        }

        $payload = $this->input($request);
        $statusValue = $payload['status'] ?? null;

        if (!is_string($statusValue)) {
            return $this->error('VALIDATION_ERROR', 'Status informado � inv�lido.', 422);
        }

        try {
            $newStatus = SaleStatus::from($statusValue);
        } catch (\ValueError) {
            return $this->error('VALIDATION_ERROR', 'Status informado � inv�lido.', 422);
        }

        $sale = $this->sales->findById($saleId);
        if ($sale === null) {
            return $this->error('NOT_FOUND', 'Venda n�o encontrada.', 404);
        }

        $user = $this->user($request);
        $role = $user['role'] ?? null;
        if ($sale->userId() !== $user['id'] && $role !== 'admin') {
            return $this->error('FORBIDDEN', 'Voc� n�o pode alterar esta venda.', 403);
        }

        if ($sale->status() === $newStatus) {
            return $this->json($this->presentSale($sale));
        }

        if (!$this->isStatusTransitionAllowed($sale->status(), $newStatus)) {
            return $this->error('INVALID_STATUS_TRANSITION', 'Mudan�a de status n�o permitida.', 422);
        }

        $this->sales->updateStatus($saleId, $newStatus);
        $updatedSale = $this->sales->findById($saleId) ?? $sale;

        return $this->json($this->presentSale($updatedSale));
    }
    public function checkout(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $saleId = $params['id'] ?? null;
        if ($saleId === null) {
            return $this->error('VALIDATION_ERROR', 'ID da venda é obrigatório.', 422);
        }

        $payload = $this->input($request);
        $method = $payload['payment_method'] ?? null;

        if (!is_string($method)) {
            return $this->error('VALIDATION_ERROR', 'Método de pagamento inválido.', 422);
        }

        try {
            $paymentMethod = PaymentMethod::from($method);
        } catch (\ValueError) {
            return $this->error('VALIDATION_ERROR', 'Método de pagamento não suportado.', 422);
        }

        $discountPercent = isset($payload['discount_percent']) ? (float) $payload['discount_percent'] : 0.0;
        $installments = isset($payload['installments']) ? max(1, (int)$payload['installments']) : 1;
        $terminalId = isset($payload['terminal_id']) && is_string($payload['terminal_id']) ? $payload['terminal_id'] : null;
        $brandId = isset($payload['brand_id']) && is_string($payload['brand_id']) ? $payload['brand_id'] : null;

        $user = $this->user($request);
        $role = (string) ($user['role'] ?? '');
        if ($role !== 'admin' && $discountPercent > 0) {
            return $this->error('FORBIDDEN', 'Somente administradores podem aplicar desconto.', 403);
        }

        try {
            $result = $this->saleService->finalizeSale($saleId, $paymentMethod, $discountPercent, $role, $installments, $terminalId, $brandId);
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('SALE_ERROR', $exception->getMessage(), 500);
        }

        return $this->json($result);
    }

    private function presentSale(Sale $sale): array
    {
        $createdAt = $sale->createdAt();
        $label = sprintf('Venda %s', $createdAt->format('d/m H:i'));
        $shortCode = strtoupper(substr($sale->id(), 0, 6));

        return [
            'id' => $sale->id(),
            'status' => $sale->status()->value,
            'status_label' => $this->statusLabel($sale->status()),
            'subtotal' => $sale->subtotal()->toFloat(),
            'total' => $sale->total()->toFloat(),
            'fees' => $sale->feeTotal()->toFloat(),
            'profit_estimated' => $sale->profitEstimated()->toFloat(),
            'created_at' => $createdAt->format(DATE_ATOM),
            'label' => $label,
            'short_code' => $shortCode,
        ];
    }

    private function isStatusTransitionAllowed(SaleStatus $current, SaleStatus $next): bool
    {
        if ($current === $next) {
            return true;
        }

        $map = [
            SaleStatus::ABERTA->value => [SaleStatus::PAGAMENTO_PENDENTE, SaleStatus::PAGA, SaleStatus::CANCELADA],
            SaleStatus::PAGAMENTO_PENDENTE->value => [SaleStatus::PAGA, SaleStatus::CANCELADA],
            SaleStatus::PAGA->value => [SaleStatus::ENTREGUE, SaleStatus::CANCELADA],
            SaleStatus::ENTREGUE->value => [],
            SaleStatus::CANCELADA->value => [],
        ];

        $allowed = $map[$current->value] ?? [];

        return in_array($next, $allowed, true);
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
}



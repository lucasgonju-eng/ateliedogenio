<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\InventoryMovement;
use AtelieDoGenio\Domain\Enum\InventoryMovementType;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Service\InventoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class InventoryController extends BaseController
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function movements(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $filters = [];

        if (isset($query['product_id'])) {
            $filters['product_id'] = (string) $query['product_id'];
        }

        if (isset($query['type'])) {
            $filters['type'] = (string) $query['type'];
        }

        if (isset($query['limit'])) {
            $filters['limit'] = max(1, (int) $query['limit']);
        }

        if (isset($query['offset'])) {
            $filters['offset'] = max(0, (int) $query['offset']);
        }

        $movements = $this->inventory->listMovements($filters);
        $items = array_map($this->presentMovement(...), $movements);

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

        foreach (['product_id', 'type', 'qty', 'unit_cost'] as $field) {
            if (!isset($payload[$field])) {
                return $this->error('VALIDATION_ERROR', sprintf('Campo %s é obrigatório.', $field), 422);
            }
        }

        try {
            $movement = $this->inventory->adjustStock(
                productId: (string) $payload['product_id'],
                type: InventoryMovementType::from((string) $payload['type']),
                qty: (int) $payload['qty'],
                unitCost: (float) $payload['unit_cost'],
                reference: isset($payload['reference']) ? (string) $payload['reference'] : '',
                userId: $user['id']
            );
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('INVENTORY_ERROR', $exception->getMessage(), 500);
        }

        return $this->json($this->presentMovement($movement), 201);
    }

    private function presentMovement(InventoryMovement $movement): array
    {
        return [
            'id' => $movement->id(),
            'product_id' => $movement->productId(),
            'user_id' => $movement->userId(),
            'type' => $movement->type()->value,
            'quantity' => $movement->quantity(),
            'unit_cost' => $movement->unitCost()->toFloat(),
            'reference' => $movement->reference(),
            'created_at' => $movement->createdAt()->format(DATE_ATOM),
        ];
    }
}


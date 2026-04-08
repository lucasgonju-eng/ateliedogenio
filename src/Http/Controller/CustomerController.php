<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\Customer;
use AtelieDoGenio\Domain\Repository\CustomerRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CustomerController extends BaseController
{
    public function __construct(private readonly CustomerRepositoryInterface $customers)
    {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $filters = [];

        if (isset($query['search'])) {
            $filters['search'] = (string) $query['search'];
        }
        if (isset($query['limit'])) {
            $filters['limit'] = max(1, (int) $query['limit']);
        }
        if (isset($query['offset'])) {
            $filters['offset'] = max(0, (int) $query['offset']);
        }

        $items = $this->customers->search($filters);
        $payload = array_map($this->presentCustomer(...), $items);

        $response = $this->json([
            'items' => $payload,
            'count' => count($payload),
        ]);

        return $response->withHeader('X-Total-Count', (string) count($payload));
    }

    public function show(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $id = $params['id'] ?? null;
        if ($id === null) {
            return $this->error('VALIDATION_ERROR', 'ID do cliente é obrigatório.', 422);
        }

        $customer = $this->customers->findById($id);

        if ($customer === null) {
            return $this->error('NOT_FOUND', 'Cliente não encontrado.', 404);
        }

        return $this->json($this->presentCustomer($customer));
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $name = trim((string) ($payload['name'] ?? ''));

        if ($name === '') {
            return $this->error('VALIDATION_ERROR', 'Nome é obrigatório.', 422);
        }

        $customer = new Customer(
            id: '',
            name: $name,
            email: isset($payload['email']) ? (string) $payload['email'] : null,
            phone: isset($payload['phone']) ? (string) $payload['phone'] : null,
            document: isset($payload['document']) ? (string) $payload['document'] : null,
            isWalkIn: isset($payload['is_walk_in']) ? (bool) $payload['is_walk_in'] : false
        );

        $this->customers->save($customer);

        return $this->json(['message' => 'Cliente cadastrado.'], 201);
    }

    public function update(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $id = $params['id'] ?? null;

        if ($id === null) {
            return $this->error('VALIDATION_ERROR', 'ID do cliente é obrigatório.', 422);
        }

        $existing = $this->customers->findById($id);
        if ($existing === null) {
            return $this->error('NOT_FOUND', 'Cliente não encontrado.', 404);
        }

        $payload = $this->input($request);

        $customer = new Customer(
            id: $existing->id(),
            name: (string) ($payload['name'] ?? $existing->name()),
            email: $payload['email'] ?? $existing->email(),
            phone: $payload['phone'] ?? $existing->phone(),
            document: $payload['document'] ?? $existing->document(),
            isWalkIn: isset($payload['is_walk_in']) ? (bool) $payload['is_walk_in'] : $existing->isWalkIn()
        );

        $this->customers->save($customer);

        return $this->json($this->presentCustomer($customer));
    }

    private function presentCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->id(),
            'name' => $customer->name(),
            'email' => $customer->email(),
            'phone' => $customer->phone(),
            'document' => $customer->document(),
            'is_walk_in' => $customer->isWalkIn(),
        ];
    }
}


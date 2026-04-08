<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Repository;

use AtelieDoGenio\Domain\Entity\Customer;
use AtelieDoGenio\Domain\Repository\CustomerRepositoryInterface;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    public function findById(string $id): ?Customer
    {
        $response = $this->client->request('GET', 'rest/v1/customers', [
            'headers' => ['Prefer' => 'single-object'],
            'query' => [
                'id' => 'eq.' . $id,
                'select' => '*',
            ],
        ]);

        if ($response === null) {
            return null;
        }

        return $this->mapCustomer($response);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<Customer>
     */
    public function search(array $filters = []): array
    {
        $query = [
            'select' => '*',
            'order' => 'created_at.desc',
        ];

        if (($filters['search'] ?? null) !== null) {
            $search = '%' . $filters['search'] . '%';
            $query['or'] = sprintf('name.ilike.%1$s,email.ilike.%1$s,document.ilike.%1$s', $search);
        }

        if (($filters['limit'] ?? null) !== null) {
            $query['limit'] = (string) $filters['limit'];
        }

        if (($filters['offset'] ?? null) !== null) {
            $query['offset'] = (string) $filters['offset'];
        }

        $response = $this->client->request('GET', 'rest/v1/customers', [
            'query' => $query,
        ]);

        if ($response === null) {
            return [];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response;

        return array_map(fn (array $row): Customer => $this->mapCustomer($row), $rows);
    }

    public function save(Customer $customer): void
    {
        $payload = [
            'name' => $customer->name(),
            'email' => $customer->email(),
            'phone' => $customer->phone(),
            'document' => $customer->document(),
            'is_walk_in' => $customer->isWalkIn(),
        ];

        if ($customer->id() !== '') {
            $this->client->request('PATCH', 'rest/v1/customers?id=eq.' . $customer->id(), [
                'json' => $payload,
                'headers' => ['Prefer' => 'return=minimal'],
            ]);

            return;
        }

        $this->client->request('POST', 'rest/v1/customers', [
            'json' => $payload,
            'headers' => ['Prefer' => 'return=representation'],
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapCustomer(array $row): Customer
    {
        return new Customer(
            id: $row['id'],
            name: $row['name'],
            email: $row['email'] ?? null,
            phone: $row['phone'] ?? null,
            document: $row['document'] ?? null,
            isWalkIn: (bool) $row['is_walk_in']
        );
    }
}


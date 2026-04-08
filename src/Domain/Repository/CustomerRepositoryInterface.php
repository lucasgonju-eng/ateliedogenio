<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\Customer;

interface CustomerRepositoryInterface
{
    public function findById(string $id): ?Customer;

    /**
     * @param array<string, mixed> $filters
     * @return list<Customer>
     */
    public function search(array $filters = []): array;

    public function save(Customer $customer): void;
}


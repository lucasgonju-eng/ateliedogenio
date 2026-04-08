<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\CardBrand;

interface CardBrandRepositoryInterface
{
    /**
     * @return list<CardBrand>
     */
    public function findAll(): array;

    public function upsert(?string $id, string $name, bool $active): CardBrand;
}


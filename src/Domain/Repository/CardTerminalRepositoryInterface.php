<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Repository;

use AtelieDoGenio\Domain\Entity\CardTerminal;

interface CardTerminalRepositoryInterface
{
    /**
     * @return list<CardTerminal>
     */
    public function findAll(): array;

    public function upsert(?string $id, string $name, bool $active): CardTerminal;
}


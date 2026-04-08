<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Enum;

enum CommissionStatus: string
{
    case PENDENTE = 'pending';
    case PAGA = 'paid';

    public function isSettled(): bool
    {
        return $this === self::PAGA;
    }
}


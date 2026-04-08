<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Enum;

enum InventoryMovementType: string
{
    case ENTRADA = 'entrada';
    case SAIDA = 'saida';
    case AJUSTE = 'ajuste';
}


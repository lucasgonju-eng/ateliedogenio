<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Enum;

enum PaymentMethod: string
{
    case CREDITO = 'credito';
    case DEBITO = 'debito';
    case PIX = 'pix';
    case DINHEIRO = 'dinheiro';
    case TRANSFERENCIA = 'transferencia';
}


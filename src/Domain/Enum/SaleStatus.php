<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Enum;

enum SaleStatus: string
{
    case ABERTA = 'aberta';
    case PAGAMENTO_PENDENTE = 'pagamento_pendente';
    case PAGA = 'paga';
    case ENTREGUE = 'entregue';
    case CANCELADA = 'cancelada';

    public function isFinal(): bool
    {
        return $this === self::ENTREGUE || $this === self::CANCELADA;
    }
}


<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Entity\Sale;

interface ReceiptNotifierInterface
{
    public function sendReceipt(Sale $sale, string $recipientEmail): void;
}


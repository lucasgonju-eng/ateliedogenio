<?php

declare(strict_types=1);

namespace AtelieDoGenio\Tests\Support\Fake;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Service\ReceiptNotifierInterface;

final class FakeReceiptNotifier implements ReceiptNotifierInterface
{
    /**
     * @var array<string, string>
     */
    public array $sent = [];

    public function sendReceipt(Sale $sale, string $recipientEmail): void
    {
        $this->sent[$sale->id()] = $recipientEmail;
    }
}


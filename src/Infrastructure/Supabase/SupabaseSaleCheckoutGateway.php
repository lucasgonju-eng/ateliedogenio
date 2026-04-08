<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Supabase;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Service\SaleCheckoutGatewayInterface;
use RuntimeException;

final class SupabaseSaleCheckoutGateway implements SaleCheckoutGatewayInterface
{
    public function __construct(private readonly SupabaseRpcClient $rpcClient)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function finalizeSale(Sale $sale, PaymentMethod $method, array $payload): array
    {
        $response = $this->rpcClient->call('fn_finalize_sale', $payload, [
            'prefer' => 'tx=commit,return=representation',
        ]);

        if (!is_array($response)) {
            throw new RuntimeException('Falha ao finalizar a venda via Supabase.');
        }

        return $response;
    }
}


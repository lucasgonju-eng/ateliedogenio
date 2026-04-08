<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\PaymentConfig;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\PaymentConfigRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PaymentConfigController extends BaseController
{
    public function __construct(private readonly PaymentConfigRepositoryInterface $repository)
    {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $items = array_map(
            fn ($config) => $this->presentConfig($config),
            $this->repository->findAll()
        );

        return $this->json(['items' => $items]);
    }

    public function upsert(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);

        $methodRaw = (string) ($payload['payment_method'] ?? '');
        try {
            $method = PaymentMethod::from(strtolower($methodRaw));
        } catch (\ValueError) {
            return $this->error('VALIDATION_ERROR', 'Método de pagamento inválido.', 422);
        }

        $feePercentage = isset($payload['fee_percentage']) ? (float) $payload['fee_percentage'] : 0.0;
        $feeFixed = isset($payload['fee_fixed']) ? (float) $payload['fee_fixed'] : 0.0;
        $allowDiscount = filter_var($payload['allow_discount'] ?? false, FILTER_VALIDATE_BOOL);
        $maxDiscount = isset($payload['max_discount_percentage']) ? (float) $payload['max_discount_percentage'] : 0.0;

        if ($feePercentage < 0 || $feePercentage > 100) {
            return $this->error('VALIDATION_ERROR', 'Percentual deve estar entre 0 e 100.', 422);
        }

        if ($feeFixed < 0) {
            return $this->error('VALIDATION_ERROR', 'Taxa fixa não pode ser negativa.', 422);
        }

        if ($maxDiscount < 0 || $maxDiscount > 100) {
            return $this->error('VALIDATION_ERROR', 'Desconto máximo deve estar entre 0 e 100.', 422);
        }

        $config = $this->repository->upsert(
            $method,
            $feePercentage,
            $feeFixed,
            $allowDiscount,
            $maxDiscount
        );

        return $this->json($this->presentConfig($config));
    }

    private function presentConfig(PaymentConfig $config): array
    {
        return [
            'id' => $config->id(),
            'payment_method' => $config->method()->value,
            'fee_percentage' => $config->feePercentage(),
            'fee_fixed' => $config->feeFixed(),
            'allow_discount' => $config->allowDiscount(),
            'max_discount_percentage' => $config->maxDiscountPercentage(),
        ];
    }
}

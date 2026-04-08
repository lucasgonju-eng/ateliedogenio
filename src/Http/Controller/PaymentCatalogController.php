<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\CardBrand;
use AtelieDoGenio\Domain\Entity\CardTerminal;
use AtelieDoGenio\Domain\Entity\PaymentFee;
use AtelieDoGenio\Domain\Enum\PaymentMethod;
use AtelieDoGenio\Domain\Repository\CardBrandRepositoryInterface;
use AtelieDoGenio\Domain\Repository\CardTerminalRepositoryInterface;
use AtelieDoGenio\Domain\Repository\PaymentFeeRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PaymentCatalogController extends BaseController
{
    public function __construct(
        private readonly CardBrandRepositoryInterface $brandRepo,
        private readonly CardTerminalRepositoryInterface $terminalRepo,
        private readonly PaymentFeeRepositoryInterface $feeRepo
    ) {
    }

    public function brands(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $items = array_map(fn (CardBrand $b) => [
                'id' => $b->id(),
                'name' => $b->name(),
                'active' => $b->active(),
            ], $this->brandRepo->findAll());

            return $this->json(['items' => $items]);
        } catch (\Throwable) {
            return $this->error('INTERNAL_ERROR', 'Falha ao listar bandeiras. Verifique se as migrations foram aplicadas.', 500);
        }
    }

    public function upsertBrand(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $id = isset($payload['id']) && is_string($payload['id']) ? $payload['id'] : null;
        $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
        $active = filter_var($payload['active'] ?? true, FILTER_VALIDATE_BOOL);

        if ($name === '') {
            return $this->error('VALIDATION_ERROR', 'Nome da bandeira é obrigatório.', 422);
        }

        try {
            $saved = $this->brandRepo->upsert($id, $name, $active);

            return $this->json([
                'id' => $saved->id(),
                'name' => $saved->name(),
                'active' => $saved->active(),
            ]);
        } catch (\Throwable) {
            return $this->error('INTERNAL_ERROR', 'Falha ao salvar bandeira. Verifique se as migrations foram aplicadas.', 500);
        }
    }

    public function terminals(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $items = array_map(fn (CardTerminal $t) => [
                'id' => $t->id(),
                'name' => $t->name(),
                'active' => $t->active(),
            ], $this->terminalRepo->findAll());

            return $this->json(['items' => $items]);
        } catch (\Throwable) {
            return $this->error('INTERNAL_ERROR', 'Falha ao listar maquininhas. Verifique se as migrations foram aplicadas.', 500);
        }
    }

    public function upsertTerminal(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $id = isset($payload['id']) && is_string($payload['id']) ? $payload['id'] : null;
        $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
        $active = filter_var($payload['active'] ?? true, FILTER_VALIDATE_BOOL);

        if ($name === '') {
            return $this->error('VALIDATION_ERROR', 'Nome da maquininha é obrigatório.', 422);
        }

        try {
            $saved = $this->terminalRepo->upsert($id, $name, $active);

            return $this->json([
                'id' => $saved->id(),
                'name' => $saved->name(),
                'active' => $saved->active(),
            ]);
        } catch (\Throwable) {
            return $this->error('INTERNAL_ERROR', 'Falha ao salvar maquininha. Verifique se as migrations foram aplicadas.', 500);
        }
    }

    public function fees(ServerRequestInterface $request): ResponseInterface
    {
        $q = $this->query($request);
        $terminalId = isset($q['terminal_id']) && is_string($q['terminal_id']) ? $q['terminal_id'] : null;
        $brandId = isset($q['brand_id']) && is_string($q['brand_id']) ? $q['brand_id'] : null;

        try {
            $items = array_map(fn (PaymentFee $f) => $this->presentFee($f), $this->feeRepo->find($terminalId, $brandId));
            return $this->json(['items' => $items]);
        } catch (\Throwable) {
            return $this->error('INTERNAL_ERROR', 'Falha ao listar taxas. Verifique se as migrations foram aplicadas.', 500);
        }
    }

    public function upsertFee(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);

        $id = isset($payload['id']) && is_string($payload['id']) ? $payload['id'] : null;
        $terminalId = (string)($payload['terminal_id'] ?? '');
        $brandId = (string)($payload['brand_id'] ?? '');
        $methodRaw = (string)($payload['payment_method'] ?? '');
        $feePercentage = isset($payload['fee_percentage']) ? (float)$payload['fee_percentage'] : 0.0;
        $feeFixed = isset($payload['fee_fixed']) ? (float)$payload['fee_fixed'] : 0.0;
        $installmentsMin = isset($payload['installments_min']) ? (int)$payload['installments_min'] : 1;
        $installmentsMax = isset($payload['installments_max']) ? (int)$payload['installments_max'] : $installmentsMin;
        $perInstallmentPct = isset($payload['per_installment_percentage']) ? (float)$payload['per_installment_percentage'] : 0.0;
        $confirmationFixedFee = isset($payload['confirmation_fixed_fee']) ? (float)$payload['confirmation_fixed_fee'] : 0.0;

        if ($terminalId === '' || $brandId === '') {
            return $this->error('VALIDATION_ERROR', 'Selecione maquininha e bandeira.', 422);
        }

        try {
            $method = PaymentMethod::from(strtolower($methodRaw));
        } catch (\Throwable) {
            return $this->error('VALIDATION_ERROR', 'Método de pagamento inválido.', 422);
        }

        if ($feePercentage < 0 || $feePercentage > 100) {
            return $this->error('VALIDATION_ERROR', 'Percentual deve estar entre 0 e 100.', 422);
        }
        if ($feeFixed < 0) {
            return $this->error('VALIDATION_ERROR', 'Taxa fixa não pode ser negativa.', 422);
        }
        // Valida parcelas (apenas para crédito)
        if ($method === PaymentMethod::CREDITO) {
            if ($installmentsMin < 1) {
                return $this->error('VALIDATION_ERROR', 'Parcelas mínimas deve ser >= 1.', 422);
            }
            if ($installmentsMax < $installmentsMin) {
                return $this->error('VALIDATION_ERROR', 'Parcelas máximas deve ser >= parcelas mínimas.', 422);
            }
            if ($perInstallmentPct < 0 || $perInstallmentPct > 100) {
                return $this->error('VALIDATION_ERROR', 'Percentual por parcela deve estar entre 0 e 100.', 422);
            }
        } else {
            // Força 1x para métodos não-crédito
            $installmentsMin = 1;
            $installmentsMax = 1;
            $perInstallmentPct = 0.0;
        }
        if ($confirmationFixedFee < 0) {
            return $this->error('VALIDATION_ERROR', 'Tarifa de confirmação não pode ser negativa.', 422);
        }

        try {
            $saved = $this->feeRepo->upsert(
                $id,
                $terminalId,
                $brandId,
                $method,
                $feePercentage,
                $feeFixed,
                $installmentsMin,
                $installmentsMax,
                $perInstallmentPct,
                $confirmationFixedFee
            );
            return $this->json($this->presentFee($saved));
        } catch (\Throwable) {
            return $this->error('INTERNAL_ERROR', 'Falha ao salvar taxa. Verifique se as migrations foram aplicadas.', 500);
        }
    }

    public function creditOptions(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $fees = $this->feeRepo->find(null, null);
        } catch (\Throwable) {
            return $this->json(['terminals' => [], 'brands' => [], 'count' => 0]);
        }

        $terminalIds = [];
        $brandIds = [];

        foreach ($fees as $f) {
            if ($f->method() !== PaymentMethod::CREDITO) {
                continue;
            }
            $terminalIds[$f->terminalId()] = true;
            $brandIds[$f->brandId()] = true;
        }

        $terminalsAll = $this->terminalRepo->findAll();
        $brandsAll = $this->brandRepo->findAll();

        $terminals = array_values(array_filter(array_map(function (CardTerminal $t) use ($terminalIds): array {
            return [
                'id' => $t->id(),
                'name' => $t->name(),
                'active' => $t->active(),
            ];
        }, $terminalsAll), function (array $t) use ($terminalIds): bool { return isset($terminalIds[$t['id']]); }));

        $brands = array_values(array_filter(array_map(function (CardBrand $b) use ($brandIds): array {
            return [
                'id' => $b->id(),
                'name' => $b->name(),
                'active' => $b->active(),
            ];
        }, $brandsAll), function (array $b) use ($brandIds): bool { return isset($brandIds[$b['id']]); }));

        return $this->json([
            'terminals' => $terminals,
            'brands' => $brands,
            'count' => count($fees),
        ]);
    }

    private function presentFee(PaymentFee $f): array
    {
        return [
            'id' => $f->id(),
            'terminal_id' => $f->terminalId(),
            'brand_id' => $f->brandId(),
            'payment_method' => $f->method()->value,
            'fee_percentage' => $f->feePercentage(),
            'fee_fixed' => $f->feeFixed(),
            'installments_min' => $f->installmentsMin(),
            'installments_max' => $f->installmentsMax(),
            'per_installment_percentage' => $f->perInstallmentPercentage(),
            'confirmation_fixed_fee' => $f->confirmationFixedFee(),
        ];
    }
}

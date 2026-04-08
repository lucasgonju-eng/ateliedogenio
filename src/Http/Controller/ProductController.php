<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\Product;
use AtelieDoGenio\Domain\Entity\ProductVariant;
use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\ValueObject\Money;
use AtelieDoGenio\Http\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AtelieDoGenio\Domain\Inventory\ProductSizeCatalog;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;
use AtelieDoGenio\Domain\Service\AuditLogger;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;

final class ProductController extends BaseController
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductVariantRepositoryInterface $variants,
        private readonly AuditLogger $auditLogger,
        private readonly SupabaseClient $supabase
    ) {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);

        $filters = [];
        if (isset($query['search'])) {
            $filters['search'] = (string) $query['search'];
        }
        if (isset($query['limit'])) {
            $filters['limit'] = max(1, (int) $query['limit']);
        }
        if (isset($query['offset'])) {
            $filters['offset'] = max(0, (int) $query['offset']);
        }
        if (isset($query['active'])) {
            $filters['active'] = filter_var($query['active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $items = $this->products->search($filters);
        $payload = array_map($this->presentProduct(...), $items);

        $response = $this->json([
            'items' => $payload,
            'count' => count($payload),
        ]);

        return $response->withHeader('X-Total-Count', (string) count($payload));
    }

    public function show(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $id = $params['id'] ?? null;

        if ($id === null) {
            return $this->error('VALIDATION_ERROR', 'ID do produto e obrigatorio.', 422);
        }

        $product = $this->products->findById($id);

        if ($product === null) {
            return $this->error('NOT_FOUND', 'Produto não encontrado.', 404);
        }

        return $this->json($this->presentProduct($product));
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);

        foreach (['sku', 'name', 'supplier_cost', 'sale_price'] as $field) {
            if (!isset($payload[$field])) {
                return $this->error('VALIDATION_ERROR', sprintf('Campo %s e obrigatorio.', $field), 422);
            }
        }

        $size = isset($payload['size']) ? trim((string) $payload['size']) : '';
        if ($size !== '' && !in_array($size, ProductSizeCatalog::all(), true)) {
            return $this->error('VALIDATION_ERROR', 'Tamanho informado nao e valido.', 422);
        }

        try {
            $product = $this->products->create($payload);
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('PRODUCT_ERROR', $exception->getMessage(), 500);
        }

        if ($size !== '') {
            try {
                $this->variants->setQuantity($product->id(), $size, $product->stock());
            } catch (\Throwable $exception) {
                return $this->error('SIZE_VARIANTS_UNAVAILABLE', 'Falha ao salvar o tamanho inicial do produto.', 500);
            }
        }

        $user = $this->user($request);
        $actorId = $user['id'] !== '' ? $user['id'] : null;
        $actorRole = $user['role'] ?? null;

        $this->auditLogger->record(
            action: 'product.created',
            entity: 'product',
            entityId: $product->id(),
            actorId: $actorId,
            actorRole: $actorRole,
            payload: [
                'sku' => $product->sku(),
                'name' => $product->name(),
                'supplier_cost' => $product->supplierCost()->toFloat(),
                'sale_price' => $product->salePrice()->toFloat(),
                'stock' => $product->stock(),
                'size' => $size !== '' ? $size : null,
            ]
        );

        return JsonResponse::success($this->presentProduct($product), 201);
    }

    public function update(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $id = $params['id'] ?? null;

        if ($id === null) {
            return $this->error('VALIDATION_ERROR', 'ID do produto e obrigatorio.', 422);
        }

        $existing = $this->products->findById($id);

        if ($existing === null) {
            return $this->error('NOT_FOUND', 'Produto não encontrado.', 404);
        }

        $payload = $this->input($request);

        $product = new Product(
            id: $existing->id(),
            sku: (string) ($payload['sku'] ?? $existing->sku()),
            name: (string) ($payload['name'] ?? $existing->name()),
            description: $payload['description'] ?? $existing->description(),
            supplierCost: Money::fromFloat((float) ($payload['supplier_cost'] ?? $existing->supplierCost()->toFloat())),
            salePrice: Money::fromFloat((float) ($payload['sale_price'] ?? $existing->salePrice()->toFloat())),
            stock: (int) ($payload['stock'] ?? $existing->stock()),
            minStockAlert: (int) ($payload['min_stock_alert'] ?? $existing->minStockAlert()),
            active: isset($payload['active']) ? (bool) $payload['active'] : $existing->isActive()
        );

        try {
            $this->products->save($product);
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('PRODUCT_ERROR', $exception->getMessage(), 500);
        }

        $user = $this->user($request);
        $actorId = $user['id'] !== '' ? $user['id'] : null;
        $actorRole = $user['role'] ?? null;

        $this->auditLogger->record(
            action: 'product.updated',
            entity: 'product',
            entityId: $product->id(),
            actorId: $actorId,
            actorRole: $actorRole,
            payload: [
                'fields' => array_intersect_key(
                    $payload,
                    array_flip([
                        'sku',
                        'name',
                        'description',
                        'supplier_cost',
                        'sale_price',
                        'stock',
                        'min_stock_alert',
                        'active',
                    ])
                ),
            ]
        );

        return $this->json($this->presentProduct($product));
    }

    private function presentProduct(Product $product): array
    {
        return [
            'id' => $product->id(),
            'sku' => $product->sku(),
            'name' => $product->name(),
            'description' => $product->description(),
            'supplier_cost' => $product->supplierCost()->toFloat(),
            'sale_price' => $product->salePrice()->toFloat(),
            'stock' => $product->stock(),
            'min_stock_alert' => $product->minStockAlert(),
            'active' => $product->isActive(),
        ];
    }

        public function options(ServerRequestInterface $request): ResponseInterface
    {
        return $this->supabase->runWithServiceRole(function (): ResponseInterface {
            try {
                $source = 'active';
                $products = $this->products->search(['active' => true, 'limit' => 1000]);

                if ($products === [] ) {
                    // Fallback: sem filtro de ativo, para garantir catálogo mínimo
                    $source = 'fallback';
                    error_log('[OPTIONS] Nenhum produto ativo encontrado. Aplicando fallback sem filtro.');
                    $products = $this->products->search(['limit' => 1000]);
                }

                $items = array_map(static function (Product $product): array {
                    return [
                        'id' => $product->id(),
                        'name' => $product->name(),
                        'sku' => $product->sku(),
                        'sale_price' => $product->salePrice()->toFloat(),
                    ];
                }, $products);

                return $this->json([
                    'items' => $items,
                    'count' => count($items),
                    'source' => $source,
                ]);
            } catch (\Throwable $e) {
                error_log('[OPTIONS] Erro ao carregar produtos: ' . $e->getMessage());
                return $this->json([
                    'items' => [],
                    'count' => 0,
                    'source' => 'error',
                ]);
            }
        });
    }

    public function sizes(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $productId = $params['id'] ?? null;

        if ($productId === null) {
            return $this->error('VALIDATION_ERROR', 'ID do produto ? obrigat?rio.', 422);
        }

        return $this->supabase->runWithServiceRole(function () use ($productId): ResponseInterface {
            $product = $this->products->findById($productId);
            if ($product === null) {
                return $this->error('NOT_FOUND', 'Produto n?o encontrado.', 404);
            }

            try {
                $response = $this->buildSizeResponse($productId);
            } catch (\RuntimeException $exception) {
                error_log(sprintf('SIZE_FALLBACK product=%s reason=%s', $productId, $exception->getMessage()));
                $response = $this->buildDefaultSizeResponse($productId);
            }

            return $this->json($response);
        });
    }

    public function updateSizeStock(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $productId = $params['id'] ?? null;

        if ($productId === null) {
            return $this->error('VALIDATION_ERROR', 'ID do produto ? obrigat?rio.', 422);
        }

        return $this->supabase->runWithServiceRole(function () use ($request, $productId): ResponseInterface {
            $product = $this->products->findById($productId);
            if ($product === null) {
                return $this->error('NOT_FOUND', 'Produto n?o encontrado.', 404);
            }

            $payload = $this->input($request);
            $size = isset($payload['size']) ? trim((string) $payload['size']) : '';

            if ($size === '') {
                return $this->error('VALIDATION_ERROR', 'Tamanho ? obrigat?rio.', 422);
            }

            $mode = strtolower((string) ($payload['mode'] ?? 'set'));
            $quantity = (int) ($payload['quantity'] ?? 0);

            try {
                switch ($mode) {
                    case 'increase':
                    case 'increment':
                        $this->variants->increment($productId, $size, max(0, $quantity));
                        break;
                    case 'decrease':
                    case 'decrement':
                        $this->variants->increment($productId, $size, -max(0, $quantity));
                        break;
                    default:
                        $this->variants->setQuantity($productId, $size, $quantity);
                }

                $total = $this->variants->sumForProduct($productId);
                $currentStock = $product->stock();
                if ($total > $currentStock) {
                    $product->increaseStock($total - $currentStock);
                } elseif ($total < $currentStock) {
                    $product->decreaseStock($currentStock - $total);
                }
                $this->products->save($product);
            } catch (\RuntimeException $exception) {
                error_log(sprintf('SIZE_UPDATE_FALLBACK product=%s size=%s reason=%s', $productId, $size, $exception->getMessage()));
                return $this->error('SIZE_VARIANTS_UNAVAILABLE', 'Cadastro de tamanhos indisponivel. Confirme migracoes no banco.', 503);
            }

            $user = $this->user($request);
            $actorId = $user['id'] !== '' ? $user['id'] : null;
            $actorRole = $user['role'] ?? null;

            $this->auditLogger->record(
                action: 'product.size_stock.updated',
                entity: 'product',
                entityId: $productId,
                actorId: $actorId,
                actorRole: $actorRole,
                payload: [
                    'size' => $size,
                    'mode' => $mode,
                    'quantity' => $quantity,
                ]
            );

            return $this->json($this->buildSizeResponse($productId));
        });
    }

    /**
     * @return array{product_id: string, total: int, sizes: list<array{size: string, group: string, quantity: int}>}
     */
    private function buildSizeResponse(string $productId): array
    {
        $variants = $this->variants->listByProduct($productId);
        $quantityBySize = [];
        foreach ($variants as $variant) {
            $quantityBySize[$variant->size()] = $variant->quantity();
        }

        $sizes = [];
        $total = array_reduce(
            $variants,
            static fn (int $carry, ProductVariant $variant): int => $carry + $variant->quantity(),
            0
        );
        $seen = [];

        foreach (ProductSizeCatalog::grouped() as $group => $groupSizes) {
            foreach ($groupSizes as $size) {
                $quantity = (int) ($quantityBySize[$size] ?? 0);
                $sizes[] = [
                    'size' => $size,
                    'group' => $group,
                    'quantity' => $quantity,
                ];
                $seen[$size] = true;
            }
        }

        foreach ($variants as $variant) {
            $size = $variant->size();
            if (isset($seen[$size])) {
                continue;
            }

            $quantity = $variant->quantity();
            $sizes[] = [
                'size' => $size,
                'group' => 'outros',
                'quantity' => $quantity,
            ];
        }

        return [
            'product_id' => $productId,
            'total' => $total,
            'sizes' => $sizes,
        ];
    }

    /**
     * @return array{product_id: string, total: int, sizes: list<array{size: string, group: string, quantity: int}>}
     */
    private function buildDefaultSizeResponse(string $productId): array
    {
        $sizes = [];
        foreach (ProductSizeCatalog::grouped() as $group => $groupSizes) {
            foreach ($groupSizes as $size) {
                $sizes[] = [
                    'size' => $size,
                    'group' => $group,
                    'quantity' => 0,
                ];
            }
        }

        return [
            'product_id' => $productId,
            'total' => 0,
            'sizes' => $sizes,
        ];
    }
}



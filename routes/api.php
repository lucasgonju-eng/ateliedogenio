<?php

declare(strict_types=1);

use AtelieDoGenio\Infrastructure\Container\Container;
use AtelieDoGenio\Infrastructure\Http\Routing\RouteGroup;
use AtelieDoGenio\Http\Controller\AuthController;
use AtelieDoGenio\Http\Controller\ProductController;
use AtelieDoGenio\Http\Controller\PaymentConfigController;
use AtelieDoGenio\Http\Controller\PaymentCatalogController;
use AtelieDoGenio\Http\Controller\SaleController;
use AtelieDoGenio\Http\Controller\DashboardController;
use AtelieDoGenio\Http\Controller\CustomerController;
use AtelieDoGenio\Http\Controller\CashLedgerController;
use AtelieDoGenio\Http\Controller\InventoryController;
use AtelieDoGenio\Http\Controller\ReportController;
use AtelieDoGenio\Http\Controller\CommissionController;
use AtelieDoGenio\Http\Controller\UsersController;
use AtelieDoGenio\Http\Controller\WebController;
use AtelieDoGenio\Http\Controller\ItemController;
use AtelieDoGenio\Http\Controller\ReturnController;

return static function (\FastRoute\RouteCollector $router, Container $container): void {
    $group = new RouteGroup($router, $container);

    $group->get('/', [WebController::class, 'home']);
    $group->get('/painel/vendedor', [WebController::class, 'vendorDashboard']);
    $group->get('/painel/admin', [WebController::class, 'adminDashboard']);

    $group->post('/auth/login', [AuthController::class, 'login']);
    $group->post('/auth/logout', [AuthController::class, 'logout'], ['auth']);
    $group->post('/auth/password/forgot', [AuthController::class, 'forgotPassword']);
    $group->post('/auth/password/reset', [AuthController::class, 'resetPassword']);

    $group->get('/products', [ProductController::class, 'index'], ['auth', 'role:admin']);
    $group->get('/products/options', [ProductController::class, 'options'], ['auth']);
    $group->get('/products/{id}/sizes', [ProductController::class, 'sizes'], ['auth']);
    $group->post('/products', [ProductController::class, 'store'], ['auth', 'role:admin']);
    $group->get('/products/{id}', [ProductController::class, 'show'], ['auth']);
    $group->put('/products/{id}', [ProductController::class, 'update'], ['auth', 'role:admin']);
    $group->post('/products/{id}/sizes', [ProductController::class, 'updateSizeStock'], ['auth', 'role:admin']);

    $group->get('/sales', [SaleController::class, 'index'], ['auth']);
    $group->post('/sales', [SaleController::class, 'createDraft'], ['auth']);
    $group->patch('/sales/{id}/status', [SaleController::class, 'updateStatus'], ['auth']);
    $group->post('/sales/{id}/checkout', [SaleController::class, 'checkout'], ['auth']);

    $group->get('/dashboard/vendor', [DashboardController::class, 'vendorSummary'], ['auth', 'role:vendedor']);
    $group->get('/dashboard/admin', [DashboardController::class, 'adminSummary'], ['auth', 'role:admin']);

    $group->get('/customers', [CustomerController::class, 'index'], ['auth']);
    $group->post('/customers', [CustomerController::class, 'store'], ['auth']);
    $group->get('/customers/{id}', [CustomerController::class, 'show'], ['auth']);
    $group->put('/customers/{id}', [CustomerController::class, 'update'], ['auth']);

    $group->get('/inventory/movements', [InventoryController::class, 'movements'], ['auth']);
    $group->post('/inventory/adjust', [InventoryController::class, 'adjust'], ['auth', 'role:admin']);

    $group->get('/cash-ledger', [CashLedgerController::class, 'index'], ['auth']);
    $group->get('/cash-ledger/summary', [CashLedgerController::class, 'summary'], ['auth']);
    $group->get('/cash-ledger/export', [CashLedgerController::class, 'export'], ['auth']);
    $group->post('/cash-ledger/adjustment', [CashLedgerController::class, 'adjust'], ['auth', 'role:admin']);

    // Devolucao com estorno (admin)
    $group->post('/returns', [ReturnController::class, 'store'], ['auth', 'role:admin']);
    $group->get('/returns', [ReturnController::class, 'store'], ['auth', 'role:admin']);

    $group->get('/reports/sales', [ReportController::class, 'sales'], ['auth', 'role:admin']);
    $group->get('/reports/inventory', [ReportController::class, 'inventory'], ['auth', 'role:admin']);

    // Commission summary for vendor
    $group->get('/commission/vendor', [CommissionController::class, 'vendorSummary'], ['auth', 'role:vendedor']);
    $group->get('/commission/admin/vendors', [CommissionController::class, 'adminVendors'], ['auth', 'role:admin']);
    $group->get('/commission/admin/summary', [CommissionController::class, 'adminSummary'], ['auth', 'role:admin']);
    $group->get('/commission/admin/overview', [CommissionController::class, 'adminOverview'], ['auth', 'role:admin']);
    $group->post('/commission/admin/confirm', [CommissionController::class, 'adminConfirm'], ['auth', 'role:admin']);
    $group->post('/commission/admin/upload', [CommissionController::class, 'adminUpload'], ['auth', 'role:admin']);

    $group->get('/payment-config', [PaymentConfigController::class, 'index'], ['auth', 'role:admin']);
    $group->post('/payment-config', [PaymentConfigController::class, 'upsert'], ['auth', 'role:admin']);

    // Catálogo de pagamentos (admin): bandeiras, maquininhas e taxas por combinação
    $group->get('/payment-catalog/brands', [PaymentCatalogController::class, 'brands'], ['auth', 'role:admin']);
    $group->post('/payment-catalog/brands', [PaymentCatalogController::class, 'upsertBrand'], ['auth', 'role:admin']);
    $group->get('/payment-catalog/terminals', [PaymentCatalogController::class, 'terminals'], ['auth', 'role:admin']);
    $group->post('/payment-catalog/terminals', [PaymentCatalogController::class, 'upsertTerminal'], ['auth', 'role:admin']);
    // Leitura de faixas de crédito acessível a usuários autenticados (usado no PDV do vendedor)
    $group->get('/payment-catalog/fees', [PaymentCatalogController::class, 'fees'], ['auth']);
    $group->post('/payment-catalog/fees', [PaymentCatalogController::class, 'upsertFee'], ['auth', 'role:admin']);
    $group->get('/payment-catalog/credit-options', [PaymentCatalogController::class, 'creditOptions'], ['auth']);

    // Users helper (admin-only)
    $group->get('/users/names', [UsersController::class, 'names'], ['auth', 'role:admin']);

        $group->get('/items', [ItemController::class, 'index'], ['auth']);
    $group->post('/items', [ItemController::class, 'store'], ['auth']);
    $group->get('/items/{id}', [ItemController::class, 'show'], ['auth']);
    $group->put('/items/{id}', [ItemController::class, 'update'], ['auth']);
    $group->delete('/items/{id}', [ItemController::class, 'destroy'], ['auth']);

    // Debug endpoints (temporário):
    $group->get('/debug/logs', [\AtelieDoGenio\Http\Controller\DebugController::class, 'logs'], ['auth']);
};

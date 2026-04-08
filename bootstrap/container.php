<?php

declare(strict_types=1);

use AtelieDoGenio\Domain\Repository\AuditLogRepositoryInterface;
use AtelieDoGenio\Domain\Repository\CashLedgerRepositoryInterface;
use AtelieDoGenio\Domain\Repository\CommissionRepositoryInterface;
use AtelieDoGenio\Domain\Repository\CustomerRepositoryInterface;
use AtelieDoGenio\Domain\Repository\InventoryMovementRepositoryInterface;
use AtelieDoGenio\Domain\Repository\PaymentConfigRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductRepositoryInterface;
use AtelieDoGenio\Domain\Repository\ProductVariantRepositoryInterface;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use AtelieDoGenio\Domain\Repository\SalesTargetRepositoryInterface;
use AtelieDoGenio\Domain\Service\AuditLogger;
use AtelieDoGenio\Domain\Service\AuthGatewayInterface;
use AtelieDoGenio\Domain\Service\AuthenticationService;
use AtelieDoGenio\Domain\Service\CashLedgerService;
use AtelieDoGenio\Domain\Service\CommissionService;
use AtelieDoGenio\Domain\Service\InventoryService;
use AtelieDoGenio\Domain\Service\ReceiptNotifierInterface;
use AtelieDoGenio\Domain\Service\ReportService;
use AtelieDoGenio\Domain\Service\SaleCheckoutGatewayInterface;
use AtelieDoGenio\Domain\Service\SaleService;
use AtelieDoGenio\Domain\Service\StockReconciliationService;
use AtelieDoGenio\Domain\Service\TokenDecoderInterface;
use AtelieDoGenio\Domain\Service\ReturnService;
use AtelieDoGenio\Http\Controller\AuthController;
use AtelieDoGenio\Http\Controller\CashLedgerController;
use AtelieDoGenio\Http\Controller\CustomerController;
use AtelieDoGenio\Http\Controller\DashboardController;
use AtelieDoGenio\Http\Controller\InventoryController;
use AtelieDoGenio\Http\Controller\ProductController;
use AtelieDoGenio\Http\Controller\PaymentConfigController;
use AtelieDoGenio\Http\Controller\PaymentCatalogController;
use AtelieDoGenio\Http\Controller\ReportController;
use AtelieDoGenio\Http\Controller\CommissionController;
use AtelieDoGenio\Http\Controller\UsersController;
use AtelieDoGenio\Http\Controller\SaleController;
use AtelieDoGenio\Http\Controller\ReturnController;
use AtelieDoGenio\Http\Controller\WebController;
use AtelieDoGenio\Http\Middleware\AuthenticationMiddleware;
use AtelieDoGenio\Http\Middleware\CorsMiddleware;
use AtelieDoGenio\Http\Middleware\CsrfMiddleware;
use AtelieDoGenio\Http\Middleware\JsonBodyParserMiddleware;
use AtelieDoGenio\Http\Middleware\RateLimitMiddleware;
use AtelieDoGenio\Http\Middleware\RoleMiddleware;
use AtelieDoGenio\Infrastructure\Container\Container;
use AtelieDoGenio\Infrastructure\Email\Mailer;
use AtelieDoGenio\Infrastructure\Email\SaleReceiptNotifier;
use AtelieDoGenio\Infrastructure\Email\SymfonyMailer;
use AtelieDoGenio\Infrastructure\Http\RequestFactory;
use AtelieDoGenio\Infrastructure\Http\ResponseEmitter;
use AtelieDoGenio\Infrastructure\Http\Routing\RouteRegistrar;
use AtelieDoGenio\Infrastructure\Http\Routing\Router;
use AtelieDoGenio\Infrastructure\Logging\LoggerFactory;
use AtelieDoGenio\Infrastructure\Report\ReportExporter;
use AtelieDoGenio\Infrastructure\Report\SimplePdfBuilder;
use AtelieDoGenio\Infrastructure\Repository\AuditLogRepository;
use AtelieDoGenio\Infrastructure\Repository\CashLedgerRepository;
use AtelieDoGenio\Infrastructure\Repository\CommissionRepository;
use AtelieDoGenio\Infrastructure\Repository\CustomerRepository;
use AtelieDoGenio\Infrastructure\Repository\InventoryMovementRepository;
use AtelieDoGenio\Infrastructure\Repository\CardBrandRepository;
use AtelieDoGenio\Infrastructure\Repository\CardTerminalRepository;
use AtelieDoGenio\Infrastructure\Repository\PaymentFeeRepository;
use AtelieDoGenio\Infrastructure\Repository\PaymentConfigRepository;
use AtelieDoGenio\Infrastructure\Repository\ProductRepository;
use AtelieDoGenio\Infrastructure\Repository\ProductVariantRepository;
use AtelieDoGenio\Infrastructure\Repository\SalesRepository;
use AtelieDoGenio\Infrastructure\Repository\SalesTargetRepository;
use AtelieDoGenio\Infrastructure\Security\CsrfTokenManager;
use AtelieDoGenio\Infrastructure\Security\Jwt\JwtDecoder;
use AtelieDoGenio\Infrastructure\Security\SessionManager;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseAuthGateway;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseRpcClient;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseSaleCheckoutGateway;
use AtelieDoGenio\Infrastructure\View\ViewRenderer;
use Nyholm\Psr7\Factory\Psr17Factory;
use AtelieDoGenio\Domain\Repository\CardBrandRepositoryInterface;
use AtelieDoGenio\Domain\Repository\CardTerminalRepositoryInterface;
use AtelieDoGenio\Domain\Repository\PaymentFeeRepositoryInterface;

return new Container([
    LoggerFactory::class => static function (): LoggerFactory {
        $channel = (string) ($_ENV['LOG_CHANNEL'] ?? 'stack');

        return new LoggerFactory($channel, dirname(__DIR__) . '/storage/logs');
    },

    SessionManager::class => static function (): SessionManager {
        $secret = (string) ($_ENV['SESSION_SECRET'] ?? '');

        return new SessionManager($secret);
    },

    CsrfTokenManager::class => static function (Container $container): CsrfTokenManager {
        return new CsrfTokenManager($container->get(SessionManager::class));
    },

    JwtDecoder::class => static function (): JwtDecoder {
        $secret = (string) ($_ENV['SUPABASE_JWT_SECRET'] ?? '');

        return new JwtDecoder($secret);
    },

    TokenDecoderInterface::class => static function (Container $container): TokenDecoderInterface {
        return $container->get(JwtDecoder::class);
    },

    Psr17Factory::class => static function (): Psr17Factory {
        return new Psr17Factory();
    },

    RequestFactory::class => static function (Container $container): RequestFactory {
        return new RequestFactory($container->get(Psr17Factory::class));
    },

    ResponseEmitter::class => static function (Container $container): ResponseEmitter {
        return new ResponseEmitter($container->get(Psr17Factory::class));
    },

    SupabaseClient::class => static function (): SupabaseClient {
        $url = (string) ($_ENV['SUPABASE_URL'] ?? '');
        $anonKey = (string) ($_ENV['SUPABASE_ANON_KEY'] ?? '');
        $serviceKey = (string) ($_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '');

        return new SupabaseClient($url, $anonKey, $serviceKey);
    },

    SupabaseRpcClient::class => static function (Container $container): SupabaseRpcClient {
        return new SupabaseRpcClient($container->get(SupabaseClient::class));
    },

    SymfonyMailer::class => static function (): SymfonyMailer {
        $host = (string) ($_ENV['MAIL_HOST'] ?? 'localhost');
        $port = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $username = (string) ($_ENV['MAIL_USERNAME'] ?? '');
        $password = (string) ($_ENV['MAIL_PASSWORD'] ?? '');
        $encryption = (string) ($_ENV['MAIL_ENCRYPTION'] ?? 'tls');
        $fromAddress = (string) ($_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com');
        $fromName = (string) ($_ENV['MAIL_FROM_NAME'] ?? 'Atelie do Genio');

        return new SymfonyMailer($host, $port, $username, $password, $encryption, $fromAddress, $fromName);
    },

    Mailer::class => static function (Container $container): Mailer {
        return $container->get(SymfonyMailer::class);
    },

    ReceiptNotifierInterface::class => static function (Container $container): ReceiptNotifierInterface {
        return new SaleReceiptNotifier($container->get(Mailer::class));
    },

    SaleCheckoutGatewayInterface::class => static function (Container $container): SaleCheckoutGatewayInterface {
        return new SupabaseSaleCheckoutGateway($container->get(SupabaseRpcClient::class));
    },

    AuthGatewayInterface::class => static function (Container $container): AuthGatewayInterface {
        return new SupabaseAuthGateway($container->get(SupabaseClient::class));
    },

    CashLedgerRepository::class => static function (Container $container): CashLedgerRepository {
        return new CashLedgerRepository($container->get(SupabaseClient::class));
    },
    CashLedgerRepositoryInterface::class => static function (Container $container): CashLedgerRepositoryInterface {
        return $container->get(CashLedgerRepository::class);
    },

    CommissionRepository::class => static function (Container $container): CommissionRepository {
        return new CommissionRepository($container->get(SupabaseClient::class));
    },
    CommissionRepositoryInterface::class => static function (Container $container): CommissionRepositoryInterface {
        return $container->get(CommissionRepository::class);
    },

    CustomerRepository::class => static function (Container $container): CustomerRepository {
        return new CustomerRepository($container->get(SupabaseClient::class));
    },
    CustomerRepositoryInterface::class => static function (Container $container): CustomerRepositoryInterface {
        return $container->get(CustomerRepository::class);
    },

    InventoryMovementRepository::class => static function (Container $container): InventoryMovementRepository {
        return new InventoryMovementRepository(
            $container->get(SupabaseClient::class),
            $container->get(SupabaseRpcClient::class)
        );
    },
    InventoryMovementRepositoryInterface::class => static function (Container $container): InventoryMovementRepositoryInterface {
        return $container->get(InventoryMovementRepository::class);
    },

    CardBrandRepository::class => static function (Container $container): CardBrandRepository {
        return new CardBrandRepository($container->get(SupabaseClient::class));
    },
    CardBrandRepositoryInterface::class => static function (Container $container): CardBrandRepositoryInterface {
        return $container->get(CardBrandRepository::class);
    },

    CardTerminalRepository::class => static function (Container $container): CardTerminalRepository {
        return new CardTerminalRepository($container->get(SupabaseClient::class));
    },
    CardTerminalRepositoryInterface::class => static function (Container $container): CardTerminalRepositoryInterface {
        return $container->get(CardTerminalRepository::class);
    },

    PaymentFeeRepository::class => static function (Container $container): PaymentFeeRepository {
        return new PaymentFeeRepository($container->get(SupabaseClient::class));
    },
    PaymentFeeRepositoryInterface::class => static function (Container $container): PaymentFeeRepositoryInterface {
        return $container->get(PaymentFeeRepository::class);
    },

    PaymentConfigRepository::class => static function (Container $container): PaymentConfigRepository {
        return new PaymentConfigRepository($container->get(SupabaseClient::class));
    },
    PaymentConfigRepositoryInterface::class => static function (Container $container): PaymentConfigRepositoryInterface {
        return $container->get(PaymentConfigRepository::class);
    },

    ProductRepository::class => static function (Container $container): ProductRepository {
        return new ProductRepository($container->get(SupabaseClient::class));
    },
    ProductRepositoryInterface::class => static function (Container $container): ProductRepositoryInterface {
        return $container->get(ProductRepository::class);
    },

    ProductVariantRepository::class => static function (Container $container): ProductVariantRepository {
        return new ProductVariantRepository($container->get(SupabaseClient::class));
    },
    ProductVariantRepositoryInterface::class => static function (Container $container): ProductVariantRepositoryInterface {
        return $container->get(ProductVariantRepository::class);
    },

    SalesRepository::class => static function (Container $container): SalesRepository {
        return new SalesRepository($container->get(SupabaseClient::class));
    },
    SaleRepositoryInterface::class => static function (Container $container): SaleRepositoryInterface {
        return $container->get(SalesRepository::class);
    },

    SalesTargetRepository::class => static function (Container $container): SalesTargetRepository {
        return new SalesTargetRepository($container->get(SupabaseClient::class));
    },
    SalesTargetRepositoryInterface::class => static function (Container $container): SalesTargetRepositoryInterface {
        return $container->get(SalesTargetRepository::class);
    },

    AuditLogRepository::class => static function (Container $container): AuditLogRepository {
        return new AuditLogRepository($container->get(SupabaseClient::class));
    },
    AuditLogRepositoryInterface::class => static function (Container $container): AuditLogRepositoryInterface {
        return $container->get(AuditLogRepository::class);
    },

    SaleService::class => static function (Container $container): SaleService {
        return new SaleService(
            $container->get(SaleRepositoryInterface::class),
            $container->get(ProductRepositoryInterface::class),
            $container->get(ProductVariantRepositoryInterface::class),
            $container->get(PaymentConfigRepositoryInterface::class),
            $container->get(\AtelieDoGenio\Domain\Repository\PaymentFeeRepositoryInterface::class),
            $container->get(ReceiptNotifierInterface::class),
            $container->get(SaleCheckoutGatewayInterface::class),
            $container->get(CommissionService::class),
            $container->get(CashLedgerService::class)
        );
    },

    CommissionService::class => static function (Container $container): CommissionService {
        return new CommissionService(
            $container->get(CommissionRepositoryInterface::class),
            $container->get(SalesTargetRepositoryInterface::class),
            $container->get(CashLedgerService::class)
        );
    },

    InventoryService::class => static function (Container $container): InventoryService {
        return new InventoryService(
            $container->get(ProductRepositoryInterface::class),
            $container->get(InventoryMovementRepositoryInterface::class)
        );
    },

    CashLedgerService::class => static function (Container $container): CashLedgerService {
        return new CashLedgerService(
            $container->get(CashLedgerRepositoryInterface::class)
        );
    },

    ReturnService::class => static function (Container $container): ReturnService {
        return new ReturnService(
            $container->get(ProductRepositoryInterface::class),
            $container->get(ProductVariantRepositoryInterface::class),
            $container->get(CashLedgerService::class)
        );
    },

    AuthenticationService::class => static function (Container $container): AuthenticationService {
        return new AuthenticationService(
            $container->get(AuthGatewayInterface::class),
            $container->get(TokenDecoderInterface::class)
        );
    },

    ReportService::class => static function (Container $container): ReportService {
        return new ReportService(
            $container->get(SaleRepositoryInterface::class),
            $container->get(ProductRepositoryInterface::class),
            $container->get(ProductVariantRepositoryInterface::class)
        );
    },

    AuditLogger::class => static function (Container $container): AuditLogger {
        return new AuditLogger($container->get(AuditLogRepositoryInterface::class));
    },

    StockReconciliationService::class => static function (Container $container): StockReconciliationService {
        return new StockReconciliationService(
            $container->get(ProductRepositoryInterface::class),
            $container->get(ProductVariantRepositoryInterface::class),
            $container->get(InventoryMovementRepositoryInterface::class),
            $container->get(AuditLogger::class)
        );
    },

    SimplePdfBuilder::class => static function (): SimplePdfBuilder {
        return new SimplePdfBuilder();
    },

    ReportExporter::class => static function (Container $container): ReportExporter {
        return new ReportExporter($container->get(SimplePdfBuilder::class));
    },

    Router::class => static function (Container $container): Router {
        $registrar = new RouteRegistrar($container);

        return new Router($registrar->register());
    },

    CorsMiddleware::class => static function (): CorsMiddleware {
        $allowedOriginsEnv = (string) ($_ENV['CORS_ALLOWED_ORIGINS'] ?? '*');
        $allowedOrigins = array_values(array_filter(array_map('trim', explode(',', $allowedOriginsEnv))));

        if ($allowedOrigins === []) {
            $allowedOrigins = ['*'];
        }

        return new CorsMiddleware($allowedOrigins);
    },

    JsonBodyParserMiddleware::class => static function (): JsonBodyParserMiddleware {
        return new JsonBodyParserMiddleware();
    },

    RateLimitMiddleware::class => static function (Container $container): RateLimitMiddleware {
        return new RateLimitMiddleware($container->get(SessionManager::class));
    },

    CsrfMiddleware::class => static function (Container $container): CsrfMiddleware {
        return new CsrfMiddleware($container->get(CsrfTokenManager::class));
    },

    AuthenticationMiddleware::class => static function (Container $container): AuthenticationMiddleware {
        return new AuthenticationMiddleware(
            $container->get(TokenDecoderInterface::class),
            $container->get(SupabaseClient::class)
        );
    },

    RoleMiddleware::class => static function (): RoleMiddleware {
        return new RoleMiddleware();
    },

    ViewRenderer::class => static function (Container $container): ViewRenderer {
        return new ViewRenderer($container->get(Psr17Factory::class));
    },

    WebController::class => static function (Container $container): WebController {
        return new WebController(
            $container->get(ViewRenderer::class),
            $container->get(CsrfTokenManager::class)
        );
    },

    AuthController::class => static function (Container $container): AuthController {
        return new AuthController(
            $container->get(AuthenticationService::class),
            $container->get(CsrfTokenManager::class)
        );
    },

    ProductController::class => static function (Container $container): ProductController {
        return new ProductController(
            $container->get(ProductRepositoryInterface::class),
            $container->get(ProductVariantRepositoryInterface::class),
            $container->get(AuditLogger::class),
            $container->get(SupabaseClient::class)
        );
    },

    SaleController::class => static function (Container $container): SaleController {
        return new SaleController(
            $container->get(SaleRepositoryInterface::class),
            $container->get(SaleService::class)
        );
    },

    CustomerController::class => static function (Container $container): CustomerController {
        return new CustomerController($container->get(CustomerRepositoryInterface::class));
    },

    InventoryController::class => static function (Container $container): InventoryController {
        return new InventoryController($container->get(InventoryService::class));
    },

    ReturnController::class => static function (Container $container): ReturnController {
        return new ReturnController($container->get(ReturnService::class));
    },

    CashLedgerController::class => static function (Container $container): CashLedgerController {
        return new CashLedgerController(
            $container->get(CashLedgerService::class),
            $container->get(SaleRepositoryInterface::class),
            $container->get(ReportExporter::class),
            $container->get(Psr17Factory::class)
        );
    },

    DashboardController::class => static function (Container $container): DashboardController {
        return new DashboardController(
            $container->get(SaleRepositoryInterface::class),
            $container->get(ProductRepositoryInterface::class)
        );
    },

    ReportController::class => static function (Container $container): ReportController {
        return new ReportController(
            $container->get(ReportService::class),
            $container->get(ReportExporter::class),
            $container->get(Psr17Factory::class)
        );
    },

    CommissionController::class => static function (Container $container): CommissionController {
        return new CommissionController(
            $container->get(SaleRepositoryInterface::class),
            $container->get(SupabaseClient::class),
            $container->get(SymfonyMailer::class)
        );
    },

    PaymentConfigController::class => static function (Container $container): PaymentConfigController {
        return new PaymentConfigController(
            $container->get(PaymentConfigRepositoryInterface::class)
        );
    },

    UsersController::class => static function (Container $container): UsersController {
        return new UsersController(
            $container->get(SupabaseClient::class)
        );
    },

    PaymentCatalogController::class => static function (Container $container): PaymentCatalogController {
        return new PaymentCatalogController(
            $container->get(CardBrandRepositoryInterface::class),
            $container->get(CardTerminalRepositoryInterface::class),
            $container->get(PaymentFeeRepositoryInterface::class)
        );
    },
]);

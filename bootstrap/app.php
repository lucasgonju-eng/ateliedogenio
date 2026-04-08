<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use AtelieDoGenio\Infrastructure\Logging\LoggerFactory;
use AtelieDoGenio\Infrastructure\Http\HttpKernel;
use AtelieDoGenio\Infrastructure\Security\SessionManager;

require_once __DIR__ . '/helpers.php';

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'AtelieDoGenio\\';
        $baseDir = dirname(__DIR__) . '/src/';

        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        }
    });
}

$rootPath = dirname(__DIR__);

if (file_exists($rootPath . '/.env')) {
    Dotenv::createImmutable($rootPath)->safeLoad();
}

$container = require __DIR__ . '/container.php';

$loggerFactory = $container->get(LoggerFactory::class);
$logger = $loggerFactory->createDefaultLogger();

$sessionManager = $container->get(SessionManager::class);
$sessionManager->start();

return new HttpKernel($container, $logger);

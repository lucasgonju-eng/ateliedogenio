#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);

require_once $rootPath . '/vendor/autoload.php';
require_once $rootPath . '/bootstrap/helpers.php';

if (file_exists($rootPath . '/.env')) {
    Dotenv\Dotenv::createImmutable($rootPath)->safeLoad();
}

$container = require $rootPath . '/bootstrap/container.php';

/** @var \AtelieDoGenio\Infrastructure\Supabase\SupabaseClient $supabase */
$supabase = $container->get(\AtelieDoGenio\Infrastructure\Supabase\SupabaseClient::class);
$supabase->useServiceRole();

$options = getopt('', ['actor::', 'role::']);

$actorId = isset($options['actor']) ? (string) $options['actor'] : null;
$actorRole = isset($options['role']) ? (string) $options['role'] : 'system';

/** @var \AtelieDoGenio\Domain\Service\StockReconciliationService $service */
$service = $container->get(\AtelieDoGenio\Domain\Service\StockReconciliationService::class);

$result = $service->reconcileAll($actorId, $actorRole);

printf(
    "Reconciliação concluída. Produtos verificados: %d | Ajustados: %d\n",
    $result['checked'],
    $result['adjusted']
);

if ($result['adjusted'] > 0) {
    foreach ($result['adjustments'] as $adjustment) {
        printf(
            "- Produto %s | Delta: %+d | Estoque Anterior: %d | Variantes: %d\n",
            $adjustment['product_id'],
            $adjustment['delta'],
            $adjustment['previous_stock'],
            $adjustment['variant_total']
        );
    }
}

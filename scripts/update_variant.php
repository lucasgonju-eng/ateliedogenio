<?php

declare(strict_types=1);

use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$productId = $argv[1] ?? null;
$size = $argv[2] ?? null;
$quantity = isset($argv[3]) ? (int) $argv[3] : 0;

if ($productId === null || $size === null) {
    fwrite(STDERR, "Usage: php scripts/update_variant.php <product_id> <size> [quantity]\n");
    exit(1);
}

$client = new SupabaseClient(
    (string) ($_ENV['SUPABASE_URL'] ?? ''),
    (string) ($_ENV['SUPABASE_ANON_KEY'] ?? ''),
    (string) ($_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '')
);

$client->useServiceRole();

$response = $client->request('PATCH', sprintf('rest/v1/product_variants?product_id=eq.%s&size=eq.%s', $productId, $size), [
    'json' => [
        'quantity' => $quantity,
    ],
    'headers' => ['Prefer' => 'return=representation'],
]);

var_export($response);
echo PHP_EOL;

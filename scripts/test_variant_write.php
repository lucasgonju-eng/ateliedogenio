<?php

declare(strict_types=1);

use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$client = new SupabaseClient(
    (string) ($_ENV['SUPABASE_URL'] ?? ''),
    (string) ($_ENV['SUPABASE_ANON_KEY'] ?? ''),
    (string) ($_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '')
);

$client->useServiceRole();

$productId = '00000000-0000-0000-0000-000000000000';

$payload = [[
    'product_id' => $productId,
    'size' => 'PP',
    'quantity' => 1,
]];

$response = $client->request('POST', 'rest/v1/product_variants', [
    'json' => $payload,
    'headers' => ['Prefer' => 'return=representation'],
]);

var_export($response);
echo PHP_EOL;

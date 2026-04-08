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

$response = $client->request('GET', 'rest/v1/products', [
    'query' => [
        'select' => 'id,sku,name',
        'limit' => '20',
        'order' => 'name.asc',
    ],
]);

var_export($response);
echo PHP_EOL;

<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use AtelieDoGenio\Infrastructure\Supabase\SalesRepository;

// >>>>>> PREENCHA COM OS SEUS DADOS <<<<<<
$BASE_URL   = 'https://tuufavmczgdwcwblnumr.supabase.co';
$ANON_KEY   = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR1dWZhdm1jemdkd2N3YmxudW1yIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjAzMDIzMTAsImV4cCI6MjA3NTg3ODMxMH0.Rop-6KanR6eEngyaUZiLEFhhdaVK6ph1gyF6_MdE0j4';
$SERVICE_KEY= 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR1dWZhdm1jemdkd2N3YmxudW1yIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MDMwMjMxMCwiZXhwIjoyMDc1ODc4MzEwfQ.PhGkxaZdF3R7zMh6F6Ip41IWxQr-bJu4sb01Ie0UEhU';

// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

$email = 'venda@teste.com';

$client = new SupabaseClient($BASE_URL, $ANON_KEY, $SERVICE_KEY);
$repo   = new SalesRepository($client);


try {
    echo "Teste ANON (RLS ativo):\n";
    $client->useAnonRole();
    $res1 = $repo->listByVendorEmail($email, limit: 10);
    var_dump($res1);

    echo "\nTeste SERVICE ROLE (RLS ignorado):\n";
    $client->useServiceRole();
    $res2 = $repo->listByVendorEmail($email, limit: 10);
    var_dump($res2);

    // ---------------------------------------------
// Exemplo com filtro de data (últimos 7 dias)
// ---------------------------------------------
$to   = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('c'); // ISO 8601
$from = (new DateTimeImmutable('-7 days', new DateTimeZone('UTC')))->format('c');

try {
    $salesRange = $client->request('GET', 'rest/v1/v_sales_with_vendor', [
        'query' => [
            'select'       => 'id,created_at,total,status,vendor_email',
            'vendor_email' => 'eq.venda@teste.com',
            // Use AND lógico para combinar gte/lte sem repetir a mesma chave
            'and'          => sprintf('(created_at.gte.%s,created_at.lte.%s)', $from, $to),
            'order'        => 'created_at.desc',
            'limit'        => 10,
        ],
    ]);
    var_dump($salesRange);
} catch (RuntimeException $e) {
    echo "ERRO (filtro por data): " . $e->getMessage() . PHP_EOL;
}


    echo "\nFim do teste.\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . PHP_EOL;
}

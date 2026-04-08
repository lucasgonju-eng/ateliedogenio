<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Supabase;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class SalesRepository
{
    public function __construct(private SupabaseClient $client)
    {
    }

    /**
     * Lista vendas por e-mail do vendedor.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listByVendorEmail(string $email, int $limit = 20): array
    {
        $resp = $this->client->request('GET', 'rest/v1/v_sales_with_vendor', [
            'query' => [
                'select'       => 'id,created_at,total,status,vendor_email',
                'vendor_email' => 'eq.' . $email,
                'order'        => 'created_at.desc',
                'limit'        => $limit,
            ],
        ]);

        return is_array($resp) ? $resp : [];
    }

    /**
     * Última venda (mais recente) por e-mail do vendedor.
     *
     * @return array<string, mixed>|null
     */
    public function latestByVendorEmail(string $email): ?array
    {
        $resp = $this->client->request('GET', 'rest/v1/v_sales_with_vendor', [
            'query' => [
                'select'       => 'id,created_at,total,status,vendor_email',
                'vendor_email' => 'eq.' . $email,
                'order'        => 'created_at.desc',
                'limit'        => 1,
            ],
        ]);

        if (is_array($resp) && isset($resp[0]) && is_array($resp[0])) {
            return $resp[0];
        }

        return null;
    }

    /**
     * Lista por e-mail e intervalo de datas (UTC), usando o operador AND do PostgREST.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listByVendorEmailBetween(string $email, DateTimeImmutable $fromUtc, DateTimeImmutable $toUtc, int $limit = 50): array
    {
        $fromIso = $fromUtc->format('Y-m-d\TH:i:s\Z');
        $toIso   = $toUtc->format('Y-m-d\TH:i:s\Z');

        $resp = $this->client->request('GET', 'rest/v1/v_sales_with_vendor', [
            'query' => [
                'select'       => 'id,created_at,total,status,vendor_email',
                'vendor_email' => 'eq.' . $email,
                // Usa "and=(created_at.gte...,created_at.lte...)"
                'and'          => sprintf('(created_at.gte.%s,created_at.lte.%s)', $fromIso, $toIso),
                'order'        => 'created_at.desc',
                'limit'        => $limit,
            ],
        ]);

        return is_array($resp) ? $resp : [];
    }

    /**
     * Lista últimos N dias (em UTC).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listByVendorEmailLastDays(string $email, int $days, int $limit = 50): array
    {
        $to   = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $from = $to->sub(new DateInterval('P' . max(1, $days) . 'D'));

        return $this->listByVendorEmailBetween($email, $from, $to, $limit);
    }

    /**
     * Soma total no intervalo (em PHP, já que a view não tem agregação).
     */
    public function sumByVendorEmailBetween(string $email, DateTimeImmutable $fromUtc, DateTimeImmutable $toUtc): float
    {
        $rows = $this->listByVendorEmailBetween($email, $fromUtc, $toUtc, 1000);
        $sum = 0.0;

        foreach ($rows as $r) {
            // total pode vir como float ou string
            if (isset($r['total'])) {
                $sum += (float)$r['total'];
            }
        }

        return $sum;
    }

    /**
     * Paginação simples via offset.
     *
     * @return array<int, array<string, mixed>>
     */
    public function paginateByVendorEmail(string $email, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $resp = $this->client->request('GET', 'rest/v1/v_sales_with_vendor', [
            'query' => [
                'select'       => 'id,created_at,total,status,vendor_email',
                'vendor_email' => 'eq.' . $email,
                'order'        => 'created_at.desc',
                'limit'        => $perPage,
                'offset'       => $offset,
            ],
        ]);

        return is_array($resp) ? $resp : [];
    }
}

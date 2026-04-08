<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Supabase;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

final class SupabaseClient implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ClientInterface $httpClient;
    private ?string $authToken = null;
    private bool $usingServiceRole = false;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $anonKey,
        private readonly string $serviceKey
    ) {
        if ($baseUrl === '') {
            throw new RuntimeException('Supabase base URL is not configured.');
        }

        $this->httpClient = new Client([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'timeout' => 10,
        ]);
    }

    public function useServiceRole(): void
    {
        $this->authToken = $this->serviceKey;
        $this->usingServiceRole = true;
    }

    public function useAnonRole(): void
    {
        $this->authToken = $this->anonKey;
        $this->usingServiceRole = false;
    }

    public function withToken(?string $token): void
    {
        $this->authToken = $token;
        $this->usingServiceRole = false;
    }

    public function resetToken(): void
    {
        $this->authToken = null;
        $this->usingServiceRole = false;
    }

    /**
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function runWithServiceRole(callable $callback): mixed
    {
        $previousToken = $this->authToken;
        $previousUsingServiceRole = $this->usingServiceRole;

        $this->useServiceRole();

        try {
            return $callback();
        } finally {
            $this->authToken = $previousToken;
            $this->usingServiceRole = $previousUsingServiceRole;
        }
    }

    /**
     * Faz uma requisição ao Supabase.
     *
     * Recursos extras (opcionais) via $options:
     * - $options['owner'] = ['userId' => 'uuid', 'strategy' => 'auto'|'user_id'|'vendor_id'|'none']
     *   Se informado, injeta o filtro correto por tabela (ex.: sales/products → vendor_id).
     *   Em 'auto' (padrão), usa vendor_id para sales/products e user_id para outras.
     *   Se for usar vendor_id, remove user_id=eq... já existente na query para evitar 400.
     *
     * - Alias automático de tabela:
     *   Se o path começar com 'rest/v1/product_variants', reescreve para 'rest/v1/products',
     *   remove '&order=size.asc' e troca 'product_id=' por 'id=' na query.
     *
     * @param string $method
     * @param string $path ex.: 'rest/v1/sales?select=*&order=created_at.desc'
     * @param array<string, mixed> $options
     * @return array<string, mixed>|array<int, mixed>|null
     */
    public function request(string $method, string $path, array $options = []): array|null
    {
        // 1) Reescritas de path/query (shims para corrigir erros do log)
        $path = $this->applyPathShims($path);

        // 2) Filtro por dono opcional
        if (isset($options['owner']) && is_array($options['owner'])) {
            /** @var array{userId?:string, strategy?:string} $owner */
            $owner = $options['owner'];
            $userId = (string)($owner['userId'] ?? '');
            $strategy = (string)($owner['strategy'] ?? 'auto');

            if ($userId !== '') {
                $path = $this->injectOwnerFilterIntoPath($path, $userId, $strategy);
            }
            unset($options['owner']);
        }

        $headers = [
            'apikey' => $this->usingServiceRole ? $this->serviceKey : $this->anonKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->authToken !== null && $this->authToken !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->authToken;
        }

        $options['headers'] = array_merge($headers, $options['headers'] ?? []);

        if (isset($options['json'])) {
            $options['body'] = json_encode($options['json'], JSON_THROW_ON_ERROR);
            unset($options['json']);
        }

        $this->logger?->debug('Supabase request', [
            'method' => $method,
            'path' => $path,
            'using_service_role' => $this->usingServiceRole,
            'options' => $this->sanitizeLogContext($options),
        ]);

        try {
            $response = $this->httpClient->request($method, ltrim($path, '/'), $options);
        } catch (GuzzleException $exception) {
            $this->logger?->error('Supabase request failed', [
                'method' => $method,
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Supabase request failed: ' . $exception->getMessage(), 0, $exception);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->logger?->debug('Supabase response', [
            'method' => $method,
            'path' => $path,
            'status' => $status,
            'body' => $this->decodeBodyForLog($body),
        ]);

        if ($body === '') {
            return null;
        }

        /** @var array<string, mixed>|array<int, mixed> $decoded */
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * Aplica correções conhecidas ao path:
     * - Alinha product_variants → products (troca tabela, remove order=size.asc, troca product_id= por id=)
     */
    private function applyPathShims(string $path): string
    {
        // Removido alias automático que reescrevia product_variants → products,
        // pois quebrava as operações de tamanhos/variantes (400 column products.product_id does not exist).
        // Mantemos o path original.
        return $path;
    }

    /**
     * Injeta filtro de dono a depender da tabela no path rest/v1/{table}?...
     * strategy:
     *  - 'auto' (default): sales/products → vendor_id; outras → user_id
     *  - 'vendor_id' | 'user_id' | 'none'
     */
    private function injectOwnerFilterIntoPath(string $path, string $userId, string $strategy = 'auto'): string
    {
        $prefix = 'rest/v1/';
        $trimmed = ltrim($path, '/');

        if (!str_starts_with($trimmed, $prefix)) {
            return $path; // não é rota de dados
        }

        $afterPrefix = substr($trimmed, strlen($prefix));
        $parts = explode('?', $afterPrefix, 2);
        $table = strtolower($parts[0] ?? '');
        $query = $parts[1] ?? '';

        $column = $this->resolveOwnerColumn($table, $strategy);

        if ($column === null) {
            // strategy 'none' ou tabela sem owner conhecido
            return $path;
        }

        // Se a tabela usar vendor_id, removemos user_id=eq... já presente (evita 400)
        if ($column === 'vendor_id') {
            $query = preg_replace('/(^|&)user_id=eq\.[^&]*/i', '$1', $query) ?? $query;
        }

        // Evita duplicar filtro se já existir para a coluna certa
        $alreadyHas = (bool) preg_match('/(^|&)' . preg_quote($column, '/') . '=eq\./i', $query);

        if (!$alreadyHas) {
            $query = $this->appendQueryParam($query, sprintf('%s=eq.%s', $column, $userId));
        }

        $query = $this->cleanupQuery($query);

        $rebuilt = $prefix . $parts[0];
        if ($query !== '') {
            $rebuilt .= '?' . $query;
        }

        return $rebuilt;
    }

    /**
     * Decide qual coluna de “owner” usar.
     */
    private function resolveOwnerColumn(string $table, string $strategy): ?string
    {
        $t = strtolower($table);

        // strategy explícita
        if ($strategy === 'vendor_id') {
            return 'vendor_id';
        }
        if ($strategy === 'user_id') {
            return 'user_id';
        }
        if ($strategy === 'none') {
            return null;
        }

        // strategy 'auto'
        if (in_array($t, ['sales', 'products'], true)) {
            return 'vendor_id';
        }

        // fallback genérico
        return 'user_id';
    }

    /**
     * Acrescenta um par k=v à query string (sem sobrescrever existentes).
     */
    private function appendQueryParam(string $query, string $kv): string
    {
        $query = trim($query);
        if ($query === '') {
            return $kv;
        }
        // evita & no final/início
        $query = rtrim($query, '&');
        return $query . '&' . ltrim($kv, '&');
    }

    /**
     * Limpa query string: remove & repetidos, trims etc.
     */
    private function cleanupQuery(string $query): string
    {
        $query = preg_replace('/&&+/', '&', $query) ?? $query;
        $query = preg_replace('/^&|&$/', '', $query) ?? $query;
        $query = trim($query);
        return $query;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function sanitizeLogContext(array $options): array
    {
        if (isset($options['headers'])) {
            $headers = $options['headers'];

            foreach (['Authorization', 'apikey'] as $header) {
                if (isset($headers[$header]) && is_string($headers[$header])) {
                    $headers[$header] = substr($headers[$header], 0, 8) . '***';
                }
            }

            $options['headers'] = $headers;
        }

        if (isset($options['body']) && is_string($options['body'])) {
            $options['body'] = $this->truncateString($options['body']);
        }

        return $options;
    }

    private function decodeBodyForLog(string $body): mixed
    {
        if ($body === '') {
            return null;
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Throwable) {
            // ignore json decode errors, fall back to raw string
        }

        return $this->truncateString($body);
    }

    private function truncateString(string $value, int $length = 500): string
    {
        return strlen($value) > $length
            ? substr($value, 0, $length) . '...'
            : $value;
    }
}

<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Middleware;

use AtelieDoGenio\Domain\Service\TokenDecoderInterface;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TokenDecoderInterface $tokenDecoder,
        private readonly SupabaseClient $supabaseClient
    ) {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $authorization = $request->getHeaderLine('Authorization');

        if ($authorization === '' || !str_starts_with($authorization, 'Bearer ')) {
            return $this->unauthorizedResponse();
        }

        $token = trim(substr($authorization, 7));

        if ($token === '') {
            return $this->unauthorizedResponse();
        }

        try {
            $claims = $this->normalizeData($this->tokenDecoder->decode($token));
        } catch (\Throwable) {
            return $this->unauthorizedResponse();
        }

        $profile = [];

        try {
            $this->supabaseClient->withToken($token);
            $profileResponse = $this->supabaseClient->request('GET', 'auth/v1/user');
            if (is_array($profileResponse)) {
                $profile = $this->normalizeData($profileResponse);
            }
        } catch (RuntimeException) {
            $profile = [];
        }

        $userId = $claims['sub'] ?? ($profile['id'] ?? null);

        $role = $this->resolveRole($claims, $profile);

        error_log('AUTH DEBUG email=' . ($profile['email'] ?? ($claims['email'] ?? '')) . ' role_resolved=' . var_export($role, true));

        if ($role === null || $role === 'authenticated') {
            $role = $this->fetchRoleFromDatabase(
                $profile['email'] ?? ($claims['email'] ?? null),
                $token
            );
        }

        error_log('AUTH DEBUG after fetch role=' . var_export($role, true));

        $user = [
            'id' => $userId,
            'role' => $role,
            'claims' => $claims,
            'profile' => $profile,
        ];

        if ($user['id'] === null) {
            return $this->unauthorizedResponse();
        }

        $this->applySupabaseCredentials($role, $token);

        $request = $request
            ->withAttribute('user', $user)
            ->withAttribute('supabase_token', $token);

        $response = $next($request);

        $this->supabaseClient->resetToken();

        return $response;
    }

    private function unauthorizedResponse(): ResponseInterface
    {
        return new Response(401, ['Content-Type' => 'application/json'], json_encode([
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Autentica????o necess??ria.',
            ],
        ], JSON_THROW_ON_ERROR));
    }

    private function applySupabaseCredentials(?string $role, string $userToken): void
    {
        if ($role === 'admin') {
            $this->supabaseClient->useServiceRole();
            return;
        }

        $this->supabaseClient->withToken($userToken);
    }

    /**
     * @param array<string, mixed> $claims
     * @param array<string, mixed> $profile
     */
    private function resolveRole(array $claims, array $profile): ?string
    {
        $sources = [$claims, $profile];
        $paths = [
            ['role'],
            ['user_metadata', 'role'],
            ['raw_user_meta_data', 'role'],
            ['app_metadata', 'role'],
            ['app_metadata', 'roles'],
            ['metadata', 'role'],
            ['user', 'role'],
        ];

        foreach ($sources as $source) {
            foreach ($paths as $path) {
                $value = $this->getNestedValue($source, $path);

                if (is_string($value) && $value !== '') {
                    return $value;
                }

                if (is_array($value)) {
                    foreach ($value as $candidate) {
                        if (is_string($candidate) && $candidate !== '') {
                            return $candidate;
                        }
                    }
                }
            }
        }

        return null;
    }

    private function fetchRoleFromDatabase(?string $email, string $userToken): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        try {
            $this->supabaseClient->useServiceRole();

            $response = $this->supabaseClient->request('GET', 'rest/v1/users', [
                'headers' => ['Prefer' => 'single-object'],
                'query' => [
                    'select' => 'email,roles(name)',
                    'email' => 'eq.' . $email,
                ],
            ]);
            error_log('AUTH DEBUG db_lookup=' . json_encode($response));
        } catch (RuntimeException) {
            $this->supabaseClient->withToken($userToken);

            return null;
        }

        $this->applySupabaseCredentials(null, $userToken);

        if (!is_array($response)) {
            return null;
        }

        $row = $response;

        if (array_is_list($response)) {
            $row = $response[0] ?? [];
        }

        if (!is_array($row)) {
            return null;
        }

        if (isset($row['roles']['name']) && is_string($row['roles']['name'])) {
            return $row['roles']['name'];
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $path
     */
    private function getNestedValue(array $data, array $path): mixed
    {
        $current = $data;

        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * @template T
     * @param mixed $data
     * @return array<string, mixed>
     */
    private function normalizeData(mixed $data): array
    {
        if (is_array($data)) {
            $normalized = [];

            foreach ($data as $key => $value) {
                $normalized[$key] = is_array($value) || is_object($value)
                    ? $this->normalizeData($value)
                    : $value;
            }

            return $normalized;
        }

        if (is_object($data)) {
            /** @var array<string, mixed> $converted */
            $converted = get_object_vars($data);

            return $this->normalizeData($converted);
        }

        return [];
    }
}


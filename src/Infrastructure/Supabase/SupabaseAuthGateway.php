<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Supabase;

use AtelieDoGenio\Domain\Service\AuthGatewayInterface;
use RuntimeException;

final class SupabaseAuthGateway implements AuthGatewayInterface
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    public function login(string $email, string $password): array
    {
        $response = $this->client->request('POST', 'auth/v1/token?grant_type=password', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        if (!is_array($response)) {
            throw new RuntimeException('Falha ao autenticar com Supabase.');
        }

        return $response;
    }

    public function logout(string $accessToken): void
    {
        $this->client->request('POST', 'auth/v1/logout', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
    }

    public function requestPasswordReset(string $email): void
    {
        $this->client->request('POST', 'auth/v1/recover', [
            'json' => ['email' => $email],
        ]);
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        $this->client->request('POST', 'auth/v1/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'password' => $newPassword,
            ],
        ]);
    }
}


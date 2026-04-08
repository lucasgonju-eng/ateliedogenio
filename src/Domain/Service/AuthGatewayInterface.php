<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

interface AuthGatewayInterface
{
    /**
     * @return array<string, mixed>
     */
    public function login(string $email, string $password): array;

    public function logout(string $accessToken): void;

    public function requestPasswordReset(string $email): void;

    public function resetPassword(string $token, string $newPassword): void;
}


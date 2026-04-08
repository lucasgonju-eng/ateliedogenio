<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

use AtelieDoGenio\Domain\Exception\BusinessRuleException;

final class AuthenticationService
{
    public function __construct(
        private readonly AuthGatewayInterface $authGateway,
        private readonly TokenDecoderInterface $tokenDecoder
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function login(string $email, string $password): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BusinessRuleException('INVALID_EMAIL', 'E-mail inválido.');
        }

        if (strlen($password) < 6) {
            throw new BusinessRuleException('INVALID_PASSWORD', 'Senha precisa ter pelo menos 6 caracteres.');
        }

        $response = $this->authGateway->login($email, $password);

        if (!isset($response['access_token'])) {
            throw new BusinessRuleException('AUTH_FAILED', 'Falha na autenticação.');
        }

        try {
            $claims = $this->tokenDecoder->decode($response['access_token']);
        } catch (\Throwable) {
            // Sem segredo ou falha na decodificação, seguimos sem claims
            $claims = [];
        }
        $response['claims'] = $claims;

        return $response;
    }

    public function logout(string $accessToken): void
    {
        $this->authGateway->logout($accessToken);
    }

    public function requestPasswordReset(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BusinessRuleException('INVALID_EMAIL', 'E-mail inválido.');
        }

        $this->authGateway->requestPasswordReset($email);
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new BusinessRuleException('WEAK_PASSWORD', 'Senha precisa ter pelo menos 8 caracteres.');
        }

        $this->authGateway->resetPassword($token, $newPassword);
    }
}

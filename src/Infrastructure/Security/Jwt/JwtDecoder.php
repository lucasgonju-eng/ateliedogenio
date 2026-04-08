<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Security\Jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use AtelieDoGenio\Domain\Service\TokenDecoderInterface;
use UnexpectedValueException;

final class JwtDecoder implements TokenDecoderInterface
{
    public function __construct(private readonly string $secret)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function decode(string $jwt): array
    {
        if ($this->secret === '') {
            // Sem segredo configurado, não validamos o token aqui.
            // Fluxos protegidos devem buscar o perfil no Supabase com o access token.
            return [];
        }

        $payload = JWT::decode($jwt, new Key($this->secret, 'HS256'));

        return (array) $payload;
    }
}

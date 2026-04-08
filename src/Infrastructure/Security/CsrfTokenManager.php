<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Security;

use RuntimeException;

final class CsrfTokenManager
{
    private const SESSION_KEY = '_csrf_tokens';

    public function __construct(private readonly SessionManager $session)
    {
    }

    public function generate(string $intention): string
    {
        $this->ensureSession();

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY][$intention] = $token;

        return $token;
    }

    public function isValid(string $intention, ?string $token): bool
    {
        $this->ensureSession();

        if ($token === null) {
            return false;
        }

        $stored = $_SESSION[self::SESSION_KEY][$intention] ?? null;

        if (!$stored) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    private function ensureSession(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session must be started before using CSRF tokens.');
        }
    }
}

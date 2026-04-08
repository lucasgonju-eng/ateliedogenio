<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Security;

final class SessionManager
{
    private bool $started = false;

    public function __construct(private readonly string $secret)
    {
    }

    public function start(): void
    {
        if ($this->started || PHP_SAPI === 'cli') {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_name('ADG_SESSION');
            session_start();
        }

        $this->started = true;
    }

    public function regenerate(): void
    {
        if (!$this->started) {
            $this->start();
        }

        session_regenerate_id(true);
    }

    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function invalidate(): void
    {
        if (!$this->started) {
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        $this->started = false;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}


<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Exception\BusinessRuleException;
use AtelieDoGenio\Domain\Service\AuthenticationService;
use AtelieDoGenio\Http\Response\JsonResponse;
use AtelieDoGenio\Infrastructure\Security\CsrfTokenManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly CsrfTokenManager $csrfTokens,
    ) {
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->error('VALIDATION_ERROR', 'Campos email e password sao obrigatorios.', 422);
        }

        try {
            $result = $this->authService->login($email, $password);
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return $this->error('AUTH_ERROR', $exception->getMessage(), 500);
        }

        $claims = $this->normalizeData($result['claims'] ?? []);
        $userData = $this->normalizeData($result['user'] ?? []);

        if ($claims !== []) {
            $result['claims'] = $claims;
        }

        if ($userData !== []) {
            $result['user'] = $userData;
        }

        $role = $result['role']
            ?? ($claims['user_metadata']['role'] ?? null)
            ?? ($claims['raw_user_meta_data']['role'] ?? null)
            ?? ($userData['user_metadata']['role'] ?? null)
            ?? ($userData['raw_user_meta_data']['role'] ?? null)
            ?? 'vendedor';

        $result['role'] = $role;
        $result['csrf_token'] = $this->csrfTokens->generate('api');

        return $this->json($result);
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $token = (string) $request->getAttribute('supabase_token', '');

        if ($token === '') {
            return $this->error('UNAUTHORIZED', 'Sessao nao encontrada.', 401);
        }

        $this->authService->logout($token);

        return JsonResponse::success(['message' => 'Sessao encerrada.'], 200);
    }

    public function forgotPassword(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $email = trim((string) ($payload['email'] ?? ''));

        if ($email === '') {
            return $this->error('VALIDATION_ERROR', 'Email eh obrigatorio.', 422);
        }

        try {
            $this->authService->requestPasswordReset($email);
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        }

        return $this->json(['message' => 'Se o usuario existir, um email foi enviado.'], 202);
    }

    public function resetPassword(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $token = (string) ($payload['token'] ?? '');
        $newPassword = (string) ($payload['new_password'] ?? '');

        if ($token === '' || $newPassword === '') {
            return $this->error('VALIDATION_ERROR', 'Token e nova senha sao obrigatorios.', 422);
        }

        try {
            $this->authService->resetPassword($token, $newPassword);
        } catch (BusinessRuleException $exception) {
            return $this->error($exception->errorCode(), $exception->getMessage(), 422);
        }

        return $this->json(['message' => 'Senha redefinida com sucesso.']);
    }

    /**
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
            return $this->normalizeData(get_object_vars($data));
        }

        return [];
    }
}

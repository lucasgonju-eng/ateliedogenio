<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Infrastructure\Security\CsrfTokenManager;
use AtelieDoGenio\Infrastructure\View\ViewRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class WebController
{
    public function __construct(
        private readonly ViewRenderer $view,
        private readonly CsrfTokenManager $csrf
    ) {
    }

    public function home(ServerRequestInterface $request): ResponseInterface
    {
        $token = $this->csrf->generate('api');

        return $this->view->render('auth/login', [
            'csrfToken' => $token,
        ]);
    }

    public function vendorDashboard(ServerRequestInterface $request): ResponseInterface
    {
        return $this->view->render('dashboard/vendor', [
            'csrfToken' => $this->csrf->generate('api'),
        ]);
    }

    public function adminDashboard(ServerRequestInterface $request): ResponseInterface
    {
        return $this->view->render('dashboard/admin', [
            'csrfToken' => $this->csrf->generate('api'),
        ]);
    }
}

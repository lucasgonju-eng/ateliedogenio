<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\View;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

final class ViewRenderer
{
    private string $basePath;

    public function __construct(
        private readonly Psr17Factory $factory,
        ?string $basePath = null
    ) {
        $this->basePath = $basePath ?? dirname(__DIR__, 3) . '/resources/views';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        $path = $this->basePath . '/' . $view . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException('View not found: ' . $path);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $path;
        $content = ob_get_clean() ?: '';

        $response = $this->factory->createResponse($status);
        $response->getBody()->write($content);

        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}


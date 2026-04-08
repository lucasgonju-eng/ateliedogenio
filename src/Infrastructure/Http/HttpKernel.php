<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Http;

use AtelieDoGenio\Infrastructure\Container\Container;
use AtelieDoGenio\Infrastructure\Http\Routing\Router;
use AtelieDoGenio\Http\Middleware\MiddlewareInterface;
use AtelieDoGenio\Infrastructure\Http\RequestFactory;
use AtelieDoGenio\Infrastructure\Http\ResponseEmitter;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class HttpKernel
{
    /**
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    private RequestFactory $requestFactory;
    private Router $router;
    private ResponseEmitter $responseEmitter;

    public function __construct(
        private readonly Container $container,
        private readonly LoggerInterface $logger,
    ) {
        $this->requestFactory = $this->container->get(RequestFactory::class);
        $this->router = $this->container->get(Router::class);
        $this->responseEmitter = $this->container->get(ResponseEmitter::class);

        $config = require dirname(__DIR__, 3) . '/config/app.php';

        foreach ($config['middlewares'] ?? [] as $middleware) {
            $this->middlewares[] = $this->container->get($middleware);
        }
    }

    public function handleFromGlobals(): ResponseInterface
    {
        $request = $this->requestFactory->fromGlobals();

        return $this->handle($request);
    }

    public function emit(ResponseInterface $response): void
    {
        $this->responseEmitter->emit($response);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $pipeline = $this->createPipeline(0);
            $response = $pipeline($request);
        } catch (Throwable $exception) {
            $this->logger->error('Unhandled exception: {message}', ['message' => $exception->getMessage(), 'exception' => $exception]);

            $debugHeader = $request->getHeaderLine('X-Debug-Error');
            $debugEnv = (string) ($_ENV['APP_ENV'] ?? '');
            $debugFlag = (string) ($_ENV['APP_DEBUG'] ?? '');
            $debugMode = $debugHeader !== '' || $debugEnv === 'local' || $debugFlag === '1';

            $payload = [
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $debugMode ? $exception->getMessage() : 'Ocorreu um erro inesperado.',
                ],
            ];

            if ($debugMode) {
                $payload['error']['exception'] = $exception::class;
            }

            $response = new Response(500, ['Content-Type' => 'application/json'], (string) json_encode($payload, JSON_THROW_ON_ERROR));
        }

        return $response;
    }

    /**
     * @param int $index
     * @return callable(ServerRequestInterface): ResponseInterface
     */
    private function createPipeline(int $index): callable
    {
        if (!isset($this->middlewares[$index])) {
            return function (ServerRequestInterface $request): ResponseInterface {
                return $this->router->dispatch($request);
            };
        }

        $middleware = $this->middlewares[$index];

        return function (ServerRequestInterface $request) use ($middleware, $index): ResponseInterface {
            return $middleware->process($request, $this->createPipeline($index + 1));
        };
    }
}

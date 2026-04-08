<?php
require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
$container = require dirname(__DIR__) . '/bootstrap/container.php';
$controller = $container->get(AtelieDoGenio\Http\Controller\ProductController::class);
$request = (new Nyholm\Psr7\ServerRequest('GET', '/products/options'))
    ->withAttribute('user', ['id' => 'test', 'role' => 'admin']);
$response = $controller->options($request);
var_dump($response->getStatusCode());
var_dump((string) $response->getBody());

<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$routesFile = $baseDir . '/routes/api.php';

if (!file_exists($routesFile)) {
    fwrite(STDERR, "Arquivo de rotas não encontrado.\n");
    exit(1);
}

$contents = file_get_contents($routesFile) ?: '';

$pattern = '/\$group->(get|post|put|patch|delete)\s*\(\s*\'([^\']+)\'\s*,\s*\[\s*([A-Za-z0-9\\\\\\\\]+)::class\s*,\s*\'([^\']+)\'\s*]/';
preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);

if ($matches === []) {
    fwrite(STDOUT, "Nenhuma rota encontrada.\n");
    exit(0);
}

fwrite(STDOUT, "# Documentação de Endpoints\n\n");
fwrite(STDOUT, "| Método | Caminho | Controller | Ação |\n");
fwrite(STDOUT, "| --- | --- | --- | --- |\n");

foreach ($matches as $match) {
    [$full, $method, $path, $controller, $action] = $match;
    $controller = str_replace('AtelieDoGenio\\Http\\Controller\\', '', str_replace('AtelieDoGenio\\Http\\', '', $controller));
    fwrite(STDOUT, sprintf("| %s | %s | %s | %s |\n", strtoupper($method), $path, $controller, $action));
}


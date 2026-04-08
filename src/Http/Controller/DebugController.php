<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Http\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DebugController extends BaseController
{
    public function logs(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $limit = isset($query['limit']) ? max(50, min(5000, (int) $query['limit'])) : 800;

        $logsDir = dirname(__DIR__, 3) . '/storage/logs';

        if (!is_dir($logsDir)) {
            return JsonResponse::success([
                'message' => 'Logs directory not found',
                'dir' => $logsDir,
                'files' => [],
                'content' => '',
            ]);
        }

        // Encontra o arquivo de log mais recente (*.log)
        $files = array_values(array_filter(scandir($logsDir) ?: [], static function (string $f): bool {
            return $f !== '.' && $f !== '..' && str_ends_with($f, '.log');
        }));

        usort($files, static function (string $a, string $b) use ($logsDir): int {
            return (filemtime($logsDir . '/' . $b) ?: 0) <=> (filemtime($logsDir . '/' . $a) ?: 0);
        });

        $latest = $files[0] ?? null;

        if ($latest === null) {
            return JsonResponse::success([
                'message' => 'No log files found',
                'dir' => $logsDir,
                'files' => $files,
                'content' => '',
            ]);
        }

        $path = $logsDir . '/' . $latest;
        $content = @file_get_contents($path) ?: '';

        if ($content === '') {
            return JsonResponse::success([
                'message' => 'Log file empty',
                'file' => $latest,
                'content' => '',
            ]);
        }

        // Retorna apenas as últimas N linhas
        $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];
        $tail = array_slice($lines, -$limit);
        $joined = implode("\n", $tail);

        return JsonResponse::success([
            'file' => $latest,
            'limit' => $limit,
            'lines' => count($tail),
            'content' => $joined,
        ]);
    }
}

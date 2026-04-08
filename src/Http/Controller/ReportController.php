<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Service\ReportService;
use AtelieDoGenio\Infrastructure\Report\ReportExporter;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ReportController extends BaseController
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly ReportExporter $exporter,
        private readonly Psr17Factory $responseFactory
    ) {
    }

    /**
     * Gera relatório de vendas em JSON, CSV ou PDF.
     *
     * Exemplo (PowerShell) para baixar CSV do mês atual:
     *
     * $token = "<SEU_TOKEN_JWT>"
     * $from = (Get-Date -Day 1).ToString("yyyy-MM-01")
     * $to = (Get-Date).ToString("yyyy-MM-dd")
     * Invoke-WebRequest `
     *   -Uri "http://localhost/reports/sales?format=csv&from=$from&to=$to" `
     *   -Headers @{ Authorization="Bearer $token" } `
     *   -OutFile "relatorio_vendas.csv"
     *
     * Para inspecionar os logs da aplicação (PowerShell):
     * Get-Content -Path "storage/logs/application.log" -Tail 200
     */
    public function sales(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $format = strtolower((string) ($query['format'] ?? 'json'));

        if (!in_array($format, ['json', 'csv', 'pdf'], true)) {
            return $this->error('VALIDATION_ERROR', 'Formato nao suportado.', 422);
        }
        try {
            $from = $this->parseDate($query['from'] ?? null);
            $to = $this->parseDate($query['to'] ?? null);
        } catch (\InvalidArgumentException $exception) {
            return $this->error('VALIDATION_ERROR', $exception->getMessage(), 422);
        }

        // Ajusta limites de data: se usuário informá-la sem hora, usa o início/fim do dia
        if (isset($query['from']) && is_string($query['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $query['from']) && $from !== null) {
            $from = $from->setTime(0, 0, 0);
        }
        if (isset($query['to']) && is_string($query['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $query['to']) && $to !== null) {
            $to = $to->setTime(23, 59, 59);
        }

        $report = $this->reports->buildSalesReport($from, $to);

        return $this->renderReport($report, $format, sprintf('relatorio_vendas_%s', date('Ymd_His')));
    }

    public function inventory(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $format = strtolower((string) ($query['format'] ?? 'json'));

        if (!in_array($format, ['json', 'csv', 'pdf'], true)) {
            return $this->error('VALIDATION_ERROR', 'Formato nao suportado.', 422);
        }

        $query = $this->query($request);
        $sku = isset($query['sku']) ? (string) $query['sku'] : null;
        $onlyActive = isset($query['active']) ? filter_var($query['active'], FILTER_VALIDATE_BOOLEAN) : null;
        $report = $this->reports->buildInventoryReport($sku, $onlyActive);

        return $this->renderReport($report, $format, sprintf('relatorio_estoque_%s', date('Ymd_His')));
    }

    /**
     * @param array{title:string, headers:list<string>, rows:list<array<int, string>>} $report
     */
    private function renderReport(array $report, string $format, string $filename): ResponseInterface
    {
        return match ($format) {
            'csv' => $this->streamResponse(
                $this->exporter->toCsv($report['headers'], $report['rows']),
                'text/csv; charset=utf-8',
                $filename . '.csv'
            ),
            'pdf' => $this->streamResponse(
                $this->exporter->toPdf($report['title'], $report['headers'], $report['rows']),
                'application/pdf',
                $filename . '.pdf'
            ),
            default => $this->json($report),
        };
    }

    private function streamResponse(string $contents, string $mime, string $filename): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', $mime)
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response->withBody(Stream::create($contents));
    }

    private function parseDate(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Data invalida: ' . $value);
        }
    }
}

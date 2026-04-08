<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Report;

final class ReportExporter
{
    public function __construct(private readonly SimplePdfBuilder $pdfBuilder)
    {
    }

    /**
     * @param list<string> $headers
     * @param list<array<int, string>> $rows
     */
    public function toCsv(array $headers, array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new \RuntimeException('Nao foi possivel criar o exportador CSV.');
        }

        // Escreve BOM UTF-8 para melhor compatibilidade com Excel
        fwrite($stream, "\xEF\xBB\xBF");

        // Usa separador ';' (pt-BR), delimitador '"' e escape '\\'
        fputcsv($stream, $headers, ';', '"', '\\');

        foreach ($rows as $row) {
            fputcsv($stream, $row, ';', '"', '\\');
        }

        rewind($stream);

        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents === false ? '' : $contents;
    }

    /**
     * @param string $title
     * @param list<string> $headers
     * @param list<array<int, string>> $rows
     */
    public function toPdf(string $title, array $headers, array $rows): string
    {
        $lines = [$title, 'Gerado em: ' . date('d/m/Y H:i'), ''];
        $lines[] = implode(' | ', $headers);

        foreach ($rows as $row) {
            $lines[] = implode(' | ', $row);
        }

        return $this->pdfBuilder->build($lines);
    }
}


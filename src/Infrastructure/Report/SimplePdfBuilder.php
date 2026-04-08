<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Report;

final class SimplePdfBuilder
{
    /**
     * @param list<string> $lines
     */
    public function build(array $lines): string
    {
        $textContent = $this->buildTextBlock($lines);

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>\n";
        $objects[] = "<< /Type /Pages /Count 1 /Kids [3 0 R] >>\n";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>\n";
        $objects[] = sprintf("<< /Length %d >>\nstream\n%s\nendstream\n", strlen($textContent), $textContent);
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>\n";

        $pdf = '%PDF-1.4' . "\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%sendobj\n", $index + 1, $object);
        }

        $xrefStart = strlen($pdf);
        $xref = "xref\n0 " . count($offsets) . "\n";
        $xref .= "0000000000 65535 f \n";

        for ($i = 1; $i < count($offsets); $i++) {
            $xref .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= $xref;
        $pdf .= "trailer\n<< /Size " . count($offsets) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefStart . "\n%%EOF";

        return $pdf;
    }

    /**
     * @param list<string> $lines
     */
    private function buildTextBlock(array $lines): string
    {
        $buffer = [];
        $buffer[] = 'BT';
        $buffer[] = '/F1 10 Tf';
        $buffer[] = '14 TL';
        $buffer[] = '72 550 Td';

        foreach ($lines as $line) {
            $buffer[] = sprintf('(%s) Tj', $this->escapeText($line));
            $buffer[] = 'T*';
        }

        $buffer[] = 'ET';

        return implode("\n", $buffer);
    }

    private function escapeText(string $value): string
    {
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);

        return $escaped;
    }
}

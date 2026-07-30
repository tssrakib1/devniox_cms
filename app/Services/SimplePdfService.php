<?php

namespace App\Services;

class SimplePdfService
{
    public function make(array $lines): string
    {
        $pages = array_chunk($lines, 48);
        $objects = [];
        $pageIds = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        foreach ($pages as $index => $pageLines) {
            $pageId = 4 + ($index * 2);
            $contentId = $pageId + 1;
            $pageIds[] = $pageId.' 0 R';
            $stream = "BT\n/F1 9 Tf\n40 760 Td\n14 TL\n";
            foreach ($pageLines as $line) {
                $stream .= '('.$this->escape((string) $line).") Tj\nT*\n";
            }
            $stream .= 'ET';
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = '<< /Length '.strlen($stream).">>\nstream\n{$stream}\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageIds).'] /Count '.count($pages).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }
        $xref = strlen($pdf);
        $count = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
        for ($id = 1; $id < $count; $id++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$id])."\n";
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function escape(string $value): string
    {
        $value = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) ?: $value;

        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $value);
    }
}

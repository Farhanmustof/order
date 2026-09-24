<?php

namespace App\Infrastructure\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ekspor CSV yang langsung terbuka rapi di Excel berbahasa Indonesia
 * (pemisah titik koma, desimal koma, BOM UTF-8).
 */
final class CsvExporter
{
    /** @param iterable<array> $rows */
    public function download(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headings, ';', '"', '');
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => is_float($v) ? str_replace('.', ',', (string) round($v, 3)) : $v, $row), ';', '"', '');
            }
            fclose($out);
        }, $filename.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

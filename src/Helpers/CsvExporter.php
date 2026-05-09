<?php

namespace App\Helpers;

class CsvExporter
{
    public static function download(string $filename, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers, ';');

        foreach ($rows as $row) {
            fputcsv($out, $row, ';');
        }

        fclose($out);
        exit;
    }

    public static function formatDate(?string $iso): string
    {
        if (!$iso) return '';
        try {
            return date('d.m.Y H:i', strtotime($iso));
        } catch (\Throwable) {
            return $iso;
        }
    }
}

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
        fputcsv($out, array_map([self::class, 'sanitize'], $headers), ';');

        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'sanitize'], $row), ';');
        }

        fclose($out);
        exit;
    }

    /**
     * Neutralise CSV/formula injection: a leading =, +, -, @ (or tab/CR) makes
     * Excel/Sheets evaluate the cell as a formula. Prefix such values with a
     * single quote so they are treated as literal text.
     */
    private static function sanitize(mixed $value): string
    {
        $s = (string)$value;
        if ($s !== '' && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $s;
        }
        return $s;
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

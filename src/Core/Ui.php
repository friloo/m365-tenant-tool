<?php

namespace App\Core;

/**
 * Small HTML snippet helpers for views — deep-link buttons and copyable
 * PowerShell/code blocks. Used on modules whose settings have no Microsoft
 * Graph write API (DLP, Retention, Defender-Office/EOP, DKIM, Purview labels),
 * so admins can jump to the portal or copy the exact PowerShell command.
 */
class Ui
{
    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    /** A "open in portal" link button (new tab, noopener). */
    public static function linkBtn(string $url, string $label, string $style = 'outline-primary'): string
    {
        return '<a href="' . self::esc($url) . '" target="_blank" rel="noopener" '
             . 'class="btn btn-sm btn-' . self::esc($style) . '">'
             . '<i class="bi bi-box-arrow-up-right me-1"></i>' . self::esc($label) . '</a>';
    }

    /**
     * A copyable code block (rendered dark) with a "Kopieren" button.
     * The button copies the block's text via the JS handler in app.js
     * (delegated on .js-copy within a .ps-snippet wrapper).
     */
    public static function psBlock(string $code, string $title = ''): string
    {
        $head = $title !== ''
            ? '<div class="small fw-semibold mb-1">' . self::esc($title) . '</div>'
            : '';
        return '<div class="ps-snippet mb-3">'
             . $head
             . '<div class="position-relative">'
             . '<button type="button" class="btn btn-sm btn-outline-light js-copy" '
             . 'style="position:absolute;top:.4rem;right:.4rem;z-index:2;">'
             . '<i class="bi bi-clipboard me-1"></i>Kopieren</button>'
             . '<pre class="mb-0 p-3 rounded" style="background:#1e1e2e;color:#e4e4e7;overflow:auto;">'
             . '<code>' . self::esc($code) . '</code></pre>'
             . '</div></div>';
    }

    /**
     * Convenience: a full "configure elsewhere" card — intro text, optional
     * deep-link buttons and one or more labeled PowerShell blocks.
     *
     * @param array<int,array{0:string,1:string}> $links  [url, label] pairs
     * @param array<int,array{0:string,1:string}> $blocks [code, title] pairs
     */
    public static function externalCard(string $title, string $intro, array $links = [], array $blocks = [], string $icon = 'box-arrow-up-right'): string
    {
        $html  = '<div class="content-card mb-4"><div class="card-body p-4">';
        $html .= '<div class="d-flex align-items-start gap-3">';
        $html .= '<i class="bi bi-' . self::esc($icon) . ' fs-3 text-primary"></i>';
        $html .= '<div class="flex-grow-1">';
        $html .= '<h5 class="mb-2">' . self::esc($title) . '</h5>';
        if ($intro !== '') {
            $html .= '<p class="text-muted mb-3">' . $intro . '</p>'; // intro is trusted caller markup
        }
        if ($links) {
            $html .= '<div class="d-flex flex-wrap gap-2 mb-3">';
            foreach ($links as [$url, $label]) {
                $html .= self::linkBtn($url, $label);
            }
            $html .= '</div>';
        }
        foreach ($blocks as [$code, $blockTitle]) {
            $html .= self::psBlock($code, $blockTitle);
        }
        $html .= '</div></div></div></div>';
        return $html;
    }
}

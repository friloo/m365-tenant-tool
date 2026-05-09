<?php

namespace App\Core;

class View
{
    public static function render(string $template, array $data = [], bool $withLayout = true): void
    {
        extract($data, EXTR_SKIP);
        $templatePath = BASE_PATH . '/views/' . ltrim($template, '/') . '.php';

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        if ($withLayout) {
            ob_start();
            require $templatePath;
            $content = ob_get_clean();
            require BASE_PATH . '/views/layout/base.php';
        } else {
            require $templatePath;
        }
    }

    public static function partial(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require BASE_PATH . '/views/' . ltrim($template, '/') . '.php';
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

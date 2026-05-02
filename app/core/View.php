<?php

namespace App\Core;

/**
 * Renderer de views dentro de layouts.
 */
final class View
{
    public static function render(string $relativeView, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $relativeView) . '.php';
        if (! is_readable($viewPath)) {
            http_response_code(500);
            echo 'View não encontrada: ' . Helpers::e($relativeView);
            return;
        }
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $layout) . '.php';
        if (! is_readable($layoutPath)) {
            echo $content;
            return;
        }
        require $layoutPath;
    }

    /** Renderiza apenas o fragmento, sem layout. */
    public static function partial(string $relativeView, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $relativeView) . '.php';
        if (is_readable($viewPath)) {
            require $viewPath;
        }
    }
}

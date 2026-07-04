<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public function __construct(private readonly array $config)
    {
    }

    public function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewFile = base_path('app/views/' . $template . '.php');
        if (!is_file($viewFile)) {
            throw new RuntimeException("View not found: {$template}");
        }

        $config = $this->config;
        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = base_path('app/views/' . $layout . '.php');
        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout not found: {$layout}");
        }

        require $layoutFile;
    }
}


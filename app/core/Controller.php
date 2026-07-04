<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected View $view;

    public function __construct(protected readonly array $config)
    {
        $this->view = new View($config);
    }

    protected function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $this->view->render($template, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path, true, 302);
        exit;
    }
}


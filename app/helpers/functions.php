<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
}

function storage_path(string $path = ''): string
{
    return base_path('storage' . ($path ? '/' . ltrim($path, '/') : ''));
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $url = '/assets/' . $path;
    $file = base_path('public/assets/' . $path);

    if (is_file($file) && preg_match('/\.(css|js|woff2?|webp|png|jpe?g|svg)$/i', $path)) {
        $url .= '?v=' . (string) filemtime($file);
    }

    return $url;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function route(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function excerpt(string $text, int $length = 160): string
{
    $text = trim(strip_tags($text));
    if (strlen($text) <= $length) {
        return $text;
    }

    return rtrim(substr($text, 0, $length - 1)) . '...';
}

function active_path(string $path): string
{
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $current === $path ? 'is-active' : '';
}

function service_visuals(array $visuals, string $class = 'service-logo-strip', int $limit = 4): string
{
    if ($visuals === []) {
        return '';
    }

    $html = '<span class="' . e($class) . '" aria-label="Service technology logos">';
    foreach (array_slice($visuals, 0, $limit) as $visual) {
        $name = (string) ($visual['name'] ?? 'Technology');
        $html .= '<span class="service-logo-chip">';
        if (!empty($visual['image'])) {
            $html .= '<img src="' . e(asset('images/' . ltrim((string) $visual['image'], '/'))) . '" alt="' . e($name) . ' logo" loading="lazy">';
        } elseif (!empty($visual['icon'])) {
            $html .= '<i class="' . e((string) $visual['icon']) . '"></i>';
        }
        $html .= '<b>' . e($name) . '</b></span>';
    }
    $html .= '</span>';

    return $html;
}

<?php

declare(strict_types=1);

$installed = is_file(base_path('config/installed.php'))
    ? require base_path('config/installed.php')
    : [];

return [
    'name' => $installed['site']['name'] ?? 'Crest Web Media',
    'tagline' => 'Enterprise-style websites, apps, SEO systems and AI workflows for growth-focused teams.',
    'url' => $installed['site']['url'] ?? 'http://localhost:8080',
    'timezone' => 'Asia/Calcutta',
    'admin_email' => $installed['site']['admin_email'] ?? 'ank.kalia@gmail.com',
    'monitor_cron_key' => getenv('MONITOR_CRON_KEY') ?: ($installed['site']['monitor_cron_key'] ?? ''),
    'security' => [
        'csp' => "default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com data:; script-src 'self' 'unsafe-inline'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'; upgrade-insecure-requests",
        'rate_limit_attempts' => 5,
        'rate_limit_window_minutes' => 15,
    ],
];

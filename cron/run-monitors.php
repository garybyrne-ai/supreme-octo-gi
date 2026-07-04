<?php

declare(strict_types=1);

/**
 * CLI cron entry point for scheduled website monitors.
 *
 * Crontab (weekly, Mondays 07:00):
 *   0 7 * * 1 /usr/bin/php /path/to/public_html/cron/run-monitors.php >> /path/to/logs/monitors.log 2>&1
 *
 * Or hit the web endpoint from a scheduler:
 *   /cron/run-monitors?key=MONITOR_CRON_KEY
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is for command-line use. Use /cron/run-monitors?key=... over HTTP.\n");
}

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/helpers/functions.php';

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, 4)) . '.php';
    $parts = explode('/', $relative);
    $parts[0] = strtolower($parts[0]);
    foreach ([BASE_PATH . '/app/' . $relative, BASE_PATH . '/app/' . implode('/', $parts)] as $file) {
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$summary = (new App\Services\MonitorService())->runDue();

fwrite(STDOUT, sprintf(
    "[%s] monitors: checked=%d alerts=%d skipped=%d\n",
    gmdate('c'),
    $summary['checked'],
    $summary['alerts'],
    $summary['skipped']
));

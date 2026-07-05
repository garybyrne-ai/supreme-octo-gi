<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Never leak stack traces, file paths or SQL to visitors — errors go to the log.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require BASE_PATH . '/app/helpers/functions.php';

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\' => BASE_PATH . '/app/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $relativePath = str_replace('\\', '/', $relative) . '.php';
            $candidates = [$baseDir . $relativePath];

            $parts = explode('/', $relativePath);
            if (count($parts) > 1) {
                $parts[0] = strtolower($parts[0]);
                $candidates[] = $baseDir . implode('/', $parts);
            }

            foreach ($candidates as $file) {
                if (is_file($file)) {
                    require $file;
                    return;
                }
            }
        }
    }
});

use App\Core\Router;

$config = require BASE_PATH . '/config/app.php';
$requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isInstalled = is_file(BASE_PATH . '/config/installed.php');
$isGet = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
$statefulPrefixes = [
    '/admin',
    '/account',
    '/install',
    '/contact',
    '/support',
    '/code-shop',
    '/membership',
    '/backlinks',
    '/download.php',
    '/webhooks',
    '/tools',
    '/seo-tools',
    '/serp-checker',
    '/free-penetration-testing-tools',
    '/forum',
    '/ai-website-growth-consultant',
    '/instant-website-quote-calculator',
    '/security-score-badge-generator',
    '/client-portal-preview',
    '/ppc-roi-calculator',
    '/before-after-speed-simulator',
    '/ai-automation-finder',
];

if ($isGet) {
    $isStatefulPage = false;
    foreach ($statefulPrefixes as $prefix) {
        if (str_starts_with($requestPath, $prefix)) {
            $isStatefulPage = true;
            break;
        }
    }

    header('Vary: Accept-Encoding, User-Agent');
    header($isStatefulPage
        ? 'Cache-Control: private, no-store, no-cache, must-revalidate'
        : 'Cache-Control: public, max-age=60, s-maxage=300, stale-while-revalidate=60'
    );

    if ($isStatefulPage) {
        header('Surrogate-Control: no-store');
    }
}

// Full security response headers (CSP, HSTS, COOP, anti-clickjacking, etc.) are
// centralised in Router::sendSecurityHeaders() so every routed response — the
// only responses PHP controls — is hardened from one place.

if (!$isInstalled && !str_starts_with($requestPath, '/install') && !str_starts_with($requestPath, '/assets')) {
    header('Location: /install', true, 302);
    exit;
}

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

$router = new Router($config);
require BASE_PATH . '/routes/web.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $exception) {
    $logDir = BASE_PATH . '/storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    error_log(
        '[' . date('c') . '] ' . get_class($exception) . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL . $exception->getTraceAsString() . PHP_EOL,
        3,
        $logDir . '/php-error-' . date('Y-m-d') . '.log'
    );

    http_response_code(500);
    echo 'Application error. Please check storage/logs for details.';
}

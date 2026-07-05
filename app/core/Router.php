<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable|array{0: class-string, 1: string}>> */
    private array $routes = [];

    public function __construct(private readonly array $config)
    {
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[strtoupper($method)][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $this->sendSecurityHeaders();

        $path = $this->normalize((string) parse_url($uri, PHP_URL_PATH));
        $method = strtoupper($method);

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $params = $this->match($route, $path);
            if ($params !== null) {
                $this->invoke($handler, $params);
                return;
            }
        }

        http_response_code(404);
        (new View($this->config))->render('errors/404', [
            'title' => 'Page Not Found',
            'metaDescription' => 'The page you requested could not be found.',
        ]);
    }

    private function invoke(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class($this->config);
            $controller->{$method}(...array_values($params));
            return;
        }

        $handler(...array_values($params));
    }

    private function match(string $route, string $path): ?array
    {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if ($route === '/') {
            return $path === '/' ? [] : null;
        }

        if (count($routeParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $index => $part) {
            $pathPart = $pathParts[$index] ?? '';
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)}$/', $part, $matches)) {
                $params[$matches[1]] = urldecode($pathPart);
                continue;
            }

            if ($part !== $pathPart) {
                return null;
            }
        }

        return $params;
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header_remove('X-Powered-By');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(self), usb=(), interest-cohort=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('X-Permitted-Cross-Domain-Policies: none');
        header('Content-Security-Policy: ' . $this->contentSecurityPolicy());

        $isHttps = ($_SERVER['HTTPS'] ?? '') === 'on'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Base CSP from config, widened for Google AdSense / Analytics hosts only
     * when advertising or analytics is actually switched on — so the policy
     * stays tight for every site that hasn't enabled ads.
     */
    private function contentSecurityPolicy(): string
    {
        $csp = (string) ($this->config['security']['csp'] ?? "default-src 'self'");

        try {
            $ads = new \App\Models\AdsSettingsRepository();
            if (!$ads->adsActive() && $ads->analyticsId() === '') {
                return $csp;
            }
        } catch (\Throwable) {
            return $csp;
        }

        $script = 'https://pagead2.googlesyndication.com https://www.googletagmanager.com https://adservice.google.com https://*.googlesyndication.com https://tpc.googlesyndication.com';
        $frame = 'https://googleads.g.doubleclick.net https://tpc.googlesyndication.com https://*.googlesyndication.com https://www.google.com';
        $connect = 'https://pagead2.googlesyndication.com https://*.googlesyndication.com https://*.google-analytics.com https://region1.google-analytics.com https://*.doubleclick.net https://www.googletagmanager.com';

        $csp = preg_replace('/script-src ([^;]+)/', 'script-src $1 ' . $script, $csp);
        $csp = preg_replace('/connect-src ([^;]+)/', 'connect-src $1 ' . $connect, $csp);
        // No frame-src in the base policy — add one (frame-ancestors is separate).
        $csp .= "; frame-src 'self' " . $frame;

        return $csp;
    }
}


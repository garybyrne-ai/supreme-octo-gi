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
        header('Content-Security-Policy: ' . ($this->config['security']['csp'] ?? "default-src 'self'"));

        $isHttps = ($_SERVER['HTTPS'] ?? '') === 'on'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}


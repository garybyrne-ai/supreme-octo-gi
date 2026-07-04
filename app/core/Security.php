<?php

declare(strict_types=1);

namespace App\Core;

final class Security
{
    public static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);
    }

    public static function csrfToken(): string
    {
        self::ensureSession();

        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::ensureSession();

        return is_string($token)
            && isset($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    public static function captchaChallenge(string $context): array
    {
        self::ensureSession();

        if (empty($_SESSION['_captcha'][$context])) {
            $left = random_int(3, 12);
            $right = random_int(2, 9);
            $_SESSION['_captcha'][$context] = [
                'question' => $left . ' + ' . $right,
                'answer' => (string) ($left + $right),
                'created_at' => time(),
            ];
        }

        return $_SESSION['_captcha'][$context];
    }

    public static function refreshCaptcha(string $context): array
    {
        unset($_SESSION['_captcha'][$context]);
        return self::captchaChallenge($context);
    }

    public static function verifyCaptcha(string $context, ?string $answer): bool
    {
        self::ensureSession();

        $challenge = $_SESSION['_captcha'][$context] ?? null;
        unset($_SESSION['_captcha'][$context]);

        return is_array($challenge)
            && isset($challenge['answer'], $challenge['created_at'])
            && time() - (int) $challenge['created_at'] <= 900
            && hash_equals((string) $challenge['answer'], trim((string) $answer));
    }

    public static function hitRateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 900): bool
    {
        self::ensureSession();

        $now = time();
        $bucketKey = 'rate_' . hash('sha256', $key . '|' . self::clientIp());
        $bucket = $_SESSION[$bucketKey] ?? ['started_at' => $now, 'attempts' => 0];

        if (!is_array($bucket) || $now - (int) ($bucket['started_at'] ?? 0) > $windowSeconds) {
            $bucket = ['started_at' => $now, 'attempts' => 0];
        }

        $bucket['attempts'] = (int) ($bucket['attempts'] ?? 0) + 1;
        $_SESSION[$bucketKey] = $bucket;

        return $bucket['attempts'] > $maxAttempts;
    }

    public static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', (string) $_SERVER[$key])[0]);
            }
        }

        return '0.0.0.0';
    }

    public static function deviceFingerprint(): array
    {
        $agent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

        return [
            'ip' => self::clientIp(),
            'user_agent' => $agent,
            'accept_language' => (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
            'location' => [
                'country' => (string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['HTTP_X_APPENGINE_COUNTRY'] ?? ''),
                'region' => (string) ($_SERVER['HTTP_X_APPENGINE_REGION'] ?? ''),
                'city' => (string) ($_SERVER['HTTP_X_APPENGINE_CITY'] ?? ''),
                'timezone' => (string) ($_SERVER['HTTP_X_TIMEZONE'] ?? ''),
            ],
            'device_hash' => hash('sha256', self::clientIp() . '|' . $agent),
        ];
    }
}

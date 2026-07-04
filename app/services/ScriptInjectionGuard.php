<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CmsRepository;

final class ScriptInjectionGuard
{
    private const ALLOWED_LOCATIONS = ['head', 'body_open', 'footer_close'];
    private const ALLOWED_SCRIPT_HOSTS = [
        'www.googletagmanager.com',
        'www.google-analytics.com',
        'connect.facebook.net',
        'js.stripe.com',
        'www.paypal.com',
        'www.paypalobjects.com',
        'static.cloudflareinsights.com',
    ];

    public function __construct(private readonly CmsRepository $repository = new CmsRepository())
    {
    }

    public function validateAndSave(array $payload, ?int $approvedBy = null): int
    {
        $location = (string) ($payload['location'] ?? 'head');
        if (!in_array($location, self::ALLOWED_LOCATIONS, true)) {
            throw new \RuntimeException('Invalid injection location.');
        }

        $code = $this->clean((string) ($payload['code'] ?? ''));
        if ($code === '') {
            throw new \RuntimeException('Injection code cannot be empty.');
        }

        return $this->repository->saveScriptInjection([
            'name' => trim((string) ($payload['name'] ?? 'Custom Script')),
            'location' => $location,
            'code' => $code,
            'load_strategy' => $payload['load_strategy'] ?? 'inline',
            'is_enabled' => !empty($payload['is_enabled']),
            'approved_by' => $approvedBy,
        ]);
    }

    public function render(string $location): string
    {
        if (!in_array($location, self::ALLOWED_LOCATIONS, true)) {
            return '';
        }

        $html = '';
        foreach ($this->repository->scriptInjections($location) as $row) {
            $html .= "\n" . '<!-- cms-injection:' . e($row['name'] ?? 'script') . ' -->' . "\n";
            $html .= (string) $row['code'] . "\n";
        }

        return $html;
    }

    private function clean(string $code): string
    {
        $code = trim(str_replace("\0", '', $code));
        $lower = strtolower($code);

        foreach (['<?php', '<?= ', '<%', 'base64_decode', 'shell_exec', 'passthru', 'proc_open', 'system('] as $blocked) {
            if (str_contains($lower, $blocked)) {
                throw new \RuntimeException('Unsafe server-side or shell execution code was blocked.');
            }
        }

        foreach (['javascript:', 'vbscript:', 'data:text/html', 'onerror=', 'onload=', 'onclick='] as $blocked) {
            if (str_contains($lower, $blocked)) {
                throw new \RuntimeException('Unsafe browser execution vector was blocked.');
            }
        }

        if (preg_match_all('/<script\b[^>]*\bsrc=["\']([^"\']+)["\']/i', $code, $matches)) {
            foreach ($matches[1] as $src) {
                $host = strtolower((string) parse_url((string) $src, PHP_URL_HOST));
                if ($host === '' || !in_array($host, self::ALLOWED_SCRIPT_HOSTS, true)) {
                    throw new \RuntimeException('Remote script host is not approved: ' . $host);
                }
            }
        }

        if (preg_match('/\b(eval|Function)\s*\(/i', $code)) {
            throw new \RuntimeException('Dynamic JavaScript execution is not allowed in CMS injections.');
        }

        return $code;
    }
}

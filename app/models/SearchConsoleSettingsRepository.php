<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Google OAuth client credentials for the Search Console integration. Set once
 * by the admin (Client ID + Client Secret from a Google Cloud project). Secrets
 * live in a JSON settings file, never in code. The redirect URI the admin must
 * register in Google Cloud is derived from the live host at request time.
 */
final class SearchConsoleSettingsRepository
{
    public function current(): array
    {
        $default = ['client_id' => '', 'client_secret' => '', 'enabled' => false];
        $path = $this->path();
        if (!is_file($path)) {
            return $default;
        }
        $saved = json_decode((string) file_get_contents($path), true);
        return is_array($saved) ? array_replace($default, $saved) : $default;
    }

    public function configured(): bool
    {
        $s = $this->current();
        return !empty($s['enabled']) && trim((string) $s['client_id']) !== '' && trim((string) $s['client_secret']) !== '';
    }

    public function clientId(): string
    {
        return trim((string) ($this->current()['client_id'] ?? ''));
    }

    public function clientSecret(): string
    {
        return trim((string) ($this->current()['client_secret'] ?? ''));
    }

    public function save(array $input): array
    {
        $clientId = trim((string) ($input['client_id'] ?? ''));
        if ($clientId !== '' && !str_ends_with($clientId, '.apps.googleusercontent.com')) {
            throw new \RuntimeException('Google Client ID should end in .apps.googleusercontent.com.');
        }

        // Keep the existing secret if the field was left blank (so it is never
        // wiped by re-saving the form without re-entering it).
        $current = $this->current();
        $secretIn = trim((string) ($input['client_secret'] ?? ''));
        $secret = $secretIn !== '' ? $secretIn : (string) $current['client_secret'];

        $settings = [
            'client_id' => $clientId,
            'client_secret' => $secret,
            'enabled' => !empty($input['enabled']),
        ];

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->path(), json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $settings;
    }

    private function path(): string
    {
        return base_path('storage/data/search-console-settings.json');
    }
}

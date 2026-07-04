<?php

declare(strict_types=1);

namespace App\Models;

final class MailSettingsRepository
{
    public function current(): array
    {
        $default = [
            'driver' => 'php_mail',
            'from_name' => 'Crest Web Media',
            'from_email' => 'no-reply@crestwebmedia.com',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_username' => '',
            'smtp_password' => '',
        ];

        $path = $this->path();
        if (!is_file($path)) {
            return $default;
        }

        $saved = json_decode((string) file_get_contents($path), true);
        return is_array($saved) ? array_replace($default, $saved) : $default;
    }

    public function save(array $input): array
    {
        $current = $this->current();
        $settings = [
            'driver' => trim((string) ($input['driver'] ?? 'php_mail')),
            'from_name' => trim((string) ($input['from_name'] ?? '')),
            'from_email' => strtolower(trim((string) ($input['from_email'] ?? ''))),
            'smtp_host' => trim((string) ($input['smtp_host'] ?? 'smtp.gmail.com')),
            'smtp_port' => (string) (int) ($input['smtp_port'] ?? 587),
            'smtp_encryption' => trim((string) ($input['smtp_encryption'] ?? 'tls')),
            'smtp_username' => trim((string) ($input['smtp_username'] ?? '')),
            'smtp_password' => (string) ($input['smtp_password'] ?? ''),
        ];

        if (!in_array($settings['driver'], ['php_mail', 'gmail_smtp'], true)) {
            throw new \RuntimeException('Choose PHP Mail or Gmail SMTP.');
        }

        if ($settings['from_name'] === '' || !filter_var($settings['from_email'], FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('From name and valid from email are required.');
        }

        if ($settings['driver'] === 'gmail_smtp') {
            if ($settings['smtp_password'] === '') {
                $settings['smtp_password'] = (string) ($current['smtp_password'] ?? '');
            }

            if ($settings['smtp_host'] === '' || (int) $settings['smtp_port'] <= 0 || !filter_var($settings['smtp_username'], FILTER_VALIDATE_EMAIL) || $settings['smtp_password'] === '') {
                throw new \RuntimeException('Gmail SMTP requires host, port, Gmail username and app password.');
            }
        } elseif ($settings['smtp_password'] === '') {
            $settings['smtp_password'] = (string) ($current['smtp_password'] ?? '');
        }

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $settings;
    }

    private function path(): string
    {
        return base_path('storage/data/mail-settings.json');
    }
}

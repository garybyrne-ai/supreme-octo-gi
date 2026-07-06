<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Advertising, analytics and cookie-consent settings. JSON-backed (no database
 * required). Drives GDPR-compliant consent gating: AdSense and analytics only
 * load after the visitor accepts non-essential cookies. Every value is editable
 * from the admin "Ads & Consent" module, and ads.txt is served from here so it
 * can be updated without file access.
 */
final class AdsSettingsRepository
{
    public function current(): array
    {
        $default = [
            'ads_enabled' => false,
            'adsense_client' => '',        // e.g. ca-pub-1234567890123456
            'analytics_id' => '',          // e.g. G-XXXXXXXXXX (GA4) — optional
            'consent_message' => 'We use cookies to keep the site secure and, with your consent, to show relevant ads and measure traffic. You can accept, reject non-essential cookies, or manage your choice any time.',
            'ads_txt' => "# Add your ad-network lines below, one per line.\n# Example (replace with your real AdSense publisher ID):\n# google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0",
        ];

        $path = $this->path();
        if (!is_file($path)) {
            return $default;
        }

        $saved = json_decode((string) file_get_contents($path), true);
        return is_array($saved) ? array_replace($default, $saved) : $default;
    }

    /**
     * Whether AdSense should actually render: enabled + a valid publisher id.
     */
    public function adsActive(): bool
    {
        $s = $this->current();
        return !empty($s['ads_enabled']) && $this->validClient((string) $s['adsense_client']);
    }

    public function adsenseClient(): string
    {
        $client = (string) ($this->current()['adsense_client'] ?? '');
        return $this->validClient($client) ? $client : '';
    }

    public function analyticsId(): string
    {
        $id = trim((string) ($this->current()['analytics_id'] ?? ''));
        return preg_match('/^G-[A-Z0-9]{4,}$/i', $id) ? $id : '';
    }

    public function adsTxt(): string
    {
        return (string) ($this->current()['ads_txt'] ?? '');
    }

    public function save(array $input): array
    {
        // Merge over the current settings so a form that omits a field (e.g. the
        // AdSense form has no ads.txt box) never wipes the stored value.
        $current = $this->current();

        $client = trim((string) ($input['adsense_client'] ?? $current['adsense_client']));
        if ($client !== '' && !$this->validClient($client)) {
            throw new \RuntimeException('AdSense publisher ID should look like ca-pub-1234567890123456.');
        }

        $analytics = trim((string) ($input['analytics_id'] ?? $current['analytics_id']));
        if ($analytics !== '' && !preg_match('/^G-[A-Z0-9]{4,}$/i', $analytics)) {
            throw new \RuntimeException('Analytics ID should look like G-XXXXXXXXXX.');
        }

        $consent = array_key_exists('consent_message', $input)
            ? mb_substr(trim(strip_tags((string) $input['consent_message'])), 0, 500)
            : (string) $current['consent_message'];

        $settings = [
            'ads_enabled' => !empty($input['ads_enabled']),
            'adsense_client' => $client,
            'analytics_id' => strtoupper($analytics),
            'consent_message' => $consent !== '' ? $consent : $current['consent_message'],
            'ads_txt' => array_key_exists('ads_txt', $input)
                ? mb_substr((string) $input['ads_txt'], 0, 4000)
                : (string) $current['ads_txt'],
        ];

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $settings;
    }

    private function validClient(string $client): bool
    {
        return (bool) preg_match('/^ca-pub-[0-9]{10,20}$/', $client);
    }

    private function path(): string
    {
        return base_path('storage/data/ads-settings.json');
    }
}

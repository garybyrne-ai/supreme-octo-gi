<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Global SEO settings edited from the admin SEO Center: default social-share
 * image, Twitter/X card handle, search-engine verification tokens, organization
 * schema fields, social profiles, and a site-wide indexing switch (handy for
 * staging). JSON-backed with sensible defaults.
 */
final class SeoSettingsRepository
{
    public function current(): array
    {
        $default = [
            'default_meta_description' => '',
            'og_image' => '',
            'twitter_site' => '',
            'google_verification' => '',
            'bing_verification' => '',
            'organization_name' => '',
            'organization_logo' => '',
            'social_linkedin' => '',
            'social_github' => '',
            'social_facebook' => '',
            'social_x' => '',
            'allow_indexing' => true,
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
        $url = static function (mixed $v): string {
            $v = trim((string) $v);
            return $v !== '' && filter_var($v, FILTER_VALIDATE_URL) ? $v : '';
        };
        $text = static fn (mixed $v, int $max): string => mb_substr(trim(strip_tags((string) $v)), 0, $max);

        $twitter = $text($input['twitter_site'] ?? '', 40);
        if ($twitter !== '' && $twitter[0] !== '@') {
            $twitter = '@' . ltrim($twitter, '@');
        }

        $settings = [
            'default_meta_description' => $text($input['default_meta_description'] ?? '', 320),
            'og_image' => $url($input['og_image'] ?? ''),
            'twitter_site' => $twitter,
            'google_verification' => $text($input['google_verification'] ?? '', 120),
            'bing_verification' => $text($input['bing_verification'] ?? '', 120),
            'organization_name' => $text($input['organization_name'] ?? '', 120),
            'organization_logo' => $url($input['organization_logo'] ?? ''),
            'social_linkedin' => $url($input['social_linkedin'] ?? ''),
            'social_github' => $url($input['social_github'] ?? ''),
            'social_facebook' => $url($input['social_facebook'] ?? ''),
            'social_x' => $url($input['social_x'] ?? ''),
            'allow_indexing' => !empty($input['allow_indexing']),
        ];

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->path(), json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

        return $settings;
    }

    /**
     * Social profile URLs that are set, for schema `sameAs`.
     *
     * @return array<int, string>
     */
    public function socialProfiles(): array
    {
        $s = $this->current();
        return array_values(array_filter([
            $s['social_linkedin'], $s['social_github'], $s['social_facebook'], $s['social_x'],
        ], static fn (string $u): bool => $u !== ''));
    }

    private function path(): string
    {
        return base_path('storage/data/seo-settings.json');
    }
}

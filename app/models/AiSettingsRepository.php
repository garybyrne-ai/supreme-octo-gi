<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Optional LLM credentials for the AI content assistant. When an admin sets a
 * provider, API key and model, the assistant calls that API; with no key it
 * falls back to a deterministic, template-based generator so the tool always
 * produces useful output. JSON-backed — no database required.
 */
final class AiSettingsRepository
{
    public const PROVIDERS = ['openai', 'anthropic'];

    public function current(): array
    {
        $default = [
            'provider' => 'openai',
            'api_key' => '',
            'model' => '',
            'enabled' => false,
        ];

        $path = $this->path();
        if (!is_file($path)) {
            return $default;
        }

        $saved = json_decode((string) file_get_contents($path), true);
        return is_array($saved) ? array_replace($default, $saved) : $default;
    }

    /**
     * True only when the integration is switched on and has a key — i.e. a live
     * LLM call is possible. The tool works either way; this just picks the path.
     */
    public function isLive(): bool
    {
        $s = $this->current();
        return !empty($s['enabled']) && trim((string) ($s['api_key'] ?? '')) !== '';
    }

    public function save(array $input): array
    {
        $current = $this->current();
        $provider = in_array($input['provider'] ?? '', self::PROVIDERS, true) ? (string) $input['provider'] : 'openai';

        // Preserve the saved key when the field is left blank (write-once secret).
        $apiKey = trim((string) ($input['api_key'] ?? ''));
        if ($apiKey === '') {
            $apiKey = (string) ($current['api_key'] ?? '');
        }

        $model = trim((string) ($input['model'] ?? ''));
        if ($model === '') {
            $model = $provider === 'anthropic' ? 'claude-haiku-4-5-20251001' : 'gpt-4o-mini';
        }

        $settings = [
            'provider' => $provider,
            'api_key' => $apiKey,
            'model' => $model,
            'enabled' => !empty($input['enabled']),
        ];

        if ($settings['enabled'] && $settings['api_key'] === '') {
            throw new \RuntimeException('Add an API key before enabling the live AI provider.');
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
        return base_path('storage/data/ai-settings.json');
    }
}

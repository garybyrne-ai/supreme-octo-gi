<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CmsRepository;

final class TypographyEngine
{
    public const SELECTOR_MAP = [
        'body' => 'body',
        'h1' => 'h1, .hero h1, .subhero h1',
        'h2' => 'h2, .section-heading h2, .panel-head h2',
        'h3' => 'h3, .service-card h3, .cyber-card h3',
        'h4' => 'h4',
        'h5' => 'h5',
        'buttons' => 'button, .pill-button, .small-link, .whatsapp-button',
        'main-menu' => '.nav a, .nav-item > a',
        'mega-dropdowns' => '.mega-menu, .mega-menu a, .mega-grid span',
        'footer' => '.site-footer, .site-footer a, .footer-statusbar',
    ];

    private const GOOGLE_FONTS = [
        'Inter' => ['weights' => [400, 500, 600, 700, 800], 'fallback' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif'],
        'Raleway' => ['weights' => [400, 500, 600, 700, 800], 'fallback' => '"Segoe UI", Aptos, system-ui, sans-serif'],
        'Oxanium' => ['weights' => [400, 500, 600, 700, 800], 'fallback' => '"Segoe UI Variable Display", "Segoe UI", system-ui, sans-serif'],
        'Sora' => ['weights' => [400, 500, 600, 700, 800], 'fallback' => 'Inter, system-ui, sans-serif'],
        'Space Grotesk' => ['weights' => [400, 500, 600, 700], 'fallback' => 'Inter, system-ui, sans-serif'],
        'Manrope' => ['weights' => [400, 500, 600, 700, 800], 'fallback' => 'Inter, system-ui, sans-serif'],
        'IBM Plex Sans' => ['weights' => [400, 500, 600, 700], 'fallback' => 'Inter, system-ui, sans-serif'],
        'DM Sans' => ['weights' => [400, 500, 700], 'fallback' => 'Inter, system-ui, sans-serif'],
    ];

    public function __construct(private readonly CmsRepository $repository = new CmsRepository())
    {
    }

    public function verifiedGoogleFonts(): array
    {
        return self::GOOGLE_FONTS;
    }

    public function selectorMap(): array
    {
        return self::SELECTOR_MAP;
    }

    public function saveRules(array $rules, ?int $updatedBy = null): string
    {
        foreach ($rules as $selectorKey => $rule) {
            if (!is_array($rule)) {
                continue;
            }

            $selectorKey = (string) $selectorKey;
            $family = trim((string) ($rule['font_family'] ?? ''));
            if ($family === '') {
                continue;
            }

            $font = $this->fontDefinition($family);
            $weights = $this->allowedWeights($family, $rule['font_weights'] ?? []);
            $this->repository->upsertTypographyRule([
                'selector_key' => $selectorKey,
                'css_selector' => (string) ($rule['css_selector'] ?? self::SELECTOR_MAP[$selectorKey] ?? $selectorKey),
                'font_family' => $family,
                'font_source' => isset(self::GOOGLE_FONTS[$family]) ? 'google' : 'system',
                'font_weights' => $weights,
                'fallback_stack' => trim((string) ($rule['fallback_stack'] ?? $font['fallback'])),
                'font_display' => 'swap',
                'is_enabled' => !empty($rule['is_enabled']),
                'sort_order' => (int) ($rule['sort_order'] ?? 100),
                'updated_by' => $updatedBy,
            ]);
        }

        return $this->rebuildCache();
    }

    public function rebuildCache(): string
    {
        $css = $this->compile($this->repository->typographyRules());
        $this->repository->saveStyleCache('global-font-map', $css);
        return $css;
    }

    public function cachedStyleBlock(): string
    {
        $css = $this->repository->cachedStyle('global-font-map');
        if ($css === null) {
            $css = $this->rebuildCache();
        }

        return $css !== '' ? '<style id="cms-typography-map">' . $css . '</style>' : '';
    }

    private function compile(array $rules): string
    {
        if ($rules === []) {
            return '';
        }

        $variables = [];
        $selectors = [];
        foreach ($rules as $rule) {
            $key = $this->cssVarKey((string) $rule['selector_key']);
            $family = $this->quoteFont((string) $rule['font_family']);
            $fallback = trim((string) $rule['fallback_stack']);
            $weight = (int) (($rule['font_weights'][0] ?? 400));
            $selector = $this->safeSelector((string) $rule['css_selector']);

            if ($selector === '') {
                continue;
            }

            $variables[] = '--cms-font-' . $key . ':' . $family . ',' . $fallback . ';';
            $variables[] = '--cms-weight-' . $key . ':' . $weight . ';';
            $selectors[] = $selector . '{font-family:var(--cms-font-' . $key . ')!important;font-weight:var(--cms-weight-' . $key . ')}';
        }

        return ':root{' . implode('', $variables) . '}' . implode('', $selectors);
    }

    private function fontDefinition(string $family): array
    {
        return self::GOOGLE_FONTS[$family] ?? [
            'weights' => [400, 500, 600, 700],
            'fallback' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        ];
    }

    private function allowedWeights(string $family, mixed $weights): array
    {
        $allowed = $this->fontDefinition($family)['weights'];
        $weights = is_array($weights) ? $weights : [$weights];
        $clean = [];
        foreach ($weights as $weight) {
            $weight = (int) $weight;
            if (in_array($weight, $allowed, true)) {
                $clean[] = $weight;
            }
        }

        return $clean !== [] ? array_values(array_unique($clean)) : [(int) $allowed[0]];
    }

    private function safeSelector(string $selector): string
    {
        return preg_match('/^[a-zA-Z0-9\s\.\#\-\_\>\,\:\[\]\=\"\']+$/', $selector) ? $selector : '';
    }

    private function quoteFont(string $family): string
    {
        return '"' . str_replace('"', '', $family) . '"';
    }

    private function cssVarKey(string $key): string
    {
        $key = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $key) ?? 'font');
        return trim($key, '-') ?: 'font';
    }
}

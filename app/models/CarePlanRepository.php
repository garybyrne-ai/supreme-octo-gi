<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Backend-controlled Website Care Plans (monthly maintenance retainers).
 * Mirrors BacklinkPlanRepository: JSON-backed so it works with or without a
 * database and survives file-replacement upgrades; every commercial attribute
 * is editable from the admin console and read by the public /website-care-plans
 * page. Defaults ship three tiers whose rising price is justified by scope:
 * small brochure sites → lead-generation sites → e-commerce + Google Ads + SEO.
 */
final class CarePlanRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function defaults(): array
    {
        return [
            [
                'slug' => 'essential-care',
                'name' => 'Essential Care',
                'price' => '€49',
                'period' => '/month',
                'ideal_for' => 'Brochure & small business websites',
                'badge' => 'Peace of mind',
                'featured' => false,
                'checkout_url' => '',
                'sort_order' => 10,
                'active' => true,
                'features' => [
                    'For brochure & small business sites (up to ~10 pages)',
                    'Weekly off-site backups with restore cover',
                    'CMS core, theme & plugin updates (monthly cycle)',
                    'Uptime monitoring with instant email alerts',
                    'Monthly security scan & malware check',
                    '30 minutes of small fixes or content edits / month',
                    'Email support — 2 business-day response',
                ],
            ],
            [
                'slug' => 'growth-care',
                'name' => 'Growth Care',
                'price' => '€99',
                'period' => '/month',
                'ideal_for' => 'Lead-generation sites & content platforms',
                'badge' => 'Most popular',
                'featured' => true,
                'checkout_url' => '',
                'sort_order' => 20,
                'active' => true,
                'features' => [
                    'Everything in Essential Care',
                    'For sites that must rank & convert (lead-gen, blogs)',
                    'Daily backups + private staging environment',
                    'Speed & Core Web Vitals tune-up every quarter',
                    'Monthly SEO health report — rankings, errors, fixes',
                    '2 hours of development / content time per month',
                    'Priority support — same business-day response',
                ],
            ],
            [
                'slug' => 'commerce-campaign-care',
                'name' => 'Commerce & Campaign Care',
                'price' => '€199',
                'period' => '/month',
                'ideal_for' => 'E-shops, Google Ads campaigns & large SEO sites',
                'badge' => 'Maximum coverage',
                'featured' => false,
                'checkout_url' => '',
                'sort_order' => 30,
                'active' => true,
                'features' => [
                    'Everything in Growth Care',
                    'Built for e-commerce, booking systems & 50+ page sites',
                    'Checkout, payment & stock monitoring — revenue protection',
                    'Google Ads (AdWords) campaign management & optimisation',
                    'Ongoing SEO: technical fixes, content tweaks, link health',
                    '5 hours of development time per month',
                    '24/7 monitoring with same-day emergency response',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(bool $includeInactive = true): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            $plans = $this->defaults();
        } else {
            $saved = json_decode((string) file_get_contents($path), true);
            $plans = is_array($saved) && $saved !== [] ? array_map([$this, 'normalize'], $saved) : $this->defaults();
        }

        if (!$includeInactive) {
            $plans = array_values(array_filter($plans, static fn (array $p): bool => !empty($p['active'])));
        }

        usort($plans, static fn (array $a, array $b): int => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return $plans;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activePlans(): array
    {
        return $this->all(false);
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->all() as $plan) {
            if ($plan['slug'] === $slug) {
                return $plan;
            }
        }

        return null;
    }

    public function save(array $input): string
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('A plan name is required.');
        }

        $slug = $this->slug((string) ($input['slug'] ?? $name));
        if ($slug === '') {
            throw new \RuntimeException('A plan slug is required.');
        }

        $checkout = trim((string) ($input['checkout_url'] ?? ''));
        if ($checkout !== '' && !filter_var($checkout, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Checkout link must be a valid URL.');
        }

        $plan = [
            'slug' => $slug,
            'name' => mb_substr($name, 0, 80),
            'price' => mb_substr(trim((string) ($input['price'] ?? '')), 0, 20),
            'period' => mb_substr(trim((string) ($input['period'] ?? '/month')), 0, 20) ?: '/month',
            'ideal_for' => mb_substr(trim((string) ($input['ideal_for'] ?? '')), 0, 120),
            'badge' => mb_substr(trim((string) ($input['badge'] ?? '')), 0, 40),
            'featured' => !empty($input['featured']),
            'active' => !empty($input['active']),
            'checkout_url' => $checkout,
            'sort_order' => max(0, (int) ($input['sort_order'] ?? 100)),
            'features' => $this->parseFeatures($input['features'] ?? ''),
        ];

        $plans = $this->all();
        $replaced = false;
        $originalSlug = $this->slug((string) ($input['original_slug'] ?? $slug));
        foreach ($plans as $index => $existing) {
            if ($existing['slug'] === $originalSlug) {
                $plans[$index] = $plan;
                $replaced = true;
                break;
            }
        }
        if (!$replaced) {
            $plans[] = $plan;
        }

        $this->persist($plans);

        return $slug;
    }

    public function delete(string $slug): void
    {
        $slug = $this->slug($slug);
        $plans = array_values(array_filter($this->all(), static fn (array $p): bool => $p['slug'] !== $slug));
        $this->persist($plans);
    }

    public function setActive(string $slug, bool $active): void
    {
        $slug = $this->slug($slug);
        $plans = $this->all();
        foreach ($plans as $index => $plan) {
            if ($plan['slug'] === $slug) {
                $plans[$index]['active'] = $active;
            }
        }
        $this->persist($plans);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalize(array $row): array
    {
        $features = $row['features'] ?? [];
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : [];
        }

        return [
            'slug' => $this->slug((string) ($row['slug'] ?? '')),
            'name' => (string) ($row['name'] ?? ''),
            'price' => (string) ($row['price'] ?? ''),
            'period' => (string) ($row['period'] ?? '/month'),
            'ideal_for' => (string) ($row['ideal_for'] ?? ''),
            'badge' => (string) ($row['badge'] ?? ''),
            'featured' => !empty($row['featured']),
            'active' => array_key_exists('active', $row) ? !empty($row['active']) : true,
            'checkout_url' => (string) ($row['checkout_url'] ?? ''),
            'sort_order' => (int) ($row['sort_order'] ?? 100),
            'features' => is_array($features)
                ? array_values(array_filter(array_map('strval', $features), static fn (string $f): bool => trim($f) !== ''))
                : [],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseFeatures(mixed $value): array
    {
        $lines = is_array($value) ? $value : (preg_split('/\r\n|\r|\n/', (string) $value) ?: []);
        $features = array_map('trim', $lines);

        return array_values(array_filter($features, static fn (string $line): bool => $line !== ''));
    }

    private function slug(string $value): string
    {
        $value = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $value));

        return trim($value, '-');
    }

    private function persist(array $plans): void
    {
        usort($plans, static fn (array $a, array $b): int => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->path(),
            json_encode(array_values($plans), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function path(): string
    {
        return base_path('storage/data/care-plans.json');
    }
}

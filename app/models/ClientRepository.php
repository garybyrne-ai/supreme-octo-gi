<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Backend-managed client showcase + case studies. JSON-backed (no database
 * required). Logos render on the public home page "Our Clients" wall; the full
 * case-study detail is gated behind member login (see PageController::portfolio)
 * to protect client confidentiality and keep scrapers out. Every field is
 * editable from the admin "Clients" module, including the logo image path so
 * official logo files uploaded via the Media Library can be swapped in.
 */
final class ClientRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function defaults(): array
    {
        return [
            [
                'slug' => 'locks',
                'name' => 'LOCKS',
                'logo' => '/assets/images/clients/locks.svg',
                'url' => '',
                'industry' => 'Locksmith & Security',
                'services' => ['Website Build', 'Local SEO Audit', 'Booking Automation'],
                'summary' => 'A fast, trust-first website and local-SEO setup for a security & locksmith business, plus an automated call-out booking flow.',
                'challenge' => 'The business relied on phone calls and had no way to capture emergency call-out requests after hours. Their old site was slow, weak on local search, and leaked leads at night.',
                'work' => 'We rebuilt the site for speed and trust signals, structured it for local "locksmith near me" search intent, and wired an automated booking + notification flow so every after-hours request is captured and routed instantly.',
                'results' => [
                    'Mobile load time cut to under 2 seconds',
                    'Local search visibility for service-area keywords improved',
                    'After-hours call-out requests now captured automatically',
                ],
                'featured' => true,
                'sort_order' => 10,
                'active' => true,
            ],
            [
                'slug' => 'centra',
                'name' => 'Centra',
                'logo' => '/assets/images/clients/centra.svg',
                'url' => '',
                'industry' => 'Retail & Convenience',
                'services' => ['Website Build', 'SEO Audit', 'Workflow Automation'],
                'summary' => 'Web presence, a technical SEO audit and back-office workflow automation for a busy retail operation.',
                'challenge' => 'Manual, repetitive back-office tasks were eating staff hours, and the digital presence was not pulling its weight for local discovery.',
                'work' => 'We delivered a clean, fast web presence, ran a full technical SEO audit with a prioritised fix list, and automated repetitive operational workflows so the team spends less time on admin and more on customers.',
                'results' => [
                    'Repetitive back-office steps automated',
                    'Technical SEO issues identified and fixed',
                    'Faster, cleaner customer-facing web presence',
                ],
                'featured' => true,
                'sort_order' => 20,
                'active' => true,
            ],
            [
                'slug' => 'embryomic',
                'name' => 'Embryomic',
                'logo' => '/assets/images/clients/embryomic.svg',
                'url' => '',
                'industry' => 'HealthTech & Fertility',
                'services' => ['Website Build', 'Technical SEO', 'Data Workflow Automation'],
                'summary' => 'A credible, accessible website and data-workflow automation for a fertility & genomics healthtech company.',
                'challenge' => 'A science-led company needed a website that reads as credible and accessible to both clinicians and patients, plus less manual handling of structured data.',
                'work' => 'We built a clear, accessible, fast site with strong technical SEO foundations, and automated parts of their data workflow to reduce manual handling and error.',
                'results' => [
                    'Credible, accessible presence for a clinical audience',
                    'Solid technical-SEO foundation for organic growth',
                    'Manual data steps reduced through automation',
                ],
                'featured' => true,
                'sort_order' => 30,
                'active' => true,
            ],
            [
                'slug' => 'depil-concept',
                'name' => 'Depil Concept',
                'logo' => '/assets/images/clients/depil-concept.svg',
                'url' => '',
                'industry' => 'Beauty & Wellness',
                'services' => ['Website Build', 'Local SEO', 'Booking Automation'],
                'summary' => 'A polished salon website with local SEO and an automated booking flow for a beauty & hair-removal brand.',
                'challenge' => 'Bookings were handled manually and the brand needed a polished, on-brand website that ranks locally and converts browsers into appointments.',
                'work' => 'We designed an on-brand, conversion-focused website, optimised it for local salon search, and automated the booking and reminder flow to cut no-shows and admin time.',
                'results' => [
                    'On-brand, conversion-focused salon website',
                    'Improved local search visibility',
                    'Booking & reminder flow automated',
                ],
                'featured' => true,
                'sort_order' => 40,
                'active' => true,
            ],
            [
                'slug' => 'property-cleaning-cork',
                'name' => 'PropertyCleaningCork.ie',
                'logo' => '/assets/images/clients/property-cleaning-cork.svg',
                'url' => 'https://propertycleaningcork.ie',
                'industry' => 'Home & Exterior Services',
                'services' => ['Website Build', 'Irish (.ie) Local SEO', 'Lead Automation'],
                'summary' => 'A high-converting .ie website with local SEO and lead automation for an exterior cleaning specialist in Cork.',
                'challenge' => 'The business needed to win local Cork searches for exterior cleaning and stop losing quote requests that arrived outside working hours.',
                'work' => 'We built a fast, high-converting .ie website targeting Cork service-area keywords, ran a local SEO setup, and automated quote-request capture and follow-up so no lead is missed.',
                'results' => [
                    'Targeted for Cork exterior-cleaning search intent',
                    'Quote requests captured and followed up automatically',
                    'Fast, conversion-focused .ie website',
                ],
                'featured' => true,
                'sort_order' => 50,
                'active' => true,
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
            $clients = $this->defaults();
        } else {
            $saved = json_decode((string) file_get_contents($path), true);
            $clients = is_array($saved) && $saved !== [] ? array_map([$this, 'normalize'], $saved) : $this->defaults();
        }

        if (!$includeInactive) {
            $clients = array_values(array_filter($clients, static fn (array $c): bool => !empty($c['active'])));
        }

        usort($clients, static fn (array $a, array $b): int => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return $clients;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activeClients(): array
    {
        return $this->all(false);
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->all() as $client) {
            if ($client['slug'] === $slug) {
                return $client;
            }
        }

        return null;
    }

    public function save(array $input): string
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('A client name is required.');
        }

        $slug = $this->slug((string) ($input['slug'] ?? $name));
        if ($slug === '') {
            throw new \RuntimeException('A client slug is required.');
        }

        $url = trim((string) ($input['url'] ?? ''));
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Client website must be a valid URL.');
        }

        $client = [
            'slug' => $slug,
            'name' => mb_substr($name, 0, 100),
            'logo' => mb_substr(trim((string) ($input['logo'] ?? '')), 0, 300),
            'url' => $url,
            'industry' => mb_substr(trim((string) ($input['industry'] ?? '')), 0, 100),
            'services' => $this->parseLines($input['services'] ?? ''),
            'summary' => mb_substr(trim((string) ($input['summary'] ?? '')), 0, 400),
            'challenge' => mb_substr(trim((string) ($input['challenge'] ?? '')), 0, 1200),
            'work' => mb_substr(trim((string) ($input['work'] ?? '')), 0, 1200),
            'results' => $this->parseLines($input['results'] ?? ''),
            'featured' => !empty($input['featured']),
            'active' => !empty($input['active']),
            'sort_order' => max(0, (int) ($input['sort_order'] ?? 100)),
        ];

        $clients = $this->all();
        $replaced = false;
        $originalSlug = $this->slug((string) ($input['original_slug'] ?? $slug));
        foreach ($clients as $index => $existing) {
            if ($existing['slug'] === $originalSlug) {
                $clients[$index] = $client;
                $replaced = true;
                break;
            }
        }
        if (!$replaced) {
            $clients[] = $client;
        }

        $this->persist($clients);

        return $slug;
    }

    public function delete(string $slug): void
    {
        $slug = $this->slug($slug);
        $this->persist(array_values(array_filter($this->all(), static fn (array $c): bool => $c['slug'] !== $slug)));
    }

    public function setActive(string $slug, bool $active): void
    {
        $slug = $this->slug($slug);
        $clients = $this->all();
        foreach ($clients as $index => $client) {
            if ($client['slug'] === $slug) {
                $clients[$index]['active'] = $active;
            }
        }
        $this->persist($clients);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalize(array $row): array
    {
        return [
            'slug' => $this->slug((string) ($row['slug'] ?? '')),
            'name' => (string) ($row['name'] ?? ''),
            'logo' => (string) ($row['logo'] ?? ''),
            'url' => (string) ($row['url'] ?? ''),
            'industry' => (string) ($row['industry'] ?? ''),
            'services' => $this->stringList($row['services'] ?? []),
            'summary' => (string) ($row['summary'] ?? ''),
            'challenge' => (string) ($row['challenge'] ?? ''),
            'work' => (string) ($row['work'] ?? ''),
            'results' => $this->stringList($row['results'] ?? []),
            'featured' => !empty($row['featured']),
            'active' => array_key_exists('active', $row) ? !empty($row['active']) : true,
            'sort_order' => (int) ($row['sort_order'] ?? 100),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseLines(mixed $value): array
    {
        $lines = is_array($value) ? $value : (preg_split('/\r\n|\r|\n/', (string) $value) ?: []);

        return array_values(array_filter(array_map(static fn ($l): string => trim(strip_tags((string) $l)), $lines), static fn (string $l): bool => $l !== ''));
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return $this->parseLines($value);
        }

        return array_values(array_filter(array_map('strval', $value), static fn (string $l): bool => trim($l) !== ''));
    }

    private function slug(string $value): string
    {
        $value = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $value));

        return trim($value, '-');
    }

    private function persist(array $clients): void
    {
        usort($clients, static fn (array $a, array $b): int => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->path(),
            json_encode(array_values($clients), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function path(): string
    {
        return base_path('storage/data/clients.json');
    }
}

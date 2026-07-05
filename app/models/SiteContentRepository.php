<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Backend-editable site content store. Gives the admin console WordPress-style
 * control over the home hero, the contact / DIRECT SIGNAL block and the intro
 * headings of key marketing pages, without touching code.
 *
 * Backed by a single JSON file (storage/data/site-content.json) so it works
 * whether or not the database is installed and survives file-replacement
 * upgrades. Defaults mirror the current hard-coded markup exactly, so nothing
 * changes on screen until an admin saves an edit.
 */
final class SiteContentRepository
{
    /**
     * @return array<string, array<int, string>>
     */
    public function pageIntroDefinitions(): array
    {
        return [
            'about' => ['About Crest Web Media', 'About'],
            'process' => ['Our Delivery Process', 'Process'],
            'pricing' => ['Transparent Pricing', 'Pricing'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'hero' => [
                'chip_text' => 'Remote. Precise. Built For Growth.',
                'headline' => 'We Build Digital Systems That Work. <span>Scale.</span> And <span>Make Money.</span>',
                'subheading' => 'Websites, PHP platforms, ecommerce, apps, SEO and AI workflows engineered for fast loading, clearer decisions and qualified demand.',
                'serving_label' => 'Serving Clients In',
                'serving' => [
                    ['flag' => '/assets/images/flags/ie.svg', 'label' => 'Ireland'],
                    ['flag' => '/assets/images/flags/gb.svg', 'label' => 'UK'],
                    ['flag' => '/assets/images/flags/us.svg', 'label' => 'USA'],
                    ['flag' => '/assets/images/flags/eu.svg', 'label' => 'Europe'],
                    ['flag' => '/assets/images/flags/in.svg', 'label' => 'India'],
                    ['flag' => '/assets/images/flags/world.svg', 'label' => 'And Beyond'],
                ],
                'cta_primary_label' => 'View My Work',
                'cta_primary_url' => '/portfolio',
                'cta_secondary_label' => 'Free Security Tools',
                'cta_secondary_url' => '/free-penetration-testing-tools',
                'ticker' => [
                    '99.9% uptime architecture',
                    'Core Web Vitals: green',
                    'A+ security headers',
                    'GDPR-ready builds',
                    '300+ projects delivered',
                    '24/7 monitoring & support',
                ],
            ],
            'contact' => [
                'email' => 'ank.kalia@gmail.com',
                'phone' => '+918894867819',
                'phone_display' => '+91 88948 67819',
                'whatsapp_url' => 'https://wa.me/918894867819',
                'signal_line' => 'Dublin + Shimla',
                'locations' => [
                    ['name' => 'Dublin, Ireland', 'type' => 'Global client coordination', 'timezone' => 'GMT / IST project overlap'],
                    ['name' => 'Shimla, Himachal Pradesh, India', 'type' => 'Remote development studio', 'timezone' => 'Asia/Kolkata'],
                ],
            ],
            'page_intros' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $defaults = $this->defaults();
        $path = $this->path();
        if (!is_file($path)) {
            return $defaults;
        }

        $saved = json_decode((string) file_get_contents($path), true);
        if (!is_array($saved)) {
            return $defaults;
        }

        return [
            'hero' => array_replace($defaults['hero'], is_array($saved['hero'] ?? null) ? $this->cleanHero($saved['hero']) : []),
            'contact' => array_replace($defaults['contact'], is_array($saved['contact'] ?? null) ? $this->cleanContact($saved['contact']) : []),
            'page_intros' => is_array($saved['page_intros'] ?? null) ? $saved['page_intros'] : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function hero(): array
    {
        return $this->all()['hero'];
    }

    /**
     * @return array<string, mixed>
     */
    public function contact(): array
    {
        return $this->all()['contact'];
    }

    /**
     * Intro heading + kicker for a marketing page, falling back to the supplied
     * built-in defaults when an admin has not overridden them.
     *
     * @return array{heading: string, kicker: string}
     */
    public function pageIntro(string $key, string $defaultHeading = '', string $defaultKicker = ''): array
    {
        $intros = $this->all()['page_intros'];
        $saved = is_array($intros[$key] ?? null) ? $intros[$key] : [];

        $heading = trim((string) ($saved['heading'] ?? ''));
        $kicker = trim((string) ($saved['kicker'] ?? ''));

        return [
            'heading' => $heading !== '' ? $heading : $defaultHeading,
            'kicker' => $kicker !== '' ? $kicker : $defaultKicker,
        ];
    }

    public function saveHero(array $input): void
    {
        $data = $this->all();

        $serving = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) ($input['serving'] ?? '')) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            $serving[] = ['flag' => $parts[0] ?? '', 'label' => $parts[1] ?? ($parts[0] ?? '')];
        }

        $ticker = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) ($input['ticker'] ?? '')) ?: [] as $line) {
            $line = trim(strip_tags($line));
            if ($line !== '') {
                $ticker[] = mb_substr($line, 0, 80);
            }
        }

        $data['hero'] = array_replace($data['hero'], [
            'chip_text' => $this->text($input['chip_text'] ?? '', 120),
            'headline' => $this->richText($input['headline'] ?? '', 300),
            'subheading' => $this->text($input['subheading'] ?? '', 600),
            'serving_label' => $this->text($input['serving_label'] ?? '', 60),
            'serving' => $serving !== [] ? $serving : $data['hero']['serving'],
            'ticker' => $ticker !== [] ? $ticker : ($data['hero']['ticker'] ?? []),
            'cta_primary_label' => $this->text($input['cta_primary_label'] ?? '', 60),
            'cta_primary_url' => $this->url($input['cta_primary_url'] ?? '', '/portfolio'),
            'cta_secondary_label' => $this->text($input['cta_secondary_label'] ?? '', 60),
            'cta_secondary_url' => $this->url($input['cta_secondary_url'] ?? '', '/free-penetration-testing-tools'),
        ]);

        $this->persist($data);
    }

    public function saveContact(array $input): void
    {
        $data = $this->all();

        $email = strtolower(trim((string) ($input['email'] ?? '')));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Enter a valid contact email address.');
        }

        $whatsapp = trim((string) ($input['whatsapp_url'] ?? ''));
        if ($whatsapp !== '' && !filter_var($whatsapp, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('WhatsApp link must be a valid URL (e.g. https://wa.me/…).');
        }

        $locations = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) ($input['locations'] ?? '')) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 3));
            $locations[] = [
                'name' => $parts[0] ?? '',
                'type' => $parts[1] ?? '',
                'timezone' => $parts[2] ?? '',
            ];
        }

        $data['contact'] = array_replace($data['contact'], [
            'email' => $email !== '' ? $email : $data['contact']['email'],
            'phone' => $this->text($input['phone'] ?? '', 40),
            'phone_display' => $this->text($input['phone_display'] ?? '', 40),
            'whatsapp_url' => $whatsapp !== '' ? $whatsapp : $data['contact']['whatsapp_url'],
            'signal_line' => $this->text($input['signal_line'] ?? '', 80),
            'locations' => $locations !== [] ? $locations : $data['contact']['locations'],
        ]);

        $this->persist($data);
    }

    public function savePageIntros(array $input): void
    {
        $data = $this->all();
        $intros = [];

        foreach (array_keys($this->pageIntroDefinitions()) as $key) {
            $heading = $this->text($input[$key . '_heading'] ?? '', 160);
            $kicker = $this->text($input[$key . '_kicker'] ?? '', 60);
            if ($heading === '' && $kicker === '') {
                continue;
            }
            $intros[$key] = ['heading' => $heading, 'kicker' => $kicker];
        }

        $data['page_intros'] = $intros;
        $this->persist($data);
    }

    /**
     * @param array<string, mixed> $hero
     * @return array<string, mixed>
     */
    private function cleanHero(array $hero): array
    {
        if (isset($hero['serving']) && is_array($hero['serving'])) {
            $hero['serving'] = array_values(array_filter(array_map(static function ($row): ?array {
                if (!is_array($row)) {
                    return null;
                }
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    return null;
                }
                return ['flag' => trim((string) ($row['flag'] ?? '')), 'label' => $label];
            }, $hero['serving'])));
            if ($hero['serving'] === []) {
                unset($hero['serving']);
            }
        }

        if (isset($hero['ticker']) && is_array($hero['ticker'])) {
            $hero['ticker'] = array_values(array_filter(
                array_map(static fn ($t): string => trim((string) $t), $hero['ticker']),
                static fn (string $t): bool => $t !== ''
            ));
            if ($hero['ticker'] === []) {
                unset($hero['ticker']);
            }
        }

        return $hero;
    }

    /**
     * @param array<string, mixed> $contact
     * @return array<string, mixed>
     */
    private function cleanContact(array $contact): array
    {
        if (isset($contact['locations']) && is_array($contact['locations'])) {
            $contact['locations'] = array_values(array_filter(array_map(static function ($row): ?array {
                if (!is_array($row)) {
                    return null;
                }
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    return null;
                }
                return [
                    'name' => $name,
                    'type' => trim((string) ($row['type'] ?? '')),
                    'timezone' => trim((string) ($row['timezone'] ?? '')),
                ];
            }, $contact['locations'])));
            if ($contact['locations'] === []) {
                unset($contact['locations']);
            }
        }

        return $contact;
    }

    private function text(mixed $value, int $max): string
    {
        return mb_substr(trim(strip_tags((string) $value)), 0, $max);
    }

    private function richText(mixed $value, int $max): string
    {
        // Admin-only field. Allow the <span> highlight tags used in the hero
        // headline, but strip everything else to keep the markup safe.
        return mb_substr(trim(strip_tags((string) $value, '<span>')), 0, $max);
    }

    private function url(mixed $value, string $fallback): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }
        if (str_starts_with($value, '/') || filter_var($value, FILTER_VALIDATE_URL)) {
            return mb_substr($value, 0, 300);
        }

        return $fallback;
    }

    private function persist(array $data): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->path(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function path(): string
    {
        return base_path('storage/data/site-content.json');
    }
}

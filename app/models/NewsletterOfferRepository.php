<?php

declare(strict_types=1);

namespace App\Models;

final class NewsletterOfferRepository
{
    public function current(): array
    {
        $default = [
            'subject' => 'Your SEO Growth Offer from Crest Web Media',
            'preheader' => 'SEO, PPC, website and app development built to win more enquiries.',
            'headline' => 'Turn Your Website Into a SERP + Lead Machine',
            'intro' => 'Thanks for using the Crest Web Media tools. We build SEO-focused websites, apps and automations that help businesses compete for top search positions and convert more visitors into enquiries.',
            'offer' => 'Book a free SEO, PPC and website growth audit. We will review your site, SERP opportunities, paid search potential, website speed, conversion flow and app/workflow automation ideas.',
            'cta_label' => 'Claim Free Growth Audit',
            'cta_url' => 'https://crestwebmedia.com/contact',
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
        $offer = [
            'subject' => trim((string) ($input['subject'] ?? '')),
            'preheader' => trim((string) ($input['preheader'] ?? '')),
            'headline' => trim((string) ($input['headline'] ?? '')),
            'intro' => trim((string) ($input['intro'] ?? '')),
            'offer' => trim((string) ($input['offer'] ?? '')),
            'cta_label' => trim((string) ($input['cta_label'] ?? '')),
            'cta_url' => trim((string) ($input['cta_url'] ?? '')),
        ];

        foreach ($offer as $value) {
            if ($value === '') {
                throw new \RuntimeException('All newsletter offer fields are required.');
            }
        }

        if (!filter_var($offer['cta_url'], FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('CTA URL must be a valid URL.');
        }

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($offer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $offer;
    }

    private function path(): string
    {
        return base_path('storage/data/newsletter-offer.json');
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

final class SeoService
{
    public static function organizationSchema(array $config): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $config['name'],
            'url' => $config['url'],
            'description' => $config['tagline'],
            'email' => $config['admin_email'],
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'contactType' => 'customer support',
                    'email' => $config['admin_email'],
                    'availableLanguage' => ['English'],
                ],
                [
                    '@type' => 'ContactPoint',
                    'contactType' => 'WhatsApp and ticket support',
                    'url' => 'https://wa.me/918894867819',
                    'availableLanguage' => ['English'],
                ],
            ],
            'areaServed' => ['Worldwide'],
            'location' => [
                ['@type' => 'Place', 'name' => 'Dublin, Ireland'],
                ['@type' => 'Place', 'name' => 'Shimla, Himachal Pradesh, India'],
            ],
            'foundingLocation' => 'Shimla, Himachal Pradesh, India',
            'sameAs' => [
                'https://www.linkedin.com/',
                'https://github.com/',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function faqSchema(array $faqs): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ], $faqs),
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

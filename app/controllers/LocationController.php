<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\IrelandData;

/**
 * Generates the Ireland location-SEO landing system:
 *  - /locations                 hub of all 32 counties by province
 *  - /locations/{county}        county page linking every service locally
 *  - /{service}-{county}        local landing page (e.g. web-design-dublin)
 *  - /{service}-ireland         national service page (e.g. seo-ireland)
 *
 * The final catch-all route sends any unmatched single-segment path here, so
 * `landing()` must 404 cleanly when the slug is not a real location page.
 */
final class LocationController extends Controller
{
    public function hub(): void
    {
        $this->render('pages/locations-hub', [
            'title' => 'Web Design & Digital Services Across Ireland | Crest Web Media',
            'metaDescription' => 'Web design, development, SEO, ecommerce and Google Ads for businesses in every county of Ireland — Dublin, Cork, Galway, Limerick, Belfast and beyond.',
            'provinces' => IrelandData::countiesByProvince(),
            'services' => IrelandData::services(),
            'countyCount' => count(IrelandData::counties()),
        ]);
    }

    public function county(string $county): void
    {
        $data = IrelandData::county($county);
        if ($data === null) {
            $this->notFound();
            return;
        }

        $this->render('pages/location-county', [
            'title' => 'Web Design & Digital Services in ' . $data['name'] . ' | Crest Web Media',
            'metaDescription' => 'Web design, website development, SEO, ecommerce, WordPress and Google Ads for businesses across County ' . $data['name'] . ', Ireland.',
            'county' => $data,
            'services' => IrelandData::services(),
            'nearby' => $this->nearbyCounties($data),
            'schema' => $this->breadcrumbSchema([
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Locations', 'url' => '/locations'],
                ['name' => $data['name'], 'url' => '/locations/' . $data['slug']],
            ]),
        ]);
    }

    public function landing(string $slug): void
    {
        $slug = strtolower(trim($slug, '/'));

        foreach (IrelandData::services() as $service) {
            $prefix = $service['slug'] . '-';
            if (!str_starts_with($slug, $prefix)) {
                continue;
            }

            $areaSlug = substr($slug, strlen($prefix));

            if ($areaSlug === 'ireland') {
                $this->renderServicePage($service, null);
                return;
            }

            $county = IrelandData::county($areaSlug);
            if ($county !== null) {
                $this->renderServicePage($service, $county);
                return;
            }
        }

        $this->notFound();
    }

    private function renderServicePage(array $service, ?array $county): void
    {
        $areaName = $county['name'] ?? 'Ireland';
        $isNational = $county === null;
        $heading = $service['name'] . ' in ' . $areaName;

        $crumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Locations', 'url' => '/locations'],
        ];
        if (!$isNational) {
            $crumbs[] = ['name' => $county['name'], 'url' => '/locations/' . $county['slug']];
        }
        $crumbs[] = ['name' => $service['name'], 'url' => '/' . $service['slug'] . '-' . ($county['slug'] ?? 'ireland')];

        $this->render('pages/location-service', [
            'title' => $heading . ' | Crest Web Media',
            'metaDescription' => $service['blurb'] . ' Serving ' . ($isNational
                ? 'businesses across Ireland.'
                : $areaName . ' and surrounding towns.'),
            'service' => $service,
            'county' => $county,
            'areaName' => $areaName,
            'isNational' => $isNational,
            'otherServices' => $this->otherServices($service['slug'], $county),
            'nearby' => $isNational ? [] : $this->nearbyCounties($county),
            'schema' => $this->breadcrumbSchema($crumbs),
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function otherServices(string $currentSlug, ?array $county): array
    {
        $areaSlug = $county['slug'] ?? 'ireland';
        $links = [];
        foreach (IrelandData::services() as $service) {
            if ($service['slug'] === $currentSlug) {
                continue;
            }
            $links[] = [
                'name' => $service['name'] . ' in ' . ($county['name'] ?? 'Ireland'),
                'short' => $service['name'],
                'url' => '/' . $service['slug'] . '-' . $areaSlug,
                'icon' => $service['icon'],
                'tagline' => $service['tagline'],
            ];
        }

        return $links;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function nearbyCounties(array $county): array
    {
        $nearby = [];
        foreach (IrelandData::counties() as $candidate) {
            if ($candidate['slug'] === $county['slug'] || $candidate['province'] !== $county['province']) {
                continue;
            }
            $nearby[] = ['name' => $candidate['name'], 'slug' => $candidate['slug']];
            if (count($nearby) >= 6) {
                break;
            }
        }

        return $nearby;
    }

    private function breadcrumbSchema(array $items): string
    {
        $config = $this->config;
        $base = rtrim((string) ($config['url'] ?? ''), '/');

        $elements = [];
        foreach ($items as $index => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $base . $item['url'],
            ];
        }

        return (string) json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ], JSON_UNESCAPED_SLASHES);
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404', [
            'title' => 'Page Not Found',
            'metaDescription' => 'The page you requested could not be found.',
        ]);
    }
}

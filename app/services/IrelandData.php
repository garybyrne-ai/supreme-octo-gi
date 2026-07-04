<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Static data for the Ireland location-SEO landing system: the 32 counties of
 * the island grouped by province, plus the core services used to generate
 * local "{service} {county}" pages (e.g. Web Design Dublin, SEO Cork).
 */
final class IrelandData
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function counties(): array
    {
        $rows = [
            // Leinster
            ['dublin', 'Dublin', 'Leinster', ['Dublin City', 'Swords', 'Tallaght', 'Dún Laoghaire']],
            ['wicklow', 'Wicklow', 'Leinster', ['Bray', 'Greystones', 'Arklow']],
            ['wexford', 'Wexford', 'Leinster', ['Wexford Town', 'Enniscorthy', 'Gorey']],
            ['carlow', 'Carlow', 'Leinster', ['Carlow Town', 'Tullow', 'Bagenalstown']],
            ['kildare', 'Kildare', 'Leinster', ['Naas', 'Newbridge', 'Maynooth']],
            ['meath', 'Meath', 'Leinster', ['Navan', 'Ashbourne', 'Trim']],
            ['louth', 'Louth', 'Leinster', ['Dundalk', 'Drogheda', 'Ardee']],
            ['longford', 'Longford', 'Leinster', ['Longford Town', 'Granard']],
            ['offaly', 'Offaly', 'Leinster', ['Tullamore', 'Birr', 'Edenderry']],
            ['westmeath', 'Westmeath', 'Leinster', ['Athlone', 'Mullingar']],
            ['laois', 'Laois', 'Leinster', ['Portlaoise', 'Portarlington']],
            ['kilkenny', 'Kilkenny', 'Leinster', ['Kilkenny City', 'Callan', 'Thomastown']],
            // Munster
            ['cork', 'Cork', 'Munster', ['Cork City', 'Ballincollig', 'Mallow', 'Midleton']],
            ['kerry', 'Kerry', 'Munster', ['Tralee', 'Killarney', 'Listowel']],
            ['limerick', 'Limerick', 'Munster', ['Limerick City', 'Newcastle West']],
            ['clare', 'Clare', 'Munster', ['Ennis', 'Shannon', 'Kilrush']],
            ['tipperary', 'Tipperary', 'Munster', ['Clonmel', 'Nenagh', 'Thurles']],
            ['waterford', 'Waterford', 'Munster', ['Waterford City', 'Dungarvan', 'Tramore']],
            // Connacht
            ['galway', 'Galway', 'Connacht', ['Galway City', 'Tuam', 'Ballinasloe']],
            ['mayo', 'Mayo', 'Connacht', ['Castlebar', 'Ballina', 'Westport']],
            ['roscommon', 'Roscommon', 'Connacht', ['Roscommon Town', 'Boyle']],
            ['sligo', 'Sligo', 'Connacht', ['Sligo Town', 'Ballymote']],
            ['leitrim', 'Leitrim', 'Connacht', ['Carrick-on-Shannon', 'Manorhamilton']],
            // Ulster
            ['donegal', 'Donegal', 'Ulster', ['Letterkenny', 'Buncrana', 'Donegal Town']],
            ['cavan', 'Cavan', 'Ulster', ['Cavan Town', 'Bailieborough']],
            ['monaghan', 'Monaghan', 'Ulster', ['Monaghan Town', 'Carrickmacross']],
            ['antrim', 'Antrim', 'Ulster', ['Belfast', 'Ballymena', 'Lisburn']],
            ['armagh', 'Armagh', 'Ulster', ['Armagh City', 'Portadown', 'Lurgan']],
            ['derry', 'Derry', 'Ulster', ['Derry City', 'Coleraine', 'Limavady']],
            ['down', 'Down', 'Ulster', ['Newry', 'Bangor', 'Newtownards']],
            ['fermanagh', 'Fermanagh', 'Ulster', ['Enniskillen', 'Lisnaskea']],
            ['tyrone', 'Tyrone', 'Ulster', ['Omagh', 'Dungannon', 'Cookstown']],
        ];

        $counties = [];
        foreach ($rows as [$slug, $name, $province, $towns]) {
            $counties[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'province' => $province,
                'towns' => $towns,
            ];
        }

        return $counties;
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function countiesByProvince(): array
    {
        $grouped = ['Leinster' => [], 'Munster' => [], 'Connacht' => [], 'Ulster' => []];
        foreach (self::counties() as $county) {
            $grouped[$county['province']][] = $county;
        }

        return $grouped;
    }

    public static function county(string $slug): ?array
    {
        return self::counties()[strtolower($slug)] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function services(): array
    {
        $rows = [
            [
                'web-design', 'Web Design', 'fa-wand-magic-sparkles',
                'Conversion-focused web design',
                'High-converting, mobile-first website design that builds trust and turns local visitors into written enquiries.',
                ['Custom UI/UX design', 'Mobile-first responsive layouts', 'Brand and colour system', 'Conversion-mapped page structure', 'Accessibility and Core Web Vitals pass'],
                ['A site that looks credible to local buyers', 'Faster load and clearer calls to action', 'Design that matches your brand and market'],
            ],
            [
                'website-development', 'Website Development', 'fa-code',
                'Fast, secure website development',
                'Lean, secure PHP/WordPress websites and custom CMS builds engineered for speed, search visibility and easy editing.',
                ['Custom CMS or WordPress build', 'Secure PHP 8 / MySQL backend', 'Performance and caching setup', 'On-page SEO foundations', 'Handover and training'],
                ['A maintainable, fast platform', 'Security best-practice baked in', 'Full control of your content'],
            ],
            [
                'seo', 'SEO Services', 'fa-chart-line',
                'Local and national SEO',
                'Technical and local SEO that grows high-intent organic traffic and helps you rank for the terms your customers actually search.',
                ['Technical SEO audit', 'Local SEO and Google Business Profile', 'Keyword and content strategy', 'On-page optimisation', 'Monthly reporting'],
                ['More qualified organic enquiries', 'Stronger local map visibility', 'Content that targets buyer intent'],
            ],
            [
                'ecommerce-development', 'Ecommerce Development', 'fa-cart-shopping',
                'Ecommerce stores that sell',
                'WooCommerce, Shopify-style and custom ecommerce builds focused on product discovery, checkout trust and abandoned-cart recovery.',
                ['Store design and build', 'Secure Stripe and PayPal checkout', 'Product, VAT and shipping setup', 'Speed and conversion tuning', 'Abandoned-cart recovery'],
                ['A store customers trust at checkout', 'Lower drop-off and more sales', 'Scalable product management'],
            ],
            [
                'wordpress-development', 'WordPress Development', 'fa-plug-circle-bolt',
                'WordPress design and development',
                'Custom WordPress themes, plugins and Elementor builds — fast, secure and easy for your team to update.',
                ['Custom theme or Elementor build', 'Plugin configuration and hardening', 'Speed optimisation', 'Security and backups', 'Editor training'],
                ['A professional, on-brand WordPress site', 'No bloated, insecure templates', 'Easy day-to-day editing'],
            ],
            [
                'google-ads', 'Google Ads Management', 'fa-bullseye',
                'Google Ads and PPC management',
                'Profitable Google Ads and PPC campaigns with tight targeting, strong landing pages and clear return-on-ad-spend reporting.',
                ['Campaign strategy and build', 'Keyword and audience targeting', 'Landing page alignment', 'Conversion tracking', 'ROAS reporting'],
                ['Faster lead generation', 'Budget spent on buyer intent', 'Clear view of return on spend'],
            ],
            [
                'app-development', 'App Development', 'fa-mobile-screen-button',
                'Web and mobile app development',
                'Workflow-led web and mobile apps with role-aware dashboards, secure APIs and resilient data operations.',
                ['Product and data architecture', 'Web / mobile app build', 'Role-based dashboards', 'Secure API foundations', 'Release and support'],
                ['A product built around real workflows', 'Structured, secure data', 'Room to scale features'],
            ],
            [
                'digital-marketing', 'Digital Marketing', 'fa-arrow-trend-up',
                'Full-funnel digital marketing',
                'Joined-up SEO, PPC, content and automation that turns local attention into a predictable pipeline of qualified enquiries.',
                ['Growth strategy and roadmap', 'SEO and content', 'Paid search and social', 'AI lead automation', 'Reporting and optimisation'],
                ['One coordinated growth plan', 'Attention that converts to leads', 'Measurable month-on-month progress'],
            ],
        ];

        $services = [];
        foreach ($rows as [$slug, $name, $icon, $tagline, $blurb, $deliverables, $benefits]) {
            $services[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'icon' => $icon,
                'tagline' => $tagline,
                'blurb' => $blurb,
                'deliverables' => $deliverables,
                'benefits' => $benefits,
            ];
        }

        return $services;
    }

    public static function service(string $slug): ?array
    {
        return self::services()[strtolower($slug)] ?? null;
    }
}

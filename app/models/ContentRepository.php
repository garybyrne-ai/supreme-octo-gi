<?php

declare(strict_types=1);

namespace App\Models;

final class ContentRepository
{
    public function contact(): array
    {
        return [
            'email' => 'ank.kalia@gmail.com',
            'phone' => '+918894867819',
            'phone_display' => '+91 88948 67819',
            'whatsapp_url' => 'https://wa.me/918894867819',
            'locations' => [
                [
                    'name' => 'Dublin, Ireland',
                    'type' => 'Global client coordination',
                    'timezone' => 'GMT / IST project overlap',
                ],
                [
                    'name' => 'Shimla, Himachal Pradesh, India',
                    'type' => 'Remote development studio',
                    'timezone' => 'Asia/Kolkata',
                ],
            ],
        ];
    }

    public function stats(): array
    {
        return [
            ['value' => '300+', 'label' => 'Websites & Apps Delivered', 'icon' => 'fa-code'],
            ['value' => '50+', 'label' => 'Happy Clients Worldwide', 'icon' => 'fa-globe'],
            ['value' => '8+', 'label' => 'Years of Experience', 'icon' => 'fa-calendar-check'],
            ['value' => '100%', 'label' => 'Secure & Optimized Solutions', 'icon' => 'fa-shield-halved'],
            ['value' => '99.9%', 'label' => 'Uptime Delivered', 'icon' => 'fa-gauge-high'],
            ['value' => '24/7', 'label' => 'Support & Maintenance', 'icon' => 'fa-clock'],
        ];
    }

    public function services(): array
    {
        $services = [
            [
                'slug' => 'website-development',
                'title' => 'Website Development',
                'summary' => 'Lean PHP 8 and Laravel architectures built for sub-second edge paint, secure CMS control and qualified B2B pipeline.',
                'icon' => 'fa-solid fa-code',
                'tags' => ['PHP 8 Architecture', 'Laravel Delivery', 'MySQL Performance', 'Custom CMS', 'Edge-Speed UX'],
            ],
            [
                'slug' => 'web-design',
                'title' => 'Web Design',
                'summary' => 'Conversion-mapped interface systems engineered to reduce cognitive friction and make complex offers easier to buy.',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'tags' => ['UX Architecture', 'Design Systems', 'Conversion Mapping', 'Interaction Clarity'],
            ],
            [
                'slug' => 'app-development',
                'title' => 'App Development',
                'summary' => 'Workflow-led web and mobile products with role-aware dashboards, API foundations and resilient data operations.',
                'icon' => 'fa-solid fa-mobile-screen-button',
                'tags' => ['Product Architecture', 'Android / iOS', 'API Layer', 'Operational Dashboards'],
            ],
            [
                'slug' => 'app-publishing',
                'title' => 'App Publishing',
                'summary' => 'Store-release operations for Android and iOS teams that need clean compliance, launch assets and review-ready builds.',
                'icon' => 'fa-solid fa-cloud-arrow-up',
                'tags' => ['Play Store Ops', 'App Store Review', 'Privacy Compliance', 'Release QA'],
            ],
            [
                'slug' => 'api-integration',
                'title' => 'API Integration',
                'summary' => 'Decoupled API and webhook layers for payments, CRMs, booking engines and automation stacks that cannot afford data drift.',
                'icon' => 'fa-solid fa-plug-circle-bolt',
                'tags' => ['REST Orchestration', 'Webhook Validation', 'Stripe / PayPal', 'CRM Sync', 'Automation Logic'],
            ],
            [
                'slug' => 'ai-integration-services',
                'title' => 'AI Integration Services',
                'summary' => 'Practical AI layers embedded into existing websites, CRMs and support workflows with guardrails, logging and human approval.',
                'icon' => 'fa-solid fa-robot',
                'tags' => ['AI Enablement', 'OpenAI APIs', 'Workflow Guardrails', 'Human-in-the-Loop'],
            ],
            [
                'slug' => 'ai-workflow-automation',
                'title' => 'AI Workflow Automation',
                'summary' => 'AI-assisted operating flows for intake, routing, summaries and CRM updates where speed matters but control still matters more.',
                'icon' => 'fa-solid fa-diagram-project',
                'tags' => ['AI Operations', 'Workflow Automation', 'Lead Routing', 'Process Intelligence'],
            ],
            [
                'slug' => 'ai-chatbot-development',
                'title' => 'AI Chatbot Development',
                'summary' => 'Service-trained assistants that qualify leads, answer commercial questions and hand off support with useful context.',
                'icon' => 'fa-solid fa-comments',
                'tags' => ['AI Assistant UX', 'Lead Qualification', 'Support Triage', 'Knowledge Retrieval'],
            ],
            [
                'slug' => 'ai-crm-automation',
                'title' => 'AI CRM Automation',
                'summary' => 'Sales operations automation for cleaner CRM records, faster follow-up and account-level lead intelligence.',
                'icon' => 'fa-solid fa-user-gear',
                'tags' => ['CRM Intelligence', 'Sales Automation', 'Lead Scoring', 'Follow-up Systems'],
            ],
            [
                'slug' => 'penetration-testing',
                'title' => 'Penetration Testing',
                'summary' => 'Responsible security reviews for web apps, APIs, admin surfaces and hosting layers with risk-ranked remediation.',
                'icon' => 'fa-solid fa-user-secret',
                'tags' => ['OWASP Testing', 'API Security', 'Auth Review', 'Header Hardening'],
            ],
            [
                'slug' => 'seo',
                'title' => 'SEO & Rankings',
                'summary' => 'Technical search architecture for high-intent organic demand, structured content and location-aware authority signals.',
                'icon' => 'fa-solid fa-arrow-trend-up',
                'tags' => ['Technical SEO', 'Entity Architecture', 'Local Intent', 'Search-Led Content'],
            ],
            [
                'slug' => 'pay-per-click-advertising',
                'title' => 'Pay Per Click Advertising',
                'summary' => 'Paid search systems where keyword intent, landing-page speed and conversion telemetry protect every pound, euro and dollar.',
                'icon' => 'fa-solid fa-bullseye',
                'tags' => ['Google Ads Strategy', 'Landing Page Match', 'Conversion Telemetry', 'CPL Control'],
            ],
            [
                'slug' => 'performance-optimization',
                'title' => 'Performance Optimization',
                'summary' => 'Core Web Vitals engineering across cache strategy, payload control, query efficiency and interaction latency.',
                'icon' => 'fa-solid fa-gauge',
                'tags' => ['LCP / INP / CLS', 'Varnish Strategy', 'Asset Budgets', 'Query Performance'],
            ],
            [
                'slug' => 'maintenance-and-support',
                'title' => 'Maintenance & Support',
                'summary' => 'Written-first technical support with monitored updates, recoverable backups and calm release governance.',
                'icon' => 'fa-solid fa-life-ring',
                'tags' => ['Release Governance', 'Backup Assurance', 'Uptime Monitoring', 'Monthly Technical Notes'],
            ],
            [
                'slug' => 'plugin-development',
                'title' => 'Plugin Development',
                'summary' => 'Reusable CMS modules and platform extensions built around clean settings, scoped permissions and maintainable logic.',
                'icon' => 'fa-solid fa-puzzle-piece',
                'tags' => ['Modular Extensions', 'WordPress Logic', 'Squarespace Blocks', 'Admin Controls'],
            ],
            [
                'slug' => 'wordpress-development',
                'title' => 'WordPress Development',
                'summary' => 'WordPress systems with lean plugin stacks, custom theme logic, WooCommerce readiness and hardened editorial control.',
                'icon' => 'fa-brands fa-wordpress',
                'tags' => ['Custom Themes', 'WooCommerce Architecture', 'Plugin Governance', 'Security Hardening'],
            ],
            [
                'slug' => 'squarespace-development',
                'title' => 'Squarespace Development',
                'summary' => 'Premium Squarespace builds with custom UI blocks, commerce clarity and code-level polish beyond template limits.',
                'icon' => 'fa-solid fa-link',
                'tags' => ['Template Systems', 'Custom UI Blocks', 'Commerce UX', 'Code Injection'],
            ],
            [
                'slug' => 'joomla-development',
                'title' => 'Joomla Development',
                'summary' => 'Joomla modernization for content-heavy portals that need cleaner components, safer access and migration discipline.',
                'icon' => 'fa-brands fa-joomla',
                'tags' => ['Component Strategy', 'Template Modernization', 'Security Cleanup', 'Migration Control'],
            ],
            [
                'slug' => 'web-design-ireland',
                'title' => 'Web Design Ireland',
                'summary' => 'Irish-market interface systems for Dublin-led service brands that need local authority, fast mobile UX and stronger enquiry quality.',
                'icon' => 'fa-solid fa-location-dot',
                'tags' => ['Ireland UX Strategy', 'Dublin Search Intent', 'Local Authority', 'Lead-Gen Design'],
            ],
            [
                'slug' => 'website-development-ireland',
                'title' => 'Website Development Ireland',
                'summary' => 'Cloudways-ready PHP builds for Irish B2B, hospitality and logistics brands competing on speed, trust and regional search demand.',
                'icon' => 'fa-solid fa-code-branch',
                'tags' => ['Ireland PHP Delivery', 'Dublin CMS Control', 'Core Web Vitals', 'Regional SEO'],
            ],
            [
                'slug' => 'web-design-dublin',
                'title' => 'Web Design Dublin',
                'summary' => 'Dublin-focused design systems for high-trust firms that need boardroom-grade polish and mobile-first conversion paths.',
                'icon' => 'fa-solid fa-city',
                'tags' => ['Dublin UX', 'Local SERP Signals', 'B2B Trust Design', 'Mobile Conversion'],
            ],
            [
                'slug' => 'ecommerce-website-development-ireland',
                'title' => 'Ecommerce Website Development Ireland',
                'summary' => 'Irish ecommerce platforms shaped around product discovery, VAT clarity, fulfilment confidence and checkout-speed discipline.',
                'icon' => 'fa-solid fa-cart-shopping',
                'tags' => ['Irish Ecommerce', 'VAT / Shipping Logic', 'Checkout Confidence', 'Store Performance'],
            ],
            [
                'slug' => 'web-development-uk',
                'title' => 'Web Development UK',
                'summary' => 'UK web engineering for B2B operators that need decoupled CMS layers, resilient integrations and measurable lead systems.',
                'icon' => 'fa-solid fa-laptop-code',
                'tags' => ['UK PHP Delivery', 'Custom CMS', 'Integration Security', 'Lead Infrastructure'],
            ],
            [
                'slug' => 'web-design-uk',
                'title' => 'Web Design UK',
                'summary' => 'UK-market design architecture for service firms balancing sharper positioning, accessibility, speed and high-intent enquiry capture.',
                'icon' => 'fa-solid fa-pen-ruler',
                'tags' => ['UK UX Strategy', 'Accessibility Signals', 'Conversion Design', 'SERP-Ready Content'],
            ],
            [
                'slug' => 'website-development-usa',
                'title' => 'Website Development USA',
                'summary' => 'Remote PHP and Laravel delivery for US startups and service brands that need fast releases, integrations and written support.',
                'icon' => 'fa-solid fa-earth-americas',
                'tags' => ['USA Remote Delivery', 'Startup Web Systems', 'Custom CMS', 'AI-Ready Workflows'],
            ],
            [
                'slug' => 'web-design-usa',
                'title' => 'Web Design USA',
                'summary' => 'US-facing UI systems for SaaS, consultants and service brands that need enterprise trust and fast decision clarity.',
                'icon' => 'fa-solid fa-flag-usa',
                'tags' => ['US SaaS UX', 'Enterprise Trust', 'Lead Velocity', 'Conversion Mapping'],
            ],
            [
                'slug' => 'web-design-europe',
                'title' => 'Web Design Europe',
                'summary' => 'European design systems for multilingual-ready brands where GDPR trust, regional authority and speed sit in the first viewport.',
                'icon' => 'fa-solid fa-earth-europe',
                'tags' => ['European UX', 'GDPR Trust', 'Multilingual Structure', 'Regional SERP Fit'],
            ],
            [
                'slug' => 'web-development-europe',
                'title' => 'Web Development Europe',
                'summary' => 'European PHP delivery for platforms, B2B websites and API-led systems that need compliance-aware, performant foundations.',
                'icon' => 'fa-solid fa-server',
                'tags' => ['EU Platform Delivery', 'API Architecture', 'Secure CMS', 'Performance Governance'],
            ],
        ];

        return array_map(function (array $service): array {
            $service['visuals'] = $this->serviceVisuals($service['slug']);
            return $service;
        }, $services);
    }

    public function serviceBySlug(string $slug): ?array
    {
        $details = $this->serviceDetails();
        foreach ($this->services() as $service) {
            if ($service['slug'] === $slug) {
                $service = $service + ($details[$slug] ?? $details['default']);
                $service['long_form'] = $this->serviceLongFormSections($service);
                $service['flow_console'] = $this->serviceFlowConsole($service);
                $service['data_panels'] = $this->serviceDataPanels($service);
                $service['seo_essentials'] = $this->serviceSeoEssentials($service);
                $service['decision_panels'] = $this->serviceDecisionPanels($service);
                $service['proof_points'] = $this->serviceProofPoints($service);
                $service['market_notes'] = $this->serviceMarketNotes($service);
                $service['seo_description'] = $this->serviceMetaDescription($service);
                $service = $service + $this->serviceConsoleCopy($service);
                return $service;
            }
        }

        return null;
    }

    private function serviceVisuals(string $slug): array
    {
        $map = [
            'website-development' => [
                ['name' => 'PHP', 'image' => 'tech/php.png'],
                ['name' => 'MySQL', 'image' => 'tech/mysql.png'],
                ['name' => 'Laravel', 'image' => 'tech/laravel.png'],
                ['name' => 'Cloudflare', 'image' => 'tech/cloudflare.png'],
            ],
            'web-design' => [
                ['name' => 'Figma', 'image' => 'tech/figma.svg'],
                ['name' => 'HTML5', 'image' => 'tech/html5.png'],
                ['name' => 'CSS3', 'image' => 'tech/css3.png'],
                ['name' => 'JavaScript', 'image' => 'tech/javascript.png'],
            ],
            'app-development' => [
                ['name' => 'Android', 'image' => 'tech/android.svg'],
                ['name' => 'iOS', 'image' => 'tech/appstore.svg'],
                ['name' => 'JavaScript', 'image' => 'tech/javascript.png'],
                ['name' => 'API', 'icon' => 'fa-solid fa-plug-circle-bolt'],
            ],
            'app-publishing' => [
                ['name' => 'Google Play', 'icon' => 'fa-brands fa-google-play'],
                ['name' => 'App Store', 'image' => 'tech/appstore.svg'],
                ['name' => 'Android', 'image' => 'tech/android.svg'],
                ['name' => 'Privacy', 'icon' => 'fa-solid fa-shield-halved'],
            ],
            'api-integration' => [
                ['name' => 'Stripe', 'image' => 'tech/stripe.png'],
                ['name' => 'Zapier', 'image' => 'tech/zapier.png'],
                ['name' => 'MySQL', 'image' => 'tech/mysql.png'],
                ['name' => 'REST API', 'icon' => 'fa-solid fa-route'],
            ],
            'ai-integration-services' => [
                ['name' => 'OpenAI', 'image' => 'tech/openai.svg'],
                ['name' => 'Zapier', 'image' => 'tech/zapier.png'],
                ['name' => 'CRM', 'icon' => 'fa-solid fa-user-gear'],
                ['name' => 'API', 'icon' => 'fa-solid fa-plug-circle-bolt'],
            ],
            'ai-workflow-automation' => [
                ['name' => 'OpenAI', 'image' => 'tech/openai.svg'],
                ['name' => 'Zapier', 'image' => 'tech/zapier.png'],
                ['name' => 'Workflow', 'icon' => 'fa-solid fa-diagram-project'],
                ['name' => 'Reports', 'icon' => 'fa-solid fa-chart-line'],
            ],
            'ai-chatbot-development' => [
                ['name' => 'OpenAI', 'image' => 'tech/openai.svg'],
                ['name' => 'Chatbot', 'icon' => 'fa-solid fa-comments'],
                ['name' => 'Leads', 'icon' => 'fa-solid fa-user-plus'],
                ['name' => 'Support', 'icon' => 'fa-solid fa-headset'],
            ],
            'ai-crm-automation' => [
                ['name' => 'OpenAI', 'image' => 'tech/openai.svg'],
                ['name' => 'CRM', 'icon' => 'fa-solid fa-user-gear'],
                ['name' => 'Zapier', 'image' => 'tech/zapier.png'],
                ['name' => 'Lead Scoring', 'icon' => 'fa-solid fa-ranking-star'],
            ],
            'penetration-testing' => [
                ['name' => 'Kali Linux', 'image' => 'tech/kalilinux.png'],
                ['name' => 'FreeBSD', 'image' => 'tech/freebsd.png'],
                ['name' => 'OWASP', 'image' => 'tech/owasp.svg'],
                ['name' => 'Parrot OS', 'image' => 'tech/parrotos.svg'],
            ],
            'seo' => [
                ['name' => 'Google', 'image' => 'tech/google.svg'],
                ['name' => 'Search Console', 'image' => 'tech/search-console.svg'],
                ['name' => 'Schema', 'icon' => 'fa-solid fa-code-branch'],
                ['name' => 'Analytics', 'icon' => 'fa-solid fa-chart-line'],
            ],
            'pay-per-click-advertising' => [
                ['name' => 'Google Ads', 'image' => 'tech/googleads.svg'],
                ['name' => 'Search', 'image' => 'tech/google.svg'],
                ['name' => 'Landing Pages', 'icon' => 'fa-solid fa-window-maximize'],
                ['name' => 'ROI', 'icon' => 'fa-solid fa-arrow-trend-up'],
            ],
            'performance-optimization' => [
                ['name' => 'Lighthouse', 'image' => 'tech/lighthouse.svg'],
                ['name' => 'Cloudflare', 'image' => 'tech/cloudflare.png'],
                ['name' => 'NGINX', 'image' => 'tech/nginx.png'],
                ['name' => 'WebP', 'icon' => 'fa-solid fa-gauge-high'],
            ],
            'maintenance-and-support' => [
                ['name' => 'Cloudflare', 'image' => 'tech/cloudflare.png'],
                ['name' => 'Git', 'image' => 'tech/git.png'],
                ['name' => 'Backups', 'icon' => 'fa-solid fa-database'],
                ['name' => 'Tickets', 'icon' => 'fa-solid fa-ticket'],
            ],
            'plugin-development' => [
                ['name' => 'WordPress', 'image' => 'tech/wordpress.png'],
                ['name' => 'PHP', 'image' => 'tech/php.png'],
                ['name' => 'JavaScript', 'image' => 'tech/javascript.png'],
                ['name' => 'Git', 'image' => 'tech/git.png'],
            ],
            'wordpress-development' => [
                ['name' => 'WordPress', 'image' => 'tech/wordpress.png'],
                ['name' => 'PHP', 'image' => 'tech/php.png'],
                ['name' => 'MySQL', 'image' => 'tech/mysql.png'],
                ['name' => 'WooCommerce', 'icon' => 'fa-solid fa-cart-shopping'],
            ],
            'squarespace-development' => [
                ['name' => 'Squarespace', 'image' => 'tech/squarespace.png'],
                ['name' => 'CSS3', 'image' => 'tech/css3.png'],
                ['name' => 'JavaScript', 'image' => 'tech/javascript.png'],
                ['name' => 'Commerce', 'icon' => 'fa-solid fa-cart-shopping'],
            ],
            'joomla-development' => [
                ['name' => 'Joomla', 'image' => 'tech/joomla.png'],
                ['name' => 'PHP', 'image' => 'tech/php.png'],
                ['name' => 'MySQL', 'image' => 'tech/mysql.png'],
                ['name' => 'Security', 'icon' => 'fa-solid fa-shield-halved'],
            ],
        ];

        if (isset($map[$slug])) {
            return $map[$slug];
        }

        if (str_contains($slug, 'ecommerce')) {
            return [
                ['name' => 'WooCommerce', 'icon' => 'fa-solid fa-cart-shopping'],
                ['name' => 'Stripe', 'image' => 'tech/stripe.png'],
                ['name' => 'WordPress', 'image' => 'tech/wordpress.png'],
                ['name' => 'Performance', 'image' => 'tech/lighthouse.svg'],
            ];
        }

        if (str_contains($slug, 'web-design')) {
            return [
                ['name' => 'Figma', 'image' => 'tech/figma.svg'],
                ['name' => 'HTML5', 'image' => 'tech/html5.png'],
                ['name' => 'CSS3', 'image' => 'tech/css3.png'],
                ['name' => 'SEO', 'image' => 'tech/search-console.svg'],
            ];
        }

        if (str_contains($slug, 'website') || str_contains($slug, 'web-development')) {
            return $map['website-development'];
        }

        return [
            ['name' => 'PHP', 'image' => 'tech/php.png'],
            ['name' => 'MySQL', 'image' => 'tech/mysql.png'],
            ['name' => 'Cloudflare', 'image' => 'tech/cloudflare.png'],
            ['name' => 'SEO', 'image' => 'tech/search-console.svg'],
        ];
    }

    private function serviceLongFormSections(array $service): array
    {
        $title = $service['title'];
        $summary = $service['summary'];
        $intro = $service['intro'] ?? $summary;
        $ideal = implode(', ', array_slice($service['ideal_for'] ?? [], 0, 4));
        $deliverables = implode(', ', array_slice($service['deliverables'] ?? [], 0, 5));
        $benefits = implode(' ', array_slice($service['benefits'] ?? [], 0, 3));
        $tags = implode(', ', array_slice($service['tags'] ?? [], 0, 5));
        $primaryKeyword = $service['tags'][0] ?? $title;
        $slug = (string) ($service['slug'] ?? '');
        $market = 'Ireland, the UK, the USA and Europe';
        if (str_contains($slug, 'dublin')) {
            $market = 'Dublin and the wider Irish market';
        } elseif (str_contains($slug, 'ireland')) {
            $market = 'Ireland, including Dublin-led commercial searches';
        } elseif (str_contains($slug, 'uk')) {
            $market = 'the United Kingdom, where credibility, accessibility and lead quality shape buying decisions';
        } elseif (str_contains($slug, 'usa')) {
            $market = 'the United States, where speed, proof and conversion clarity decide whether a visitor becomes a lead';
        } elseif (str_contains($slug, 'europe')) {
            $market = 'Europe, with GDPR expectations, multilingual readiness and regional authority signals built in';
        }

        return [
            [
                'heading' => 'Commercial Architecture',
                'paragraphs' => [
                    $intro . ' The page is treated as a revenue surface, not a brochure. Each section has a job: explain the offer, reduce buyer uncertainty, prove technical competence and move serious visitors toward a written brief, WhatsApp message or support ticket.',
                    $summary . ' Search intent and buyer intent are mapped together. The content gives search engines focused topical depth while giving decision-makers enough operational detail to understand scope, risk, timelines and the commercial reason to enquire.',
                ],
            ],
            [
                'heading' => 'Audience And Market Fit',
                'paragraphs' => [
                    'The work is shaped for ' . $ideal . '. These clients are usually fixing a measurable constraint: underqualified enquiries, dated positioning, slow mobile UX, fragmented operations, weak regional search coverage or a digital system that no longer matches the business behind it.',
                    'For ' . $market . ', the strategy balances local authority signals with global-quality execution. Service wording, proof placement, technical SEO, Core Web Vitals and lead paths are planned around how real buyers compare vendors before they make contact.',
                ],
            ],
            [
                'heading' => 'Delivery Standard',
                'paragraphs' => [
                    'Core deliverables include ' . $deliverables . '. The exact scope changes by project, but the baseline stays firm: conversion-mapped layouts, lean code, secure forms, fast templates, editable content, clean handoff notes and launch assets that do not leave the business dependent on guesswork.',
                    'Backends are kept practical and decoupled for DigitalOcean, Cloudways, PHP/MySQL hosting and future maintenance. Frontend systems are built for sub-second perceived response, stable mobile states and interface clarity that reduces cognitive friction before the user reaches a call to action.',
                ],
            ],
            [
                'heading' => 'Search And Conversion Layer',
                'paragraphs' => [
                    'The SEO layer has one clear primary theme led by ' . $primaryKeyword . ', supported by related signals such as ' . $tags . '. Headings are written for decision makers, internal links connect adjacent capabilities and FAQ content gives search engines structured answers without repeating the page title in every block.',
                    'Conversion is built into the page architecture. Visitors can compare the service, inspect deliverables, use relevant tools, request support or send a project brief without being pushed into a call funnel. That written-first path is deliberate for B2B buyers who want context before commitment.',
                ],
            ],
            [
                'heading' => 'Performance, Security And Trust',
                'paragraphs' => [
                    'Performance and security are not afterthoughts. Pages are planned around compressed media, cache-aware delivery, controlled JavaScript, resilient form handling, CSRF protection, upload rules, session hygiene and Core Web Vitals that can survive real hosting conditions.',
                    'After launch, the system should keep earning its place. Organic visibility, enquiry quality, form completions, crawl health, page speed and support patterns become the improvement loop. That is why the build includes documented decisions, maintainable code and a support route from day one.',
                ],
            ],
            [
                'heading' => 'Integrated Growth System',
                'paragraphs' => [
                    'Design, PHP 8/Laravel development, ecommerce, app publishing, API integrations, PPC, SEO, security and AI workflow automation are treated as connected disciplines. SEO fails when the platform is slow. Ads waste budget when landing pages are vague. AI assistants underperform when the service content is thin. The system has to work as one commercial layer.',
                    $benefits . ' The next recommendation is based on the current website, target jurisdiction, buyer profile and operating constraint. That may be a focused page rebuild, a custom PHP module, an AI workflow, a search campaign, a security review or a complete platform upgrade.',
                ],
            ],
            [
                'heading' => 'Measurement And Next Step',
                'paragraphs' => [
                    'Success is measured by the signals that matter: qualified enquiries, organic impressions, ranking movement, WhatsApp clicks, form completion rate, support volume, page speed, crawl health and lead quality. The goal is not traffic theatre; it is better commercial momentum.',
                    'Send the current website, the offer you want to push, the jurisdictions that matter and the business outcome you want. From there Crest Web Media can define the smallest serious move that improves search visibility, speed, trust, automation or conversion quality first.',
                ],
            ],
        ];
    }

    private function serviceFlowConsole(array $service): array
    {
        $steps = $service['process'] ?? ['Discovery', 'Roadmap', 'Design', 'Build', 'Test', 'Launch', 'Optimize'];
        $deliverables = $service['deliverables'] ?? ['Project brief', 'Conversion system', 'Technical setup', 'Launch checklist'];
        $tags = $service['tags'] ?? ['Strategy', 'SEO', 'Performance', 'Security'];
        $icons = ['fa-magnifying-glass-chart', 'fa-route', 'fa-wand-magic-sparkles', 'fa-code', 'fa-shield-halved', 'fa-rocket', 'fa-chart-line'];
        $signals = [
            'Intent, audience, competitor gaps and conversion friction are mapped before production starts.',
            'Page architecture, content depth, integrations and priority actions are converted into a build roadmap.',
            'Interface states, lead paths, trust blocks and mobile patterns are shaped around real customer decisions.',
            'The system is built with clean PHP/MySQL foundations, optimized assets and reusable sections.',
            'Performance, accessibility, form behaviour, security controls and responsive layouts are checked before launch.',
            'Deployment, tracking, redirects, metadata, schema and handoff notes are reviewed as one release package.',
            'Search data, leads, speed, support tickets and user behaviour are reviewed for the next improvement loop.',
        ];
        $metrics = ['Intent map', 'Architecture lock', 'UX system', 'Build telemetry', 'QA gates', 'Launch signal', 'Growth loop'];

        return array_map(function (string $step, int $index) use ($deliverables, $tags, $icons, $signals, $metrics): array {
            return [
                'phase' => $step,
                'icon' => $icons[$index % count($icons)],
                'signal' => $signals[$index % count($signals)],
                'output' => $deliverables[$index % count($deliverables)],
                'metric' => $metrics[$index % count($metrics)],
                'tag' => $tags[$index % count($tags)],
            ];
        }, array_values($steps), array_keys(array_values($steps)));
    }

    private function serviceDataPanels(array $service): array
    {
        $title = $service['title'];
        $tags = implode(' / ', array_slice($service['tags'] ?? [], 0, 4));
        $ideal = implode(', ', array_slice($service['ideal_for'] ?? [], 0, 3));
        $deliverables = implode(', ', array_slice($service['deliverables'] ?? [], 0, 4));

        return [
            [
                'label' => 'Intent Engine',
                'value' => 'Search + buyer intent',
                'body' => 'Maps content around the commercial questions, objections and decision triggers that separate research traffic from qualified enquiries.',
                'icon' => 'fa-magnifying-glass-chart',
            ],
            [
                'label' => 'Conversion Layer',
                'value' => 'Lead capture paths',
                'body' => 'Builds written-first actions around WhatsApp, forms, tickets, tools and project briefs so visitors can move without a forced call.',
                'icon' => 'fa-bullseye',
            ],
            [
                'label' => 'Stack Signal',
                'value' => $tags,
                'body' => 'Surfaces the relevant platform, performance and integration signals so buyers can see the technical fit quickly.',
                'icon' => 'fa-layer-group',
            ],
            [
                'label' => 'Audience Fit',
                'value' => $ideal,
                'body' => 'Keeps copy, layout and proof aligned with the actual buyers this service is meant to attract.',
                'icon' => 'fa-user-shield',
            ],
            [
                'label' => 'Delivery Output',
                'value' => $deliverables,
                'body' => 'Turns strategy into visible outputs, measurable checks and handoff notes that can be maintained after launch.',
                'icon' => 'fa-clipboard-list',
            ],
            [
                'label' => 'Growth Telemetry',
                'value' => 'SEO, speed, leads, support',
                'body' => 'Tracks the signals that matter after launch: rankings, enquiries, page speed, crawl health and support requests.',
                'icon' => 'fa-chart-line',
            ],
        ];
    }

    private function serviceSeoEssentials(array $service): array
    {
        $copy = $this->serviceCopyProfile($service);

        return [
            [
                'label' => $copy['cards'][0]['label'],
                'value' => $copy['cards'][0]['value'],
                'body' => $copy['cards'][0]['body'],
                'icon' => $copy['cards'][0]['icon'],
            ],
            [
                'label' => $copy['cards'][1]['label'],
                'value' => $copy['cards'][1]['value'],
                'body' => $copy['cards'][1]['body'],
                'icon' => $copy['cards'][1]['icon'],
            ],
            [
                'label' => $copy['cards'][2]['label'],
                'value' => $copy['cards'][2]['value'],
                'body' => $copy['cards'][2]['body'],
                'icon' => $copy['cards'][2]['icon'],
            ],
            [
                'label' => $copy['cards'][3]['label'],
                'value' => $copy['cards'][3]['value'],
                'body' => $copy['cards'][3]['body'],
                'icon' => $copy['cards'][3]['icon'],
            ],
        ];
    }

    private function serviceConsoleCopy(array $service): array
    {
        $copy = $this->serviceCopyProfile($service);

        return [
            'console_kicker' => $copy['kicker'],
            'console_title' => $copy['title'],
            'console_intro' => $copy['intro'],
            'orbit_note' => $copy['orbit'],
        ];
    }

    private function serviceCopyProfile(array $service): array
    {
        $slug = (string) ($service['slug'] ?? '');
        $title = (string) ($service['title'] ?? 'Digital Service');
        $tags = array_values($service['tags'] ?? []);
        $summary = (string) ($service['summary'] ?? '');

        $default = [
            'kicker' => 'Service Intelligence',
            'title' => 'Digital Delivery Mapped To Buyer Intent And Operating Reality',
            'intro' => 'The page frames the commercial problem, technical path, handoff standard and next step without burying buyers in repeated keywords.',
            'orbit' => 'A focused delivery plan for authority, speed, trust and qualified written enquiries.',
            'cards' => [
                ['label' => 'Commercial Constraint', 'value' => 'Lead quality', 'body' => $summary !== '' ? $summary : 'A practical service for businesses that need stronger digital systems and clearer customer action paths.', 'icon' => 'fa-bullseye'],
                ['label' => 'Technical Direction', 'value' => $tags[0] ?? 'Strategy', 'body' => 'Scope is defined around the outcome first, then translated into the right pages, modules, integrations and support flow.', 'icon' => 'fa-route'],
                ['label' => 'Engineering Standard', 'value' => $tags[1] ?? 'Performance', 'body' => 'Speed, mobile behaviour, security, accessibility and maintainability are treated as delivery requirements.', 'icon' => 'fa-shield-halved'],
                ['label' => 'Conversion Path', 'value' => 'Brief, WhatsApp, ticket', 'body' => 'Written enquiry routes let serious buyers send context without being forced into a meeting funnel.', 'icon' => 'fa-paper-plane'],
            ],
        ];

        $profiles = [
            'api-integration' => [
                'kicker' => 'Integration Blueprint',
                'title' => 'Operational APIs With Clean Data Movement And Failure Control',
                'intro' => 'Integration work should remove manual admin, not create another fragile dependency. Payment systems, CRMs, booking engines, forms and automations are mapped around credentials, retries, logging and recovery paths.',
                'orbit' => 'A secure integration plan for payments, CRMs, webhooks, booking flows and automation handoff.',
                'cards' => [
                    ['label' => 'Operational Friction', 'value' => 'Disconnected tools', 'body' => 'Leads, payments, bookings and customer records often sit in separate systems. The integration plan connects the tools already in use so data moves with less manual copying.', 'icon' => 'fa-plug-circle-bolt'],
                    ['label' => 'Integration Scope', 'value' => 'REST APIs + webhooks', 'body' => 'Requests, credentials, payloads, retries and webhook events are mapped before code is written, so the build has clear inputs, outputs and failure handling.', 'icon' => 'fa-route'],
                    ['label' => 'Security Standard', 'value' => 'Validated requests', 'body' => 'API keys, webhook signatures, server-side validation, least-privilege access and logging are planned so integrations stay reliable after launch.', 'icon' => 'fa-shield-halved'],
                    ['label' => 'Commercial Result', 'value' => 'Cleaner operations', 'body' => 'The goal is fewer missed leads, faster fulfilment, more accurate CRM data and less admin time spent moving information between platforms.', 'icon' => 'fa-chart-line'],
                ],
            ],
            'pay-per-click-advertising' => [
                'kicker' => 'Paid Search System',
                'title' => 'Paid Search Systems Built To Protect Spend And Improve Lead Quality',
                'intro' => 'PPC performance depends on the full post-click system: keyword intent, offer clarity, landing-page speed, conversion tracking and follow-up discipline.',
                'orbit' => 'A paid-search plan around keywords, landing pages, tracking, budget control and lead quality.',
                'cards' => [
                    ['label' => 'Campaign Goal', 'value' => 'Qualified enquiries', 'body' => 'Campaigns are planned around the enquiries you actually want, not vanity traffic. That means choosing search intent carefully and matching ads to focused landing pages.', 'icon' => 'fa-bullseye'],
                    ['label' => 'Tracking Layer', 'value' => 'Calls, forms, WhatsApp', 'body' => 'Conversion actions are defined before launch so spend can be judged by useful leads, not just clicks and impressions.', 'icon' => 'fa-chart-line'],
                    ['label' => 'Landing Page Fit', 'value' => 'Message match', 'body' => 'The visitor should see the same promise from keyword to ad to page, with proof, speed and a clear next step.', 'icon' => 'fa-window-maximize'],
                    ['label' => 'Optimization Loop', 'value' => 'Search terms + ROI', 'body' => 'Search terms, cost per lead, conversion rate and lead quality guide weekly improvements instead of guesswork.', 'icon' => 'fa-arrow-trend-up'],
                ],
            ],
            'penetration-testing' => [
                'kicker' => 'Security Review',
                'title' => 'Security Reviews With Evidence, Priority And Remediation Context',
                'intro' => 'Security testing should produce clear risk, proof, priority and remediation steps. Scope, evidence handling and production safety are defined before testing begins.',
                'orbit' => 'A responsible testing plan for web apps, APIs, forms, auth flows and admin areas.',
                'cards' => [
                    ['label' => 'Testing Scope', 'value' => 'OWASP + business logic', 'body' => 'The review covers common web risks, authentication, headers, TLS, forms, exposed data and workflow-specific weaknesses.', 'icon' => 'fa-user-secret'],
                    ['label' => 'Safe Method', 'value' => 'Responsible testing', 'body' => 'Testing is scoped to avoid production disruption, with clear rules, evidence and no reckless automated noise.', 'icon' => 'fa-shield-halved'],
                    ['label' => 'Report Output', 'value' => 'Risk-ranked fixes', 'body' => 'Findings are written so a business owner can understand impact and a developer can fix the issue without decoding vague scanner text.', 'icon' => 'fa-clipboard-list'],
                    ['label' => 'Aftercare', 'value' => 'Remediation support', 'body' => 'The important part is reducing risk after the report, so fixes, retests and secure development guidance are part of the conversation.', 'icon' => 'fa-screwdriver-wrench'],
                ],
            ],
            'seo' => [
                'kicker' => 'Search Growth Plan',
                'title' => 'Search Architecture Built Around Intent, Authority And Qualified Demand',
                'intro' => 'SEO connects crawlability, content depth, local intent, internal links and conversion paths. Rankings matter because they create qualified commercial opportunities.',
                'orbit' => 'A search plan for technical SEO, content structure, schema, internal links and reporting.',
                'cards' => [
                    ['label' => 'Search Intent', 'value' => 'Service + location demand', 'body' => 'Keywords are mapped to the actual questions and commercial searches buyers use before choosing a provider.', 'icon' => 'fa-magnifying-glass-chart'],
                    ['label' => 'Technical Base', 'value' => 'Crawlable and fast', 'body' => 'Metadata, schema, headings, internal links, Core Web Vitals and indexability are checked so content has a fair chance to rank.', 'icon' => 'fa-code-branch'],
                    ['label' => 'Content Depth', 'value' => 'Useful answers', 'body' => 'Pages are written to answer real buyer questions with enough detail for users, Google and AI search systems to understand the offer.', 'icon' => 'fa-newspaper'],
                    ['label' => 'Lead Path', 'value' => 'Rankings to enquiries', 'body' => 'SEO pages include proof, service links, tools and written CTAs so visibility turns into useful business conversations.', 'icon' => 'fa-paper-plane'],
                ],
            ],
            'website-development' => [
                'kicker' => 'Website Build Plan',
                'title' => 'Revenue Websites Built On Lean PHP, Search Structure And Clear UX',
                'intro' => 'A serious website should explain the offer, load quickly, earn trust, capture enquiries and stay maintainable after launch.',
                'orbit' => 'A PHP/MySQL website plan focused on speed, security, CMS control and qualified enquiries.',
                'cards' => [
                    ['label' => 'Commercial Goal', 'value' => 'More useful leads', 'body' => 'The build starts with the action the website must create: project briefs, bookings, sales, quote requests or support tickets.', 'icon' => 'fa-bullseye'],
                    ['label' => 'Technical Stack', 'value' => 'PHP + MySQL', 'body' => 'The backend is kept lean, secure and hosting-friendly so it works well on DigitalOcean, Cloudways and standard PHP/MySQL hosting.', 'icon' => 'fa-database'],
                    ['label' => 'Front-End Standard', 'value' => 'Fast and responsive', 'body' => 'Layouts are built mobile-first with compressed assets, stable components and clean performance budgets.', 'icon' => 'fa-mobile-screen-button'],
                    ['label' => 'Handoff', 'value' => 'CMS + support', 'body' => 'The result should be editable, documented and supported, not a mystery build only one person can touch.', 'icon' => 'fa-headset'],
                ],
            ],
            'web-design' => [
                'kicker' => 'Interface Strategy',
                'title' => 'Interface Systems That Reduce Friction And Sharpen Buyer Confidence',
                'intro' => 'Modern web design is hierarchy, clarity, proof, mobile behaviour and the confidence a visitor feels before sending an enquiry.',
                'orbit' => 'A design plan for UI hierarchy, proof blocks, service clarity, mobile polish and conversion paths.',
                'cards' => [
                    ['label' => 'Visual Positioning', 'value' => 'Premium first impression', 'body' => 'The design makes the brand feel credible before the visitor reads every detail, with a clean hierarchy and modern visual system.', 'icon' => 'fa-palette'],
                    ['label' => 'UX Structure', 'value' => 'Clear decisions', 'body' => 'Sections are ordered around how people compare services: problem, offer, proof, process, risk and next action.', 'icon' => 'fa-route'],
                    ['label' => 'Mobile Feel', 'value' => 'App-like polish', 'body' => 'Buttons, cards, menus and forms are sized for real thumbs, not just desktop screenshots.', 'icon' => 'fa-mobile-screen-button'],
                    ['label' => 'Conversion Detail', 'value' => 'Trust near CTAs', 'body' => 'Proof, FAQs and reassurance sit close to action points so visitors do not have to hunt for confidence.', 'icon' => 'fa-circle-check'],
                ],
            ],
            'app-development' => [
                'kicker' => 'Application Architecture',
                'title' => 'Application Platforms Built Around Workflows, Roles And Reliable Data',
                'intro' => 'App development needs more than screens. It needs user roles, permissions, data structure, dashboards, APIs, testing and release planning.',
                'orbit' => 'An app delivery plan for workflows, dashboards, APIs, user roles and scalable releases.',
                'cards' => [
                    ['label' => 'Workflow Design', 'value' => 'Real user tasks', 'body' => 'The app is shaped around the jobs users need to complete, not a pile of disconnected screens.', 'icon' => 'fa-diagram-project'],
                    ['label' => 'Backend Logic', 'value' => 'Data and permissions', 'body' => 'Roles, validation, records, notifications and admin workflows are planned before build decisions harden.', 'icon' => 'fa-database'],
                    ['label' => 'Interface Quality', 'value' => 'Fast and focused', 'body' => 'The UI should feel direct, stable and usable on mobile and desktop, especially for repeated actions.', 'icon' => 'fa-mobile-screen-button'],
                    ['label' => 'Launch Path', 'value' => 'Test, deploy, improve', 'body' => 'A useful app release includes QA, feedback loops, performance checks and a plan for the next version.', 'icon' => 'fa-rocket'],
                ],
            ],
            'performance-optimization' => [
                'kicker' => 'Speed Diagnostics',
                'title' => 'Performance Engineering For Faster Paint, Cleaner Interaction And Better Leads',
                'intro' => 'Optimization should reduce friction users can feel: slow loading, layout shift, delayed taps and heavy assets that waste mobile attention.',
                'orbit' => 'A Core Web Vitals plan for images, caching, JavaScript, CSS, server response and mobile speed.',
                'cards' => [
                    ['label' => 'Core Web Vitals', 'value' => 'LCP, INP, CLS', 'body' => 'The page is reviewed around the metrics Google and users both care about: loading speed, interaction delay and layout stability.', 'icon' => 'fa-gauge-high'],
                    ['label' => 'Asset Weight', 'value' => 'Images and CSS', 'body' => 'Oversized images, unused styles and heavy scripts are reduced so mobile users get the page sooner.', 'icon' => 'fa-images'],
                    ['label' => 'Hosting Layer', 'value' => 'Cache-aware delivery', 'body' => 'Cloudways, Varnish, browser cache and static asset versioning are treated as part of the performance system.', 'icon' => 'fa-server'],
                    ['label' => 'Business Impact', 'value' => 'Speed to leads', 'body' => 'The work is measured by better user flow, stronger Core Web Vitals and fewer people leaving before they enquire.', 'icon' => 'fa-chart-line'],
                ],
            ],
        ];

        foreach ($profiles as $key => $profile) {
            if ($slug === $key || str_contains($slug, $key)) {
                return $profile;
            }
        }

        if (str_contains($slug, 'ai')) {
            return [
                'kicker' => 'AI Workflow Plan',
                'title' => 'AI Integration That Solves Real Workflow Problems',
                'intro' => 'AI should make an existing process faster, clearer or easier to manage. The work focuses on where automation helps and where human approval still belongs.',
                'orbit' => 'An AI implementation plan for forms, CRM updates, support triage, summaries and workflow automation.',
                'cards' => [
                    ['label' => 'Use Case', 'value' => 'Practical automation', 'body' => 'The project starts by choosing useful AI jobs: lead qualification, support routing, summaries, content drafts or internal reporting.', 'icon' => 'fa-brain'],
                    ['label' => 'Data Flow', 'value' => 'Inputs and guardrails', 'body' => 'Inputs, outputs, prompts, limits, logs and approval points are mapped so AI does not become a black box.', 'icon' => 'fa-route'],
                    ['label' => 'Integration', 'value' => 'Website + CRM + tools', 'body' => 'AI connects to the systems that matter: forms, inboxes, CRMs, spreadsheets, dashboards and support workflows.', 'icon' => 'fa-plug-circle-bolt'],
                    ['label' => 'Result', 'value' => 'Less manual admin', 'body' => 'The win is faster response, cleaner records, better lead context and less repetitive work for the team.', 'icon' => 'fa-chart-line'],
                ],
            ];
        }

        if (str_contains($slug, 'wordpress') || str_contains($slug, 'plugin')) {
            return [
                'kicker' => 'WordPress Build Plan',
                'title' => 'WordPress Work That Stays Fast, Secure And Editable',
                'intro' => 'WordPress should give the business control without turning the website into a slow plugin stack. The service focuses on clean structure, security and maintainability.',
                'orbit' => 'A WordPress plan for custom features, plugin control, speed, security and editor-friendly handoff.',
                'cards' => [
                    ['label' => 'Build Scope', 'value' => 'Theme, plugin or workflow', 'body' => 'The work is scoped around the actual problem: custom layouts, plugin features, WooCommerce flow, forms or editor control.', 'icon' => 'fa-wordpress'],
                    ['label' => 'Performance', 'value' => 'Lean plugin stack', 'body' => 'Unnecessary plugins, heavy assets and fragile theme choices are avoided so the site remains fast.', 'icon' => 'fa-gauge-high'],
                    ['label' => 'Security', 'value' => 'Updates and hardening', 'body' => 'Admin access, forms, uploads, plugin updates and backups are handled with practical security controls.', 'icon' => 'fa-shield-halved'],
                    ['label' => 'Handoff', 'value' => 'Editable CMS', 'body' => 'The site should be easy to update without making future developers untangle a mess.', 'icon' => 'fa-floppy-disk'],
                ],
            ];
        }

        if (str_contains($slug, 'web-design') || str_contains($slug, 'website') || str_contains($slug, 'web-development') || str_contains($slug, 'ecommerce')) {
            $profile = $profiles['website-development'];
            if (str_contains($slug, 'web-design')) {
                $profile = $profiles['web-design'];
            }
            if (str_contains($slug, 'ecommerce')) {
                $profile['title'] = 'Ecommerce Websites Built Around Trust, Checkout Confidence And Speed';
                $profile['intro'] = 'Ecommerce pages need product clarity, delivery confidence, payment trust, fast mobile browsing and a checkout path that does not make customers hesitate.';
                $profile['cards'][0] = ['label' => 'Store Goal', 'value' => 'More completed orders', 'body' => 'The build focuses on product discovery, checkout confidence, shipping clarity and trust near payment points.', 'icon' => 'fa-cart-shopping'];
            }
            return $profile;
        }

        return $default;
    }

    private function serviceDecisionPanels(array $service): array
    {
        $title = $service['title'];
        $ideal = implode(', ', array_slice($service['ideal_for'] ?? [], 0, 3));
        $deliverables = implode(', ', array_slice($service['deliverables'] ?? [], 0, 3));
        $benefits = $service['benefits'] ?? [];

        return [
            [
                'step' => '01',
                'title' => 'Clarify the commercial job',
                'body' => 'Before the page is designed, the offer is tightened: who it is for, what problem it solves and what action the visitor should take next.',
                'signal' => $ideal,
            ],
            [
                'step' => '02',
                'title' => 'Build the proof around the buyer',
                'body' => 'The page needs practical details, visible deliverables and trust cues that help a serious visitor feel safe sending an enquiry.',
                'signal' => $deliverables,
            ],
            [
                'step' => '03',
                'title' => 'Connect SEO to conversion',
                'body' => 'Search visibility only matters when the page can turn attention into a lead. Headings, copy, internal links and CTAs are planned together.',
                'signal' => $benefits[0] ?? 'Better search visibility and stronger lead quality.',
            ],
        ];
    }

    private function serviceProofPoints(array $service): array
    {
        return [
            ['value' => 'SEO', 'label' => 'Intent-led headings, metadata, FAQs and internal links'],
            ['value' => 'UX', 'label' => 'Mobile-first sections with clear written enquiry paths'],
            ['value' => 'Speed', 'label' => 'Compressed media, clean CSS and hosting-aware performance'],
            ['value' => 'Trust', 'label' => 'Secure forms, visible deliverables and support handoff'],
        ];
    }

    private function serviceMarketNotes(array $service): array
    {
        $slug = $service['slug'];
        $title = $service['title'];
        $notes = [
            'buyer' => 'Buyers compare clarity, trust, speed, visible proof and how easy it is to send a useful enquiry.',
            'search' => 'Search pages need a clear primary topic, supporting terms, useful answers, internal links and FAQ structure.',
            'measure' => 'The page should be judged by rankings, qualified enquiries, form completion, WhatsApp clicks and page speed.',
        ];

        if (str_contains($slug, 'seo')) {
            $notes = [
                'buyer' => 'SEO buyers want to see technical competence, realistic expectations, local/search intent knowledge and reporting that connects rankings to leads.',
                'search' => 'The page needs crawlable structure, schema, service/location intent, internal links, content depth and headings that answer commercial search queries.',
                'measure' => 'Useful SEO signals include impressions, ranking movement, indexed pages, click-through rate, enquiry quality and technical health.',
            ];
        } elseif (str_contains($slug, 'pay-per-click')) {
            $notes = [
                'buyer' => 'PPC buyers care about wasted spend, landing page quality, conversion tracking and whether ad traffic turns into actual enquiries.',
                'search' => 'The page should connect Google Ads, landing pages, keyword intent, Quality Score, conversion actions and remarketing logic naturally.',
                'measure' => 'Campaign quality is measured through cost per lead, conversion rate, search terms, landing page speed and lead quality.',
            ];
        } elseif (str_contains($slug, 'ai')) {
            $notes = [
                'buyer' => 'AI buyers want practical automation, not hype. They need to know what will be automated, where human approval stays and how data is protected.',
                'search' => 'The page should explain real AI use cases: lead qualification, support replies, CRM updates, summaries, routing and workflow automation.',
                'measure' => 'AI value is measured by faster response time, fewer manual tasks, cleaner CRM data, better lead context and reduced support repetition.',
            ];
        } elseif (str_contains($slug, 'penetration')) {
            $notes = [
                'buyer' => 'Security buyers need plain risk explanations, responsible testing scope, remediation guidance and confidence that the work will not disrupt production.',
                'search' => 'The page should cover OWASP risks, authentication, headers, TLS, DNS, API testing, reporting and secure development practices.',
                'measure' => 'Security improvement is measured by reduced critical findings, patched vulnerabilities, stronger headers, safer forms and cleaner access control.',
            ];
        } elseif (str_contains($slug, 'app')) {
            $notes = [
                'buyer' => 'App buyers compare workflow clarity, user roles, data structure, backend reliability, release support and how easily the product can grow.',
                'search' => 'The page should connect app architecture, dashboards, APIs, mobile UX, admin tools, testing and launch planning.',
                'measure' => 'A successful app project is measured by task completion, stability, user adoption, support volume, speed and clean release cycles.',
            ];
        } elseif (str_contains($slug, 'performance')) {
            $notes = [
                'buyer' => 'Performance buyers usually feel the pain already: slow pages, poor Core Web Vitals, lower conversion and weaker mobile experience.',
                'search' => 'The page should explain caching, image optimization, JavaScript control, CSS cleanup, server response and Lighthouse/Core Web Vitals checks.',
                'measure' => 'Progress is measured through LCP, INP, CLS, page weight, mobile speed, crawl efficiency and conversion lift.',
            ];
        } elseif (str_contains($slug, 'maintenance')) {
            $notes = [
                'buyer' => 'Maintenance buyers want calm: updates handled, backups tested, issues noticed early and small improvements made without drama.',
                'search' => 'The page should include support, monitoring, backups, security updates, reports, uptime and written-first communication.',
                'measure' => 'Good maintenance is measured by uptime, fewer urgent fixes, stable forms, safe updates, backup reliability and clear monthly notes.',
            ];
        } elseif (str_contains($slug, 'website') || str_contains($slug, 'web-design') || str_contains($slug, 'web-development') || str_contains($slug, 'wordpress') || str_contains($slug, 'squarespace') || str_contains($slug, 'joomla')) {
            $notes = [
                'buyer' => 'Website buyers compare first impression, mobile quality, proof, speed, service clarity and whether the site makes it easy to enquire.',
                'search' => 'The page should cover service intent, technical SEO, responsive design, schema, internal links, local relevance and conversion content.',
                'measure' => 'Website quality is measured through organic visibility, page speed, enquiry quality, scroll depth, form completion and support requests.',
            ];
        }

        return [
            ['title' => 'What buyers compare', 'body' => $notes['buyer'], 'icon' => 'fa-user-shield'],
            ['title' => 'What search engines need', 'body' => $notes['search'], 'icon' => 'fa-magnifying-glass-chart'],
            ['title' => 'What should be measured', 'body' => $notes['measure'], 'icon' => 'fa-chart-line'],
        ];
    }

    private function serviceMetaDescription(array $service): string
    {
        $title = $service['title'];
        $tags = implode(', ', array_slice($service['tags'] ?? [], 0, 3));
        return $title . ' by Crest Web Media: human-written service strategy, SEO essentials, fast design, secure PHP/MySQL delivery, lead capture and support. Focus areas include ' . $tags . '.';
    }

    private function serviceDetails(): array
    {
        $default = [
            'intro' => 'A focused delivery path for businesses that need a clean, secure and commercially useful digital asset.',
            'ideal_for' => ['Growing businesses', 'Service companies', 'Teams replacing slow or outdated systems'],
            'deliverables' => ['Discovery workshop', 'Conversion-focused pages', 'Performance and SEO setup', 'Launch checklist', 'Handoff documentation'],
            'benefits' => [
                'A faster, cleaner digital asset built to produce leads and sales.',
                'Security, SEO and performance considered from the first sprint.',
                'Documentation and handoff that makes future growth easier.',
            ],
            'process' => ['Discovery', 'Roadmap', 'Design', 'Build', 'Test', 'Launch', 'Optimize'],
            'faq' => [
                ['question' => 'How quickly can this be delivered?', 'answer' => 'Most focused projects start within a week and ship in milestone releases based on scope.'],
                ['question' => 'Will it be optimized for Core Web Vitals?', 'answer' => 'Yes. Performance budgets, image optimization, caching and clean front-end delivery are included.'],
                ['question' => 'Can you support it after launch?', 'answer' => 'Yes. Maintenance plans include updates, monitoring, backups and improvement reports.'],
            ],
        ];

        return array_merge([
            'default' => $default,
            'website-development' => array_replace($default, [
                'intro' => 'Custom websites and CMS platforms for businesses that need speed, clarity, security and measurable enquiries.',
                'ideal_for' => ['Digital agencies', 'Local service businesses', 'Tourism and hospitality brands', 'B2B lead generation'],
                'deliverables' => ['Custom responsive website', 'CMS-ready page structure', 'Lead capture forms', 'SEO metadata and schema', 'Speed and security checklist'],
            ]),
            'web-design' => array_replace($default, [
                'intro' => 'High-trust UI/UX design for brands that want a premium interface and a clearer path from visitor attention to action.',
                'ideal_for' => ['Website redesigns', 'SaaS dashboards', 'Service brands', 'Landing pages'],
                'deliverables' => ['UX audit', 'Wireframes', 'High-fidelity responsive design', 'Design system components', 'Conversion notes'],
            ]),
            'app-development' => array_replace($default, [
                'intro' => 'Application development for workflow tools, portals, dashboards and mobile-first experiences with strong architecture.',
                'ideal_for' => ['Client portals', 'Booking systems', 'Internal tools', 'Mobile app MVPs'],
                'deliverables' => ['App architecture', 'User flows', 'API-ready backend', 'Admin dashboard', 'Testing and deployment plan'],
            ]),
            'app-publishing' => array_replace($default, [
                'intro' => 'Release support for app teams that need clean store listings, build checks and launch coordination.',
                'ideal_for' => ['Android releases', 'iOS launches', 'App updates', 'Compliance fixes'],
                'deliverables' => ['Store checklist', 'Listing copy support', 'Build review', 'Privacy notes', 'Launch support'],
            ]),
            'api-integration' => array_replace($default, [
                'intro' => 'API integration work that connects your website, app, CRM, payment system, booking engine and automations into one smoother workflow.',
                'ideal_for' => ['Stripe and payment flows', 'CRM and lead routing', 'Booking engines', 'Zapier and automation', 'Third-party platform sync'],
                'deliverables' => ['API discovery and mapping', 'Secure credential handling', 'Webhook setup', 'Error logging', 'Integration testing and handoff notes'],
                'benefits' => [
                    'Reduce manual admin by connecting forms, payments, bookings and CRM tools.',
                    'Improve data accuracy with validated requests, secure webhooks and clear failure handling.',
                    'Give your website or app the power to talk to the services your business already uses.',
                ],
                'faq' => [
                    ['question' => 'Which APIs can you integrate?', 'answer' => 'Common integrations include Stripe, PayPal, CRMs, booking tools, Google APIs, Zapier, email platforms, analytics and custom REST APIs.'],
                    ['question' => 'Do you handle webhooks?', 'answer' => 'Yes. Webhook endpoints can include validation, logging, retry strategy and admin visibility.'],
                    ['question' => 'Can you fix a broken integration?', 'answer' => 'Yes. I can audit failed requests, authentication issues, payload mismatches and unreliable automation flows.'],
                ],
            ]),
            'penetration-testing' => array_replace($default, [
                'intro' => 'Hands-on security testing for websites, APIs and admin areas before attackers or automated scanners find weaknesses.',
                'ideal_for' => ['Web apps', 'APIs', 'WordPress sites', 'Client portals'],
                'deliverables' => ['Recon and threat review', 'OWASP checks', 'Authentication testing', 'Risk-ranked report', 'Remediation guidance'],
            ]),
            'seo' => array_replace($default, [
                'intro' => 'Technical and on-page SEO work that gives Google a cleaner site to crawl and customers a clearer path to convert.',
                'ideal_for' => ['Local SEO', 'Service websites', 'Content hubs', 'Site migrations'],
                'deliverables' => ['Technical audit', 'Keyword and intent map', 'Schema setup', 'Internal linking plan', 'SEO reporting'],
            ]),
            'pay-per-click-advertising' => array_replace($default, [
                'intro' => 'Pay per click advertising for businesses that want targeted enquiries while SEO authority is building.',
                'ideal_for' => ['Local service businesses', 'Ecommerce stores', 'New offers', 'SEO campaigns that need immediate leads'],
                'deliverables' => ['Keyword and competitor research', 'Google Ads campaign setup', 'Landing page recommendations', 'Conversion tracking', 'Weekly optimization notes'],
                'benefits' => [
                    'Generate targeted traffic faster while long-term SEO pages gain traction.',
                    'Connect ad spend to leads with cleaner conversion tracking and landing pages.',
                    'Use PPC data to discover which keywords deserve dedicated SEO pages.',
                ],
                'faq' => [
                    ['question' => 'Can PPC work with SEO?', 'answer' => 'Yes. PPC can generate leads quickly while SEO builds organic visibility. Search term data can also guide future content and service pages.'],
                    ['question' => 'Do you build landing pages for ads?', 'answer' => 'Yes. PPC works best with focused landing pages, strong offers, tracking and fast page speed.'],
                    ['question' => 'Can you manage Google Ads?', 'answer' => 'Campaign setup, conversion tracking, landing page advice and ongoing optimization can be included.'],
                ],
            ]),
            'performance-optimization' => array_replace($default, [
                'intro' => 'Speed and Core Web Vitals optimization for websites that feel heavy, lose users or struggle in search performance.',
                'ideal_for' => ['Slow WordPress sites', 'Image-heavy websites', 'E-commerce stores', 'Landing pages'],
                'deliverables' => ['Performance audit', 'Image optimization', 'Caching plan', 'CSS/JS cleanup', 'Lighthouse report'],
            ]),
            'maintenance-and-support' => array_replace($default, [
                'intro' => 'Ongoing website care for businesses that need updates, security monitoring, backups and practical monthly improvements.',
                'ideal_for' => ['Business websites', 'WordPress sites', 'Retainer clients', 'Agencies needing overflow support'],
                'deliverables' => ['Updates', 'Backups', 'Uptime checks', 'Security monitoring', 'Monthly summary'],
            ]),
            'plugin-development' => array_replace($default, [
                'intro' => 'Custom plugin and extension development for workflows that off-the-shelf tools do not handle well.',
                'ideal_for' => ['WordPress plugins', 'Squarespace custom features', 'CMS integrations', 'Automation modules'],
                'deliverables' => ['Feature specification', 'Plugin architecture', 'Secure code', 'Admin settings', 'Documentation'],
            ]),
            'wordpress-development' => array_replace($default, [
                'intro' => 'WordPress development for businesses that need the flexibility of WordPress without the usual speed and security problems.',
                'ideal_for' => ['Business websites', 'WooCommerce stores', 'Theme rebuilds', 'Plugin-heavy sites'],
                'deliverables' => ['Custom theme work', 'Plugin setup or development', 'Security hardening', 'Performance optimization', 'Editor training notes'],
            ]),
            'squarespace-development' => array_replace($default, [
                'intro' => 'Squarespace builds and customizations for brands that need polished visuals, clean pages and easy content management.',
                'ideal_for' => ['Portfolio sites', 'Service brands', 'Simple commerce', 'Template customization'],
                'deliverables' => ['Template setup', 'Custom CSS/JS', 'Page design', 'SEO setup', 'Launch support'],
            ]),
            'joomla-development' => array_replace($default, [
                'intro' => 'Joomla development, migration and support for established websites that need a cleaner, safer and easier system.',
                'ideal_for' => ['Existing Joomla sites', 'Content-heavy portals', 'Membership websites', 'Migration projects'],
                'deliverables' => ['Template work', 'Component support', 'Security cleanup', 'Content migration', 'Maintenance plan'],
            ]),
        ], $this->aiServiceDetails($default), $this->regionalServiceDetails($default));
    }

    private function aiServiceDetails(array $default): array
    {
        return [
            'ai-integration-services' => array_replace($default, [
                'intro' => 'AI integration services for businesses that want practical automation inside existing websites, CRMs, forms, support inboxes and internal workflows.',
                'ideal_for' => ['Service businesses adding AI to existing websites', 'Teams with repetitive admin work', 'Companies using CRMs, forms and spreadsheets', 'Agencies that need AI implementation help'],
                'deliverables' => ['AI opportunity audit', 'Workflow and data map', 'AI API integration', 'Prompt and guardrail setup', 'Testing, logging and handoff'],
                'benefits' => [
                    'Turn existing business processes into faster AI-assisted workflows.',
                    'Add AI without rebuilding your full website or internal system.',
                    'Keep human approval, logging and security around business-critical actions.',
                ],
                'process' => ['Audit', 'Use-case map', 'Prototype', 'Integrate', 'Test', 'Train', 'Improve'],
                'faq' => [
                    ['question' => 'Can AI be added to an existing website?', 'answer' => 'Yes. AI can be integrated into existing forms, search, support flows, CRMs, dashboards and internal workflows without a full rebuild.'],
                    ['question' => 'Which AI tools can you integrate?', 'answer' => 'Projects can use OpenAI APIs, automation platforms, CRM APIs, knowledge bases and custom backend logic depending on the workflow.'],
                    ['question' => 'Is business data protected?', 'answer' => 'Yes. AI workflows should include validation, access control, logging, least-privilege API keys and clear human approval points.'],
                ],
            ]),
            'ai-workflow-automation' => array_replace($default, [
                'intro' => 'AI workflow automation for businesses that want to reduce manual tasks, route enquiries faster and give teams cleaner operating systems.',
                'ideal_for' => ['Operations teams', 'Sales and support teams', 'Agencies', 'Local service businesses', 'Busy founders'],
                'deliverables' => ['Workflow audit', 'Automation blueprint', 'AI summary and routing logic', 'CRM or spreadsheet sync', 'Error handling and reporting'],
                'benefits' => [
                    'Reduce repetitive admin and speed up response times.',
                    'Connect forms, emails, CRM updates and reports into one smoother workflow.',
                    'Use AI for summaries, triage and drafts while keeping humans in control.',
                ],
                'faq' => [
                    ['question' => 'What workflows can AI automate?', 'answer' => 'Common workflows include lead triage, quote intake, support summaries, meeting notes, CRM updates, content briefs and internal reporting.'],
                    ['question' => 'Do I need a new system?', 'answer' => 'Usually no. AI automation can often connect the tools you already use through APIs and secure workflow logic.'],
                    ['question' => 'Can automation include approvals?', 'answer' => 'Yes. Sensitive workflows can require human approval before messages, updates or actions are completed.'],
                ],
            ]),
            'ai-chatbot-development' => array_replace($default, [
                'intro' => 'AI chatbot development for websites that need better lead capture, instant answers, support triage and smoother customer handoff.',
                'ideal_for' => ['Service websites', 'Ecommerce stores', 'Support teams', 'SaaS products', 'Agencies'],
                'deliverables' => ['Chatbot flow map', 'Knowledge-base setup', 'Lead capture fields', 'Escalation and handoff logic', 'Analytics and improvement notes'],
                'benefits' => [
                    'Answer common questions instantly while capturing qualified leads.',
                    'Route support requests with better context and less manual back-and-forth.',
                    'Train the assistant around your actual offers, FAQs and business rules.',
                ],
            ]),
            'ai-crm-automation' => array_replace($default, [
                'intro' => 'AI CRM automation for businesses that want cleaner lead qualification, faster follow-up and less manual CRM maintenance.',
                'ideal_for' => ['Sales teams', 'Consultants', 'Real estate and local services', 'Agencies', 'B2B companies'],
                'deliverables' => ['CRM field and pipeline audit', 'Lead scoring logic', 'AI follow-up draft flow', 'Webhook/API setup', 'Reporting dashboard notes'],
                'benefits' => [
                    'Stop losing leads because follow-up is slow or inconsistent.',
                    'Use AI to summarize enquiries and recommend next actions.',
                    'Keep CRM records cleaner with automated structured updates.',
                ],
            ]),
        ];
    }

    private function regionalServiceDetails(array $default): array
    {
        $pages = [
            'web-design-ireland' => ['Irish-market interface systems', 'Irish service brands, hospitality teams and B2B operators', 'Ireland', 'Dublin search intent, local proof, fast mobile UX and conversion-mapped content'],
            'website-development-ireland' => ['Cloudways-ready PHP delivery', 'Irish B2B, logistics, healthcare and service companies', 'Ireland', 'PHP 8 architecture, custom CMS control, Core Web Vitals, security and regional SEO'],
            'web-design-dublin' => ['Dublin-grade digital positioning', 'Dublin consultancies, clinics, trades, agencies and growth companies', 'Dublin', 'premium UX, local authority signals, service-page hierarchy and lead capture'],
            'ecommerce-website-development-ireland' => ['Irish ecommerce build systems', 'Irish retailers, product brands and fulfilment-led operators', 'Ireland', 'product discovery, VAT clarity, shipping confidence, checkout speed and local trust'],
            'web-development-uk' => ['UK web engineering systems', 'UK B2B teams, service firms, startups and operations-led brands', 'the UK', 'custom CMS layers, integrations, accessibility signals, security and support'],
            'web-design-uk' => ['UK-market conversion interfaces', 'UK service brands, consultancies and high-growth operators', 'the UK', 'responsive UX, accessibility-aware design, conversion copy and technical SEO'],
            'website-development-usa' => ['US-facing remote engineering', 'US startups, service brands and enterprise teams', 'the USA', 'fast release cycles, CMS delivery, API integrations, AI-ready workflows and written support'],
            'web-design-usa' => ['US enterprise-style UI systems', 'US SaaS teams, consultants and B2B service brands', 'the USA', 'trust-focused UX, sharper positioning, lead velocity and performance-led layouts'],
            'web-design-europe' => ['European-ready design systems', 'European service brands, tourism groups, clinics and B2B teams', 'Europe', 'GDPR-aware forms, multilingual-ready hierarchy, regional authority and fast mobile design'],
            'web-development-europe' => ['European platform delivery', 'European teams building secure websites, portals and API-led systems', 'Europe', 'secure PHP platforms, API architecture, CMS governance, compliance-aware delivery and support'],
        ];

        $details = [];
        foreach ($pages as $slug => [$positioning, $audience, $region, $focus]) {
            $details[$slug] = array_replace($default, [
                'intro' => ucfirst($positioning) . ' for ' . $audience . ' that need authority, speed, clear enquiry paths and search visibility across ' . $region . '.',
                'ideal_for' => [$audience, 'Teams targeting high-intent searches in ' . $region, 'Brands replacing outdated websites with measurable lead systems', 'Agencies needing senior remote delivery support'],
                'deliverables' => ['Localized search-intent map', ucfirst($focus), 'Responsive interface and PHP/MySQL build support', 'Written enquiry and WhatsApp flow', 'Launch SEO and Core Web Vitals checklist'],
                'benefits' => [
                    'A region-aware page architecture built around the searches buyers actually use.',
                    'Stronger trust, faster mobile journeys and clearer conversion paths for visitors from ' . $region . '.',
                    'A practical foundation for SEO, PPC, local campaigns, analytics and future AI workflow automation.',
                ],
                'process' => ['Intent map', 'Positioning', 'UX system', 'PHP build', 'SEO layer', 'Launch QA', 'Growth review'],
                'faq' => [
                    ['question' => 'Do you work remotely with clients in ' . $region . '?', 'answer' => 'Yes. Crest Web Media is remote-first and coordinates projects through email, WhatsApp and support tickets.'],
                    ['question' => 'Can the page support SEO and paid search?', 'answer' => 'Yes. The structure connects localized search intent, fast landing-page performance, conversion actions and measurement so SEO and Google Ads have a stronger base.'],
                    ['question' => 'Can AI and CRM automation be added later?', 'answer' => 'Yes. AI chat, lead qualification, CRM updates, support routing and reporting workflows can be added once the website foundation is stable.'],
                ],
            ]);
        }

        return $details;
    }

    public function why(): array
    {
        return [
            ['title' => 'Built Around Revenue', 'body' => 'Websites, apps and automations are shaped around qualified enquiries, buyer confidence and measurable commercial movement.', 'icon' => 'fa-money-bill-trend-up'],
            ['title' => 'Performance By Default', 'body' => 'Core Web Vitals, cache strategy, asset weight and mobile stability are planned before launch, not patched after complaints.', 'icon' => 'fa-bolt'],
            ['title' => 'Search Architecture', 'body' => 'Technical SEO, schema, internal linking and service-page depth are built into the structure so rankings have a stronger foundation.', 'icon' => 'fa-chart-line'],
            ['title' => 'Secure Operating Layer', 'body' => 'Forms, logins, uploads, APIs and admin workflows are handled with practical hardening and risk-aware implementation.', 'icon' => 'fa-shield'],
            ['title' => 'AI With Guardrails', 'body' => 'AI is used to accelerate research, workflows and delivery while keeping human judgment, logging and business control in place.', 'icon' => 'fa-brain'],
        ];
    }

    public function technologies(): array
    {
        return [
            ['name' => 'PHP', 'icon' => 'fa-brands fa-php', 'image' => 'tech/php.png', 'group' => 'Backend'],
            ['name' => 'Laravel', 'icon' => 'fa-brands fa-laravel', 'image' => 'tech/laravel.png', 'group' => 'Backend'],
            ['name' => 'MySQL', 'icon' => 'fa-solid fa-database', 'image' => 'tech/mysql.png', 'group' => 'Backend'],
            ['name' => 'jQuery', 'icon' => 'fa-solid fa-code', 'image' => 'tech/jquery.png', 'group' => 'Frontend'],
            ['name' => 'Tailwind CSS', 'icon' => 'fa-solid fa-wind', 'image' => 'tech/tailwindcss.png', 'group' => 'Frontend'],
            ['name' => 'JavaScript', 'icon' => 'fa-brands fa-js', 'image' => 'tech/javascript.png', 'group' => 'Frontend'],
            ['name' => 'HTML5', 'icon' => 'fa-brands fa-html5', 'image' => 'tech/html5.png', 'group' => 'Frontend'],
            ['name' => 'CSS3', 'icon' => 'fa-brands fa-css3-alt', 'image' => 'tech/css3.png', 'group' => 'Frontend'],
            ['name' => 'Bootstrap', 'icon' => 'fa-brands fa-bootstrap', 'image' => 'tech/bootstrap.png', 'group' => 'Frontend'],
            ['name' => 'Figma', 'icon' => 'fa-solid fa-palette', 'image' => 'tech/figma.svg', 'group' => 'Frontend'],
            ['name' => 'WordPress', 'icon' => 'fa-brands fa-wordpress', 'image' => 'tech/wordpress.png', 'group' => 'Platforms'],
            ['name' => 'Joomla', 'icon' => 'fa-brands fa-joomla', 'image' => 'tech/joomla.png', 'group' => 'Platforms'],
            ['name' => 'Squarespace', 'icon' => 'fa-solid fa-link', 'image' => 'tech/squarespace.png', 'group' => 'Platforms'],
            ['name' => 'Kali Linux', 'icon' => 'fa-solid fa-terminal', 'image' => 'tech/kalilinux.png', 'group' => 'Security'],
            ['name' => 'FreeBSD', 'icon' => 'fa-solid fa-shield-halved', 'image' => 'tech/freebsd.png', 'group' => 'Security'],
            ['name' => 'OWASP', 'icon' => 'fa-solid fa-bug-slash', 'image' => 'tech/owasp.svg', 'group' => 'Security'],
            ['name' => 'Google Ads', 'icon' => 'fa-brands fa-google', 'image' => 'tech/googleads.svg', 'group' => 'Marketing'],
            ['name' => 'Search Console', 'icon' => 'fa-solid fa-magnifying-glass-chart', 'image' => 'tech/search-console.svg', 'group' => 'Marketing'],
            ['name' => 'OpenAI', 'icon' => 'fa-solid fa-robot', 'image' => 'tech/openai.svg', 'group' => 'AI'],
            ['name' => 'Git', 'icon' => 'fa-brands fa-git-alt', 'image' => 'tech/git.png', 'group' => 'Workflow'],
            ['name' => 'Docker', 'icon' => 'fa-brands fa-docker', 'image' => 'tech/docker.png', 'group' => 'Cloud'],
            ['name' => 'Stripe', 'icon' => 'fa-solid fa-credit-card', 'image' => 'tech/stripe.png', 'group' => 'API'],
            ['name' => 'Zapier', 'icon' => 'fa-solid fa-bolt', 'image' => 'tech/zapier.png', 'group' => 'API'],
            ['name' => 'Cloudflare', 'icon' => 'fa-solid fa-cloud', 'image' => 'tech/cloudflare.png', 'group' => 'Cloud'],
            ['name' => 'NGINX', 'icon' => 'fa-solid fa-server', 'image' => 'tech/nginx.png', 'group' => 'Cloud'],
        ];
    }

    public function process(): array
    {
        return [
            ['step' => '01', 'title' => 'Diagnostic', 'body' => 'Clarify the offer, market, buyer friction, technical constraints and the action the system must create.', 'icon' => 'fa-magnifying-glass-chart'],
            ['step' => '02', 'title' => 'Architecture', 'body' => 'Define sitemap, content model, SEO map, integration points, data flow and launch priorities.', 'icon' => 'fa-clipboard-list'],
            ['step' => '03', 'title' => 'Interface System', 'body' => 'Design conversion-mapped layouts that reduce cognitive friction and make decisions easier on mobile and desktop.', 'icon' => 'fa-pen-ruler'],
            ['step' => '04', 'title' => 'Engineering', 'body' => 'Build lean PHP/MySQL, Laravel or API-led modules with reusable components and maintainable backend logic.', 'icon' => 'fa-laptop-code'],
            ['step' => '05', 'title' => 'Quality Gates', 'body' => 'Check performance, accessibility, forms, responsive states, security controls and conversion tracking.', 'icon' => 'fa-vial-circle-check'],
            ['step' => '06', 'title' => 'Release', 'body' => 'Deploy with redirects, metadata, cache rules, analytics, gateway settings and a clear launch checklist.', 'icon' => 'fa-rocket'],
            ['step' => '07', 'title' => 'Growth Loop', 'body' => 'Review rankings, leads, support tickets, speed and user behaviour to decide the next improvement.', 'icon' => 'fa-headset'],
        ];
    }

    public function portfolio(): array
    {
        return [
            ['title' => 'Embryomic', 'category' => 'Healthcare Technology Website', 'summary' => 'Advanced reproductive health technology website focused on clinical trust, modern presentation and clear specialist pathways.', 'accent' => 'blue', 'url' => 'https://www.embryomic.com/', 'image' => 'embryomic.webp'],
            ['title' => 'Dyno Locks', 'category' => 'Locksmith Website', 'summary' => '24-hour locksmith and security service website built around fast enquiries, trust and local search.', 'accent' => 'cyan', 'url' => 'https://www.dynolocks.ie', 'image' => 'dynolocks.webp'],
            ['title' => 'VanQuotes.ie', 'category' => 'Lead Generation Platform', 'summary' => 'Ireland removals quote platform connecting customers with man-with-a-van and moving companies.', 'accent' => 'green', 'url' => 'https://www.vanquotes.ie', 'image' => 'vanquotes.webp'],
            ['title' => 'DepilConcept Ireland', 'category' => 'Beauty & Clinic Website', 'summary' => 'Permanent hair removal and beauty treatment website with polished brand presentation.', 'accent' => 'blue', 'url' => 'https://www.depilconcept.ie', 'image' => 'depilconcept.webp'],
            ['title' => 'Locksmiths.ie', 'category' => 'Emergency Service Website', 'summary' => 'Emergency locksmith website focused on rapid response, service clarity and customer enquiries.', 'accent' => 'orange', 'url' => 'https://www.locksmiths.ie', 'image' => 'locksmiths.webp'],
            ['title' => "Gary's Tech Hub Shop", 'category' => 'E-Commerce Store', 'summary' => 'Technology parts and accessories storefront for devices, repairs, networking and local Irish orders.', 'accent' => 'purple', 'url' => 'https://shop.garystechhub.ie', 'image' => 'garytechhub.webp'],
            ['title' => 'Ceres Fertility', 'category' => 'Healthcare Website', 'summary' => 'Fertility care website for IVF and surrogacy information, trust-building content and enquiries.', 'accent' => 'cyan', 'url' => 'https://www.ceresfertility.com', 'image' => 'ceresfertility.webp'],
            ['title' => 'IP Fertility Options', 'category' => 'Fertility Website', 'summary' => 'Fertility options website designed around sensitive healthcare information, trust signals and enquiry pathways.', 'accent' => 'blue', 'url' => 'https://www.ipfertilityoptions.com/', 'image' => 'ipfertilityoptions.webp'],
            ['title' => 'Ennis Bridal', 'category' => 'Bridal Retail Website', 'summary' => 'Elegant bridal boutique website with visual product discovery, appointment intent and premium brand presentation.', 'accent' => 'purple', 'url' => 'https://www.ennisbridal.com/', 'image' => 'ennisbridal.webp'],
            ['title' => 'Cliffs of Moher Holiday Home', 'category' => 'Tourism Website', 'summary' => 'Holiday accommodation website built to showcase the location, guide guests and convert travel interest into bookings.', 'accent' => 'green', 'url' => 'https://cliffsofmoherholidayhome.com/', 'image' => 'cliffsofmoherholidayhome.webp'],
            ['title' => 'IVF Directory Guide', 'category' => 'Healthcare Directory', 'summary' => 'Fertility directory platform created for clinic discovery, structured information and search-friendly healthcare content.', 'accent' => 'cyan', 'url' => 'https://ivfdirectoryguide.com/', 'image' => 'ivfdirectoryguide.webp'],
            ['title' => 'Clavis Fertility', 'category' => 'Fertility Clinic Website', 'summary' => 'Fertility clinic website focused on expert positioning, patient confidence and clear treatment enquiry flows.', 'accent' => 'orange', 'url' => 'https://www.clavisfertility.com/', 'image' => 'clavisfertility.webp'],
            ['title' => 'Smart Dental', 'category' => 'Dental Clinic Website', 'summary' => 'Dublin dental clinic website designed for service clarity, patient trust, appointment intent and local search visibility.', 'accent' => 'blue', 'url' => 'https://www.smartdental.ie/', 'image' => 'smartdental.webp'],
            ['title' => 'Global Travel Platform', 'category' => 'Travel Website System', 'summary' => 'Destination content architecture with faster enquiry paths, structured location pages and mobile-first booking intent.', 'accent' => 'green'],
            ['title' => 'FinTech Dashboard', 'category' => 'Web Application', 'summary' => 'Role-aware financial dashboard concept with clean data hierarchy, account-level visibility and decision-grade reporting states.', 'accent' => 'blue'],
            ['title' => 'Healthcare Platform', 'category' => 'Web Application', 'summary' => 'Healthcare workflow interface built around trust, consent-aware messaging, service discovery and structured patient actions.', 'accent' => 'orange'],
            ['title' => 'E-Commerce Store', 'category' => 'Commerce Platform', 'summary' => 'Conversion-focused storefront architecture with product clarity, checkout confidence and performance-led browsing.', 'accent' => 'purple'],
            ['title' => 'Task Management App', 'category' => 'Mobile Application', 'summary' => 'Operational task interface with quick capture, status clarity, team accountability and app-style mobile navigation.', 'accent' => 'cyan'],
            ['title' => 'RDT Care Document Validator', 'category' => 'Lab Verification Portal', 'summary' => 'Document validation portal for labs, built around quick verification, clear trust signals and secure access to report checks.', 'accent' => 'green', 'url' => 'https://verify.rdtcare.com/#', 'image' => 'rdtcare-validator.webp'],
        ];
    }

    public function testimonials(): array
    {
        return [
            ['name' => 'Liam OConnor', 'role' => 'CEO, TravelGrid', 'country' => 'Global', 'flag' => 'GL', 'quote' => 'Crest Web Media took the time to understand what our customers needed before touching the design. The finished website feels faster, clearer and far more professional, and the enquiry quality improved because the pages finally explain our offer properly.'],
            ['name' => 'Sarah Johnson', 'role' => 'Founder, FinTechOS', 'country' => 'Global', 'flag' => 'GL', 'quote' => 'The web application build was handled with real care. We had dashboards, user flows, forms and admin details that needed to work cleanly, and everything was explained without technical drama. It felt like working with someone who cared about the product, not just the code.'],
            ['name' => 'Thomas Muller', 'role' => 'Owner, StyleHaus', 'country' => 'Global', 'flag' => 'GL', 'quote' => 'Our store used to look fine but it did not guide people to buy. Crest Web Media tightened the layout, improved performance and made the product pages easier to trust. The site now feels more premium, and customers tell us checkout is much smoother.'],
            ['name' => 'David Byrne', 'role' => 'CTO, TechSecure', 'country' => 'Global', 'flag' => 'GL', 'quote' => 'The security review was practical and easy to act on. Instead of just sending a scary report, they showed us what mattered, what could wait and how to fix the risks properly. It gave our team confidence before pushing the next release live.'],
        ];
    }

    public function faqs(): array
    {
        return [
            ['question' => 'Where does Crest Web Media work?', 'answer' => 'Crest Web Media works remotely with clients in Ireland, the UK, the USA and Europe, with written-first communication through email, WhatsApp and support tickets.'],
            ['question' => 'Can you build the website, CMS and backend together?', 'answer' => 'Yes. Projects can include public pages, custom PHP/MySQL admin workflows, media management, blog publishing, SEO controls, payment settings and secure user access.'],
            ['question' => 'Do you handle security and performance?', 'answer' => 'Yes. Forms, uploads, sessions, headers, cache behaviour, Core Web Vitals and hosting constraints are reviewed during delivery, with deeper penetration testing available as a dedicated service.'],
            ['question' => 'Can AI be integrated into an existing website?', 'answer' => 'Yes. AI can support lead qualification, CRM updates, support triage, content operations, summaries and internal workflows without forcing a full rebuild.'],
        ];
    }

    public function pricing(): array
    {
        return [
            ['name' => 'Launch', 'price' => 'Focused Build', 'features' => ['Offer and sitemap strategy', 'Conversion website', 'Core SEO setup', 'Written enquiry paths', 'Launch support']],
            ['name' => 'Growth', 'price' => 'Scale System', 'features' => ['Custom CMS', 'Blog and portfolio structure', 'Performance optimization', 'Analytics and reporting', 'Monthly improvement plan']],
            ['name' => 'Platform', 'price' => 'Custom', 'features' => ['Web app architecture', 'Client portal', 'API integrations', 'Security review', 'Roadmap and support retainer']],
        ];
    }

    public function posts(): array
    {
        return array_map(fn (array $topic, int $index): array => $this->buildLongFormPost($topic, $index), $this->blogTopics(), array_keys($this->blogTopics()));
    }

    private function blogTopics(): array
    {
        return [
            ['ai-website-development-trends-2026', 'AI Website Development Trends 2026: How Modern Sites Will Sell, Rank and Automate', 'AI Development', 'AI website development trends 2026', ['AI website design', 'AI web development', 'website automation', 'AI SEO'], 'business websites', 'AI-assisted planning, component systems and automated follow-up', 'turn traffic into qualified enquiries without making the site feel robotic'],
            ['ecommerce-trends-2026-ai-personalization-checkout', 'Ecommerce Trends 2026: AI Personalization, Faster Checkout and Trust-First Store Design', 'E-Commerce', 'ecommerce trends 2026', ['AI ecommerce personalization', 'checkout optimization', 'online store development', 'first-party data'], 'online stores', 'real-time product guidance, checkout confidence and first-party customer data', 'increase conversion while reducing wasted ad spend'],
            ['ai-search-seo-strategy-for-business-websites', 'AI Search SEO Strategy: How Business Websites Can Stay Visible in AI Results', 'SEO', 'AI search SEO strategy', ['generative engine optimization', 'AI Overviews SEO', 'structured content', 'SERP visibility'], 'service businesses', 'AI search, longer queries and evidence-rich content architecture', 'earn visibility when search engines summarize, compare and recommend'],
            ['headless-commerce-vs-traditional-ecommerce', 'Headless Commerce vs Traditional Ecommerce: Which Build Makes Sense in 2026?', 'E-Commerce', 'headless commerce vs traditional ecommerce', ['headless ecommerce', 'WooCommerce development', 'Shopify development', 'commerce architecture'], 'growth-focused retailers', 'API-first storefronts, flexible content and operational complexity', 'choose the right store architecture before money is spent'],
            ['core-web-vitals-inp-performance-optimization', 'Core Web Vitals and INP: The Practical Performance Plan for Lead-Generating Websites', 'Performance', 'Core Web Vitals INP optimization', ['Interaction to Next Paint', 'website speed optimization', 'Core Web Vitals', 'performance SEO'], 'lead-generation websites', 'interaction speed, JavaScript budgets and stable user journeys', 'make the website feel instant enough to keep buyers moving'],
            ['progressive-web-apps-vs-native-apps-2026', 'Progressive Web Apps vs Native Apps in 2026: The Smart Choice for Growing Businesses', 'App Development', 'progressive web apps vs native apps', ['PWA development', 'mobile app development', 'cross-platform apps', 'app strategy'], 'businesses planning an app', 'installable web apps, native capabilities and maintenance cost', 'ship useful app experiences without overbuilding'],
            ['secure-web-development-owasp-2025', 'Secure Web Development in 2026: Lessons From OWASP, APIs and Real Business Risk', 'Security', 'secure web development 2026', ['OWASP 2025', 'API security', 'secure PHP development', 'website security'], 'business websites and platforms', 'secure forms, authorization, API controls and practical hardening', 'protect revenue, user trust and operational continuity'],
            ['ai-chatbots-for-business-websites', 'AI Chatbots for Business Websites: From Basic Support Widget to Revenue Assistant', 'AI Automation', 'AI chatbots for business websites', ['AI chatbot development', 'website chatbot', 'lead qualification', 'customer support automation'], 'service websites', 'AI chat, lead routing, support summaries and human handoff', 'capture more useful enquiries while reducing repetitive support work'],
            ['website-accessibility-inclusive-design-seo', 'Website Accessibility and Inclusive Design: Why Better UX Also Improves SEO and Sales', 'Web Design', 'website accessibility inclusive design', ['accessible web design', 'inclusive UX', 'WCAG', 'SEO usability'], 'public-facing websites', 'accessible components, readable content and inclusive conversion paths', 'reach more users and remove silent friction from the buying journey'],
            ['app-privacy-compliance-ios-android', 'App Privacy Compliance for iOS and Android: What Businesses Need Before Launch', 'App Development', 'app privacy compliance iOS Android', ['App Store privacy labels', 'Android data safety', 'mobile app privacy', 'app launch checklist'], 'mobile app owners', 'privacy labels, data mapping, permissions and transparent onboarding', 'avoid launch delays and build trust before the first download'],
            ['woocommerce-shopify-automation-for-growing-stores', 'WooCommerce and Shopify Automation: How Growing Stores Can Save Time and Sell More', 'E-Commerce', 'WooCommerce Shopify automation', ['ecommerce automation', 'Shopify automation', 'WooCommerce automation', 'order workflows'], 'ecommerce operators', 'inventory sync, abandoned checkout, customer segments and reporting', 'reduce manual work while improving customer timing'],
            ['api-first-website-development-business-systems', 'API-First Website Development: Connecting Websites, CRMs, Payments and AI Workflows', 'Web Development', 'API-first website development', ['API integration', 'CRM integration', 'payment API', 'business automation'], 'businesses with disconnected tools', 'payments, CRM, booking, reporting and AI workflow integration', 'turn the website into the operating layer of the business'],
            ['android-15-edge-to-edge-mobile-app-ux', 'Android 15 Edge-to-Edge UX: What App Owners Should Know Before Updating', 'App Development', 'Android 15 edge-to-edge UX', ['Android app design', 'edge-to-edge UI', 'mobile UX', 'safe areas'], 'Android app owners', 'system bars, insets, adaptive layouts and visual polish', 'keep apps modern without hiding buttons or breaking layouts'],
            ['ai-workflow-automation-small-business', 'AI Workflow Automation for Small Business: Practical Website, CRM and Support Ideas', 'AI Automation', 'AI workflow automation small business', ['AI automation', 'CRM automation', 'support automation', 'business workflows'], 'small businesses', 'lead intake, summaries, follow-up, reporting and decision support', 'save time without replacing the human relationship'],
            ['ppc-landing-page-trends-2026', 'PPC Landing Page Trends 2026: How to Turn Paid Clicks Into Better Leads', 'PPC', 'PPC landing page trends 2026', ['Google Ads landing pages', 'conversion rate optimization', 'paid search leads', 'PPC ROI'], 'advertisers', 'message match, speed, proof, forms and post-click tracking', 'stop paying for traffic that the page fails to convert'],
            ['local-seo-website-architecture-ireland-uk-usa', 'Local SEO Website Architecture: Ranking Service Pages in Ireland, the UK, the USA and Europe', 'SEO', 'local SEO website architecture', ['local SEO service pages', 'web design Ireland', 'SEO UK USA Europe', 'location pages'], 'local and regional service brands', 'location intent, service clusters, internal links and proof', 'build pages that search engines and buyers can understand'],
            ['client-portal-tickets-digital-agencies', 'Client Portals and Ticketing Systems: The Support Experience Modern Customers Expect', 'Client Portals', 'client portal ticketing system', ['support ticket system', 'client portal', 'customer support website', 'project dashboard'], 'agencies and service businesses', 'tickets, status updates, files, reports and shared accountability', 'make customers feel informed after the sale'],
            ['green-fast-hosting-performance-websites', 'Fast, Efficient Websites: Why Performance, Hosting and Sustainability Now Belong Together', 'Performance', 'fast efficient website hosting', ['green web hosting', 'website performance', 'efficient websites', 'Core Web Vitals'], 'performance-conscious businesses', 'lean code, caching, image strategy, hosting and waste reduction', 'make the site faster, cheaper to run and easier to maintain'],
            ['ai-ready-cms-content-operations', 'AI-Ready CMS Design: Preparing Content Operations for Search, Sales and Automation', 'CMS', 'AI-ready CMS design', ['custom CMS development', 'AI content workflows', 'structured content', 'content operations'], 'content-heavy businesses', 'structured fields, approvals, reusable blocks and AI-assisted editing', 'publish faster without losing brand control'],
            ['website-redesign-strategy-2026', 'Website Redesign Strategy 2026: How to Modernize Without Losing SEO, Leads or Trust', 'Web Design', 'website redesign strategy 2026', ['website redesign SEO', 'conversion redesign', 'website migration', 'modern web design'], 'businesses planning a redesign', 'migration planning, content pruning, design systems and launch QA', 'modernize the brand while protecting search equity and enquiries'],
        ];
    }

    private function buildLongFormPost(array $topic, int $index): array
    {
        [$slug, $title, $category, $focusKeyword, $secondaryKeywords, $audience, $trend, $commercialGoal] = $topic;
        $sections = $this->longFormSections($title, $focusKeyword, $audience, $trend, $commercialGoal, $secondaryKeywords);
        $plainText = $title . ' ' . implode(' ', array_map(
            static fn (array $section): string => $section['heading'] . ' ' . implode(' ', $section['paragraphs']),
            $sections
        ));
        $wordCount = str_word_count(strip_tags($plainText));

        return [
            'slug' => $slug,
            'title' => $title,
            'seo_title' => $title,
            'meta_description' => ucfirst($focusKeyword) . ' guide for ' . $audience . ': trends, SEO essentials, UX, security, implementation steps and measurable business outcomes.',
            'category' => $category,
            'focus_keyword' => $focusKeyword,
            'secondary_keywords' => $secondaryKeywords,
            'excerpt' => 'A fresh, practical ' . $focusKeyword . ' guide for ' . $audience . ', covering strategy, UX, SEO, automation, security and measurable implementation steps.',
            'reading_time' => max(12, (int) ceil($wordCount / 210)) . ' min read',
            'word_count' => $wordCount,
            'views' => 2400 + ($index * 615),
            'published_at' => '2026-07-01',
            'updated_at' => '2026-07-01',
            'body_sections' => $sections,
            'body' => array_merge(...array_map(static fn (array $section): array => $section['paragraphs'], $sections)),
            'checklist' => $this->articleChecklist($focusKeyword, $trend, $commercialGoal),
            'faq' => $this->articleFaq($focusKeyword, $audience, $commercialGoal),
        ];
    }

    private function longFormSections(string $title, string $focusKeyword, string $audience, string $trend, string $commercialGoal, array $secondaryKeywords): array
    {
        $keywordLine = implode(', ', $secondaryKeywords);
        return [
            [
                'heading' => 'Executive Summary',
                'paragraphs' => [
                    $title . ' is not a trend recap. It is a practical guide for ' . $audience . ' that need websites, stores, apps or digital systems to produce measurable work. The important shift is ' . $trend . ', but the commercial question is sharper: how does the business use that shift to ' . $commercialGoal . '? A modern digital project should connect strategy, interface design, technical architecture, search visibility, security and follow-up into one operating system.',
                    'The market is moving away from brochure thinking. Buyers expect faster pages, clearer proof, smarter recommendations, better mobile journeys and less waiting around for answers. Search is also changing. People ask longer questions, compare more options and increasingly see AI-assisted summaries before they click. That means a page must be understandable to humans, search engines and AI systems at the same time. The best answer is not keyword stuffing; it is structured, useful, specific content backed by clean technical signals.',
                    'For Crest Web Media, the lesson is simple: build every digital asset like it has a job. A service page should rank and persuade. A checkout should reduce doubt. A portal should lower support friction. An app should respect privacy and device conventions. AI should remove repetitive work while keeping human judgment visible. This article breaks the topic into strategy, SEO essentials, design decisions, automation, security, measurement and a realistic rollout plan.',
                ],
            ],
            [
                'heading' => 'Why This Trend Matters Now',
                'paragraphs' => [
                    'The reason ' . $focusKeyword . ' matters in 2026 is that digital competition is no longer limited to who has the nicest homepage. A business is compared at the speed of a search result, a social click, a map listing, a review snippet and a mobile form. If the site feels slow, vague or disconnected from the user intent, the visitor leaves before the sales conversation begins. The winning businesses are treating their websites and apps as living systems, not one-time design projects.',
                    'Several changes are converging. AI search is teaching users to ask more detailed questions. Ecommerce buyers expect recommendations and delivery clarity. Mobile app users expect edge-to-edge polish, privacy transparency and fewer interruptions. Developers now have better web platform features, but also more responsibility to use them carefully. Security expectations are rising because forms, APIs, payments, accounts and admin panels are all part of the customer experience. A weak technical foundation is now a marketing problem.',
                    'The opportunity is large because many competitors are still adding tools without strategy. They install chat widgets without lead routing, redesign stores without product data, publish AI content without original insight, or launch apps without understanding privacy disclosures. A focused business can move faster by building the fundamentals correctly: clear offers, structured content, fast templates, secure forms, useful automation and reporting that shows which actions are creating enquiries.',
                ],
            ],
            [
                'heading' => 'Search Intent and SEO Essentials',
                'paragraphs' => [
                    'The SEO essentials for ' . $focusKeyword . ' begin with intent. A page should know whether it is educating, comparing, selling, onboarding or supporting. The primary keyword should appear naturally in the title, introduction, headings, metadata and internal links, but the bigger win is topical completeness. Related phrases such as ' . $keywordLine . ' help the page cover the user journey without becoming repetitive. Each section should answer a real question a buyer would ask before contacting a supplier.',
                    'A strong article or service page needs a clean title tag, persuasive meta description, one clear H1, descriptive H2s, internal links to relevant services, compressed media, schema where appropriate and a final conversion action. It should also use specific examples. Search systems are getting better at detecting thin summaries that could apply to any business. A fresh article should include trade-offs, implementation detail, mistakes to avoid and the metrics a team should watch after launch.',
                    'AI search makes structure even more important. When a system summarizes options, it needs entities, facts and relationships it can parse. That means naming the service, audience, location, problem, solution, proof and next action. FAQ sections, comparison tables, checklists and clearly labelled benefits all help. The goal is not to trick an AI result. The goal is to be the most understandable and trustworthy source for a focused commercial question.',
                ],
            ],
            [
                'heading' => 'User Experience and Conversion Design',
                'paragraphs' => [
                    'User experience is where trends become money or noise. The visitor does not care whether a website uses the newest framework if the offer is unclear, the text is hard to scan or the form feels risky. For ' . $audience . ', the design should answer three questions quickly: what is being offered, why should the visitor trust it, and what should they do next? Visual style matters, but it should support clarity rather than hide weak messaging behind decoration.',
                    'Modern conversion design uses strong hierarchy, short forms, proof near decision points, service-specific conversion prompts and fast feedback. A quote calculator, SEO checker, portal preview or automation finder can work well because it lets the visitor participate before sending a project brief. Interactive tools also create better leads because the user reveals intent. The key is to ask for only the information needed, explain what happens next and follow up with relevant service messaging.',
                    'Mobile deserves special attention. Many buyers first experience a business through a mobile screen, even for high-value services. Buttons need stable dimensions, forms need sensible input types, menus need to close cleanly, and important text must not sit under system UI. App-style polish is now expected on the web too. A site that feels calm, fast and predictable builds trust before the visitor reads the case studies.',
                ],
            ],
            [
                'heading' => 'Technical Architecture',
                'paragraphs' => [
                    'The technical architecture behind ' . $focusKeyword . ' should match the business model. A simple service website may need a custom PHP CMS, fast templates, strong SEO fields, secure contact handling and an admin area. A growing ecommerce brand may need product feeds, inventory logic, abandoned checkout workflows and analytics events. A platform may need roles, dashboards, API integrations, audit logs and support tickets. The wrong architecture creates future friction even if the launch looks good.',
                    'A useful planning question is: what needs to change often, and who should be able to change it? Content teams need editable landing pages, FAQs, offers and metadata. Sales teams need lead details and follow-up context. Support teams need ticket history. Developers need structured data rather than fragile page text. When these needs are identified early, the site can be built with reusable components and clean data boundaries.',
                    'Performance should be designed into the architecture, not bolted on later. Image sizes, caching rules, CSS scope, JavaScript budgets, database queries and third-party scripts all affect speed. The same is true for security. CSRF protection, rate limiting, input validation, secure sessions, prepared queries, logging and least-privilege admin access should be part of the build. Good architecture is quiet: users simply feel that everything works.',
                ],
            ],
            [
                'heading' => 'AI and Automation Opportunities',
                'paragraphs' => [
                    'AI is most valuable when it removes delay from a workflow. For ' . $audience . ', that may mean summarizing enquiries, scoring leads, drafting replies, recommending products, generating support notes, preparing weekly reports or routing tickets to the right person. The website becomes the entry point, but the real value appears when the information flows into CRM, email, support, analytics and operations without manual copying.',
                    'The safest automation pattern is human-in-the-loop. Let AI draft, classify, summarize and suggest, while humans approve important messages, pricing, legal claims and strategic decisions. This avoids the brittle feeling of a fully automated business while still saving time. It also protects brand voice. Customers should feel helped, not processed. Automation should make the team more responsive and more consistent.',
                    'Content operations can also become AI-ready. Instead of storing everything as one block of page text, a CMS can use structured fields for services, benefits, FAQs, proof, locations, offers and schema. AI tools can then help draft variants, but the business still controls facts and approvals. This is especially useful for SEO pages because the team can create consistent, location-specific or service-specific content without losing quality control.',
                ],
            ],
            [
                'heading' => 'Security, Privacy and Trust',
                'paragraphs' => [
                    'Trust is now part of the product. A visitor may never inspect the code, but they can sense risk when forms ask for too much, pages load insecurely, checkout feels unfamiliar or privacy language is missing. For ' . $focusKeyword . ', security should be visible enough to reassure users and disciplined enough to protect the business. That includes HTTPS, secure headers, careful authentication, spam protection and clear data handling.',
                    'APIs and admin systems need special attention. Broken authorization, excessive data exposure and weak rate limits can turn a useful feature into a liability. Any system that handles accounts, orders, quotes, tickets, uploaded files or customer records should include validation, permission checks, logging and backup planning. Security is not just a penetration test at the end; it is a series of design decisions made throughout the project.',
                    'Privacy expectations also affect apps and ecommerce. Businesses should understand what data they collect, why they collect it, where it goes and how long it is retained. App stores require privacy disclosures, and customers increasingly expect transparency. A clear privacy posture can become a selling point because it reduces hesitation. The best digital systems collect enough data to serve the customer well, but not so much that the business creates unnecessary risk.',
                ],
            ],
            [
                'heading' => 'Implementation Roadmap',
                'paragraphs' => [
                    'A practical roadmap starts with discovery. List the commercial goal, audience, current friction, search opportunities, technical constraints and required integrations. Then decide which pages, tools or workflows will create the most value first. For ' . $commercialGoal . ', the first release should focus on the shortest path between user intent and a qualified action. Extra features can wait if they do not improve that path.',
                    'The second step is design and content architecture. Create the page templates, navigation, forms, trust blocks, internal links and reusable sections. Write metadata and headings before development so SEO is not rushed at launch. Decide what needs schema, what needs tracking and what needs admin editing. For ecommerce or apps, map the states users will see: empty carts, errors, confirmations, account screens, support requests and follow-up emails.',
                    'The third step is build, test and measure. Development should include responsive QA, accessibility checks, performance budgets, security checks and analytics events. After launch, the team should review search impressions, rankings, form completion, conversion rate, page speed, support volume and lead quality. A website is not finished when it goes live; it becomes more valuable as real data shows where users hesitate and what content attracts the right audience.',
                ],
            ],
            [
                'heading' => 'Common Mistakes to Avoid',
                'paragraphs' => [
                    'The first mistake is treating ' . $focusKeyword . ' as a single plugin, theme or feature. Trends do not create growth by themselves. A business can add AI, redesign a homepage or rebuild a store and still see no improvement if the offer, content, tracking and follow-up are weak. Every feature should have a reason connected to revenue, trust, speed or service quality.',
                    'The second mistake is ignoring content depth. Many websites publish short pages that mention services but do not answer buyer questions. That leaves search engines with little context and visitors with little confidence. Strong content explains who the service is for, what problem it solves, what the process looks like, what decisions matter and how to start. This is especially important as AI search systems compare sources before presenting answers.',
                    'The third mistake is launching without maintenance. Frameworks change, plugins update, search behavior shifts, competitors publish new pages and customer expectations rise. A site needs backups, monitoring, security updates, content improvements and conversion reviews. The most profitable digital assets are maintained like business systems, not abandoned like campaign flyers.',
                ],
            ],
            [
                'heading' => 'Metrics That Prove It Is Working',
                'paragraphs' => [
                    'Good measurement connects technical work to business outcomes. For ' . $audience . ', useful metrics include qualified enquiries, WhatsApp clicks, form completion rate, project briefs, cart conversion, average order value, support resolution time, repeat purchases and organic landing page growth. Traffic alone is not enough. A page with fewer visitors but better intent can produce more revenue than a busy page that attracts the wrong audience.',
                    'Technical metrics still matter because they influence behaviour. Core Web Vitals, uptime, crawl errors, index coverage, page speed, accessibility checks and security scan results show whether the foundation is healthy. If a paid campaign sends users to a slow page, the ad budget is fighting the website. If a support portal is hard to use, customers will return to untracked messages and scattered email threads. Measurement reveals where the system leaks value.',
                    'Reporting should lead to action. A monthly dashboard should not just show numbers; it should explain what changed, what was learned and what will be improved next. That might mean rewriting a service page, adding FAQ schema, compressing images, simplifying a form, improving checkout trust, adjusting PPC message match or creating a new location page. The habit of iteration is what turns a launch into a growth engine.',
                ],
            ],
            [
                'heading' => 'Final Takeaway',
                'paragraphs' => [
                    $focusKeyword . ' is worth paying attention to because it sits at the intersection of user expectations, search visibility and operational efficiency. The businesses that win will not be the ones that chase every trend. They will be the ones that choose the right trend, connect it to a measurable goal, implement it cleanly and keep improving after launch.',
                    'For Crest Web Media, the practical path is to build websites, ecommerce stores, apps and automations as connected systems. That means strong SEO foundations, high-performance interfaces, secure forms and APIs, useful admin tools, clear reporting and AI support where it genuinely reduces friction. The work should feel modern, but more importantly it should produce enquiries, sales, saved time and customer confidence.',
                    'If a business is planning a new build or redesign, the best starting point is a focused audit. Identify the pages that should rank, the actions visitors should take, the workflows that waste staff time and the risks that could damage trust. From there, the roadmap becomes much clearer: build the smallest strong system that can generate results, then improve it with real data.',
                ],
            ],
        ];
    }

    private function articleChecklist(string $focusKeyword, string $trend, string $commercialGoal): array
    {
        return [
            'Define the primary conversion goal before choosing tools or layouts.',
            'Map the focus keyword "' . $focusKeyword . '" to one clear search intent.',
            'Create title tags, meta descriptions, H1s, H2s, FAQs and internal links before launch.',
            'Build reusable sections for proof, process, pricing signals, FAQs and conversion prompts.',
            'Test performance, accessibility, form handling, mobile menus, security headers and analytics events.',
            'Use AI or automation only where it supports ' . $trend . ' and helps ' . $commercialGoal . '.',
            'Review search, conversion, lead quality and technical health every month.',
        ];
    }

    private function articleFaq(string $focusKeyword, string $audience, string $commercialGoal): array
    {
        return [
            ['question' => 'What is the first step with ' . $focusKeyword . '?', 'answer' => 'Start with the business goal, audience intent and current friction. The right technical plan depends on what the website, store or app needs to achieve commercially.'],
            ['question' => 'Does this matter for small businesses?', 'answer' => 'Yes. ' . ucfirst($audience) . ' can often gain faster results because they have fewer layers of approval and can improve pages, offers and workflows quickly.'],
            ['question' => 'How long does implementation take?', 'answer' => 'A focused improvement can take days, while a full website, ecommerce or app build may take weeks. The timeline depends on content, integrations, approvals and testing depth.'],
            ['question' => 'How does Crest Web Media approach this?', 'answer' => 'Crest Web Media connects strategy, design, development, SEO, security and automation so the finished system can ' . $commercialGoal . '.'],
        ];
    }

    public function postBySlug(string $slug): ?array
    {
        foreach ($this->posts() as $post) {
            if ($post['slug'] === $slug) {
                return $post;
            }
        }

        return null;
    }
}

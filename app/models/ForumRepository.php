<?php

declare(strict_types=1);

namespace App\Models;

final class ForumRepository
{
    public function users(): array
    {
        return [
            ['name' => 'Aoife Byrne', 'role' => 'SEO strategist', 'city' => 'Dublin', 'avatar' => 'AB'],
            ['name' => 'Conor Walsh', 'role' => 'WordPress developer', 'city' => 'Cork', 'avatar' => 'CW'],
            ['name' => 'Niamh Kelly', 'role' => 'Ecommerce manager', 'city' => 'Galway', 'avatar' => 'NK'],
            ['name' => 'Liam Murphy', 'role' => 'App founder', 'city' => 'Belfast', 'avatar' => 'LM'],
            ['name' => 'Saoirse Nolan', 'role' => 'Content lead', 'city' => 'Limerick', 'avatar' => 'SN'],
            ['name' => 'Eoin Gallagher', 'role' => 'Security analyst', 'city' => 'Derry', 'avatar' => 'EG'],
            ['name' => 'Meabh Ryan', 'role' => 'UX designer', 'city' => 'Waterford', 'avatar' => 'MR'],
            ['name' => 'Patrick ONeill', 'role' => 'PPC specialist', 'city' => 'Dublin', 'avatar' => 'PO'],
            ['name' => 'Grace Collins', 'role' => 'Local SEO consultant', 'city' => 'Sligo', 'avatar' => 'GC'],
            ['name' => 'Daniel Hughes', 'role' => 'SaaS engineer', 'city' => 'London', 'avatar' => 'DH'],
            ['name' => 'Emma Wilson', 'role' => 'Shopify consultant', 'city' => 'Manchester', 'avatar' => 'EW'],
            ['name' => 'Noah Carter', 'role' => 'AI automation builder', 'city' => 'Boston', 'avatar' => 'NC'],
            ['name' => 'Olivia Martin', 'role' => 'Technical SEO', 'city' => 'New York', 'avatar' => 'OM'],
            ['name' => 'Jack Thompson', 'role' => 'Backend developer', 'city' => 'Birmingham', 'avatar' => 'JT'],
            ['name' => 'Maya Singh', 'role' => 'Conversion analyst', 'city' => 'Berlin', 'avatar' => 'MS'],
            ['name' => 'Alex Novak', 'role' => 'Cloud engineer', 'city' => 'Amsterdam', 'avatar' => 'AN'],
            ['name' => 'Priya Sharma', 'role' => 'AI workflow consultant', 'city' => 'Dublin', 'avatar' => 'PS'],
            ['name' => 'Tom Becker', 'role' => 'Laravel developer', 'city' => 'Hamburg', 'avatar' => 'TB'],
            ['name' => 'Laura Evans', 'role' => 'Growth marketer', 'city' => 'Cardiff', 'avatar' => 'LE'],
            ['name' => 'Ronan Kalia', 'role' => 'Forum moderator', 'city' => 'Shimla', 'avatar' => 'RK'],
        ];
    }

    public function topics(): array
    {
        $topics = [];
        $templates = $this->topicTemplates();
        $users = $this->users();
        $index = 0;

        foreach ($users as $userIndex => $user) {
            for ($slot = 0; $slot < 5; $slot++) {
                $template = $templates[$index % count($templates)];
                $date = (new \DateTimeImmutable('2026-01-08 09:00:00 UTC'))->modify('+' . $index . ' days +' . (($userIndex % 4) * 3) . ' hours');
                $slug = $this->slug($template['title']) . '-' . ($index + 1);
                $topics[] = [
                    'slug' => $slug,
                    'title' => $template['title'],
                    'category' => $template['category'],
                    'excerpt' => $template['excerpt'],
                    'author' => $user,
                    'created_at' => $date->format('c'),
                    'views' => 420 + ($index * 37),
                    'replies' => 3 + ($index % 7),
                    'links' => $template['links'] ?? [],
                    'posts' => $this->postsFor($template, $user, $date, $index),
                ];
                $index++;
            }
        }

        foreach ($this->storedPosts() as $post) {
            foreach ($topics as &$topic) {
                if (($topic['slug'] ?? '') === ($post['topic_slug'] ?? '')) {
                    $topic['posts'][] = $post;
                    $topic['replies'] = count($topic['posts']);
                    break;
                }
            }
            unset($topic);
        }

        usort($topics, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
        return $topics;
    }

    public function topic(string $slug): ?array
    {
        foreach ($this->topics() as $topic) {
            if (($topic['slug'] ?? '') === $slug) {
                return $topic;
            }
        }

        return null;
    }

    public function storePost(string $topicSlug, array $member, string $body): void
    {
        $body = trim(strip_tags($body));
        if (strlen($body) < 20 || strlen($body) > 2500) {
            throw new \RuntimeException('Forum replies must be between 20 and 2500 characters.');
        }

        $posts = $this->storedPosts();
        $posts[] = [
            'topic_slug' => $topicSlug,
            'author' => [
                'name' => (string) ($member['name'] ?? 'Verified member'),
                'role' => 'Verified Crest member',
                'city' => 'Member',
                'avatar' => strtoupper(substr((string) ($member['name'] ?? 'VM'), 0, 1)) . 'M',
            ],
            'body' => $body,
            'created_at' => gmdate('c'),
        ];

        $dir = dirname($this->postsPath());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->postsPath(), json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    public function backlinkList(): array
    {
        return [
            ['name' => 'Golden Pages', 'url' => 'https://www.goldenpages.ie/'],
            ['name' => 'Yelp Ireland', 'url' => 'https://www.yelp.ie/'],
            ['name' => 'Hotfrog Ireland', 'url' => 'https://www.hotfrog.ie/'],
            ['name' => 'Kompass Ireland', 'url' => 'https://ie.kompass.com/'],
            ['name' => 'EUROPAGES Ireland', 'url' => 'https://www.europages.co.uk/companies/ireland.html'],
            ['name' => 'Local Enterprise Office', 'url' => 'https://www.localenterprise.ie/'],
            ['name' => 'Enterprise Ireland', 'url' => 'https://www.enterprise-ireland.com/'],
            ['name' => 'Chambers Ireland', 'url' => 'https://www.chambers.ie/'],
            ['name' => 'Dublin Chamber', 'url' => 'https://www.dublinchamber.ie/'],
            ['name' => 'Cork Chamber', 'url' => 'https://www.corkchamber.ie/'],
            ['name' => 'Galway Chamber', 'url' => 'https://www.galwaychamber.com/'],
            ['name' => 'Limerick Chamber', 'url' => 'https://www.limerickchamber.ie/'],
            ['name' => 'Waterford Chamber', 'url' => 'https://www.waterfordchamber.ie/'],
            ['name' => 'Sligo Chamber', 'url' => 'https://www.sligochamber.ie/'],
            ['name' => 'IBEC', 'url' => 'https://www.ibec.ie/'],
            ['name' => 'ISME', 'url' => 'https://isme.ie/'],
            ['name' => 'Guaranteed Irish', 'url' => 'https://www.guaranteedirish.ie/'],
            ['name' => 'Irish Tech News', 'url' => 'https://irishtechnews.ie/'],
            ['name' => 'Silicon Republic', 'url' => 'https://www.siliconrepublic.com/'],
            ['name' => 'Business Post', 'url' => 'https://www.businesspost.ie/'],
            ['name' => 'ThinkBusiness', 'url' => 'https://www.thinkbusiness.ie/'],
            ['name' => 'Irish Times Business', 'url' => 'https://www.irishtimes.com/business/'],
            ['name' => 'Independent Business', 'url' => 'https://www.independent.ie/business/'],
            ['name' => 'RTE Business', 'url' => 'https://www.rte.ie/news/business/'],
            ['name' => 'TechIreland', 'url' => 'https://www.techireland.org/'],
            ['name' => 'Scale Ireland', 'url' => 'https://www.scaleireland.org/'],
            ['name' => 'Startup Network Europe Dublin', 'url' => 'https://startupnetwork.eu/'],
            ['name' => 'Dogpatch Labs', 'url' => 'https://dogpatchlabs.com/'],
            ['name' => 'Dublin Business School Events', 'url' => 'https://www.dbs.ie/'],
            ['name' => 'Trinity Innovation', 'url' => 'https://www.tcd.ie/innovation/'],
            ['name' => 'UCD Innovation', 'url' => 'https://www.ucd.ie/innovation/'],
            ['name' => 'TU Dublin Hothouse', 'url' => 'https://www.tudublin.ie/connect/innovation-and-enterprise/'],
            ['name' => 'Meetup Dublin Tech', 'url' => 'https://www.meetup.com/find/?location=ie--Dublin&source=EVENTS&keywords=tech'],
            ['name' => 'Eventbrite Ireland Business', 'url' => 'https://www.eventbrite.ie/d/ireland--dublin/business--events/'],
            ['name' => 'IrishJobs Company Profiles', 'url' => 'https://www.irishjobs.ie/'],
            ['name' => 'Jobs.ie Company Listings', 'url' => 'https://www.jobs.ie/'],
            ['name' => 'LinkedIn Company Pages', 'url' => 'https://www.linkedin.com/company/'],
            ['name' => 'Facebook Business Pages', 'url' => 'https://www.facebook.com/business/pages'],
            ['name' => 'Google Business Profile', 'url' => 'https://www.google.com/business/'],
            ['name' => 'Bing Places', 'url' => 'https://www.bingplaces.com/'],
            ['name' => 'Apple Business Connect', 'url' => 'https://businessconnect.apple.com/'],
            ['name' => 'Foursquare Places', 'url' => 'https://location.foursquare.com/'],
            ['name' => 'Trustpilot Business', 'url' => 'https://business.trustpilot.com/'],
            ['name' => 'Clutch Ireland', 'url' => 'https://clutch.co/ie'],
            ['name' => 'DesignRush Ireland', 'url' => 'https://www.designrush.com/agency/website-design-development/ie'],
            ['name' => 'GoodFirms Ireland', 'url' => 'https://www.goodfirms.co/directory/country/top-website-development-companies/ie'],
            ['name' => 'Sortlist Ireland', 'url' => 'https://www.sortlist.com/'],
            ['name' => 'UpCity Ireland', 'url' => 'https://upcity.com/'],
            ['name' => 'Bark Ireland', 'url' => 'https://www.bark.com/en/ie/'],
            ['name' => 'Houzz Ireland', 'url' => 'https://www.houzz.ie/'],
        ];
    }

    private function postsFor(array $template, array $user, \DateTimeImmutable $date, int $index): array
    {
        $users = $this->users();
        $second = $users[($index + 3) % count($users)];
        $third = $users[($index + 7) % count($users)];
        $links = $template['links'] ?? [];
        $linkText = $links ? ' Useful starting points: ' . implode(', ', array_map(static fn (array $link): string => $link['url'], array_slice($links, 0, 4))) : '';

        return [
            [
                'author' => $user,
                'body' => $template['body'] . $linkText,
                'created_at' => $date->format('c'),
            ],
            [
                'author' => $second,
                'body' => 'I would add that quality matters more than raw volume. A clean profile, consistent NAP details, useful landing page and proof screenshots usually beat low-effort directory spam.',
                'created_at' => $date->modify('+7 hours')->format('c'),
            ],
            [
                'author' => $third,
                'body' => 'Good thread. I have seen the best results when the link target is a local service page, not just the homepage. Pair the listing with schema, fast loading and a real offer.',
                'created_at' => $date->modify('+1 day +2 hours')->format('c'),
            ],
        ];
    }

    private function topicTemplates(): array
    {
        $backlinks = $this->backlinkList();

        return [
            [
                'title' => 'Top 50 Irish websites where businesses can create legitimate backlinks',
                'category' => 'SEO',
                'excerpt' => 'A practical list of Irish directories, chambers, business profiles, event platforms and authority sites worth researching.',
                'body' => 'I started this thread for Irish businesses that need clean citation and backlink opportunities. Check whether each site fits your business, avoid spammy submissions, and keep brand details consistent.',
                'links' => $backlinks,
            ],
            [
                'title' => 'Best Core Web Vitals fixes for PHP MySQL websites on Cloudways',
                'category' => 'Performance',
                'excerpt' => 'Varnish, image compression, font loading and query cleanup for practical 100-score targets.',
                'body' => 'The biggest wins I keep seeing are WebP conversion, fewer third-party scripts, Varnish-friendly public pages, lazy sessions and cutting render-blocking font calls.',
                'links' => [['name' => 'PageSpeed Insights', 'url' => 'https://pagespeed.web.dev/'], ['name' => 'WebPageTest', 'url' => 'https://www.webpagetest.org/']],
            ],
            [
                'title' => 'AI automation ideas for small service businesses in Ireland and the UK',
                'category' => 'AI',
                'excerpt' => 'Lead intake, ticket summaries, quote drafts, CRM updates and weekly reporting workflows.',
                'body' => 'The useful automations are usually boring: classify enquiries, draft replies, extract ticket actions, update CRM fields and create weekly SEO/PPC summaries.',
                'links' => [['name' => 'Zapier', 'url' => 'https://zapier.com/'], ['name' => 'Make', 'url' => 'https://www.make.com/']],
            ],
            [
                'title' => 'Secure login forms: captcha, CSRF, rate limits and session rules',
                'category' => 'Security',
                'excerpt' => 'How to harden PHP account forms without making the user experience painful.',
                'body' => 'For PHP sites I would use CSRF tokens, short captcha contexts, IP-aware rate limiting, HttpOnly cookies, strict mode sessions and no sessions on cacheable public pages.',
                'links' => [['name' => 'OWASP Cheat Sheets', 'url' => 'https://cheatsheetseries.owasp.org/']],
            ],
            [
                'title' => 'PPC landing page checklist before spending on Google Ads',
                'category' => 'PPC',
                'excerpt' => 'Message match, speed, tracking, trust blocks, form friction and thank-you-page measurement.',
                'body' => 'Do not send paid traffic to a generic homepage. Build a dedicated page, match the keyword intent, show proof, track forms/calls and test one clear offer first.',
                'links' => [['name' => 'Google Ads', 'url' => 'https://ads.google.com/']],
            ],
            [
                'title' => 'Schema markup that actually helps service pages',
                'category' => 'SEO',
                'excerpt' => 'Service, LocalBusiness, FAQ and Breadcrumb schema patterns for service websites.',
                'body' => 'I like Service schema plus FAQ and Breadcrumb where it is accurate. The important bit is matching visible page content and keeping the JSON-LD clean.',
                'links' => [['name' => 'Schema.org Service', 'url' => 'https://schema.org/Service'], ['name' => 'Rich Results Test', 'url' => 'https://search.google.com/test/rich-results']],
            ],
            [
                'title' => 'WordPress or custom PHP for a business website in 2026',
                'category' => 'Development',
                'excerpt' => 'When to use WordPress, when custom PHP/MySQL is cleaner, and when hybrid CMS makes sense.',
                'body' => 'WordPress is fast for content teams, but custom PHP wins when the site needs bespoke workflows, client portals, tools, strict performance and fewer plugin risks.',
                'links' => [['name' => 'WordPress', 'url' => 'https://wordpress.org/'], ['name' => 'PHP', 'url' => 'https://www.php.net/']],
            ],
            [
                'title' => 'Technical SEO audit workflow for ecommerce stores',
                'category' => 'Ecommerce',
                'excerpt' => 'Indexation, faceted URLs, product schema, speed, filters, canonical tags and collection pages.',
                'body' => 'Ecommerce audits should start with crawl control, collection page intent, product schema, duplicate filters, Core Web Vitals and internal links to money categories.',
                'links' => [['name' => 'Screaming Frog', 'url' => 'https://www.screamingfrog.co.uk/seo-spider/']],
            ],
            [
                'title' => 'Penetration testing checklist for launch week',
                'category' => 'Security',
                'excerpt' => 'Headers, TLS, uploads, auth, admin routes, input handling and backup verification.',
                'body' => 'My launch checklist covers security headers, TLS dates, upload execution blocks, admin rate limits, SQL parameterization, backup restore tests and error log review.',
                'links' => [['name' => 'Mozilla Observatory', 'url' => 'https://observatory.mozilla.org/'], ['name' => 'Security Headers', 'url' => 'https://securityheaders.com/']],
            ],
            [
                'title' => 'Best tech stack for a client portal with tickets and SEO reports',
                'category' => 'Development',
                'excerpt' => 'Roles, ticket objects, report snapshots, file uploads, notifications and admin moderation.',
                'body' => 'Start with the data model: clients, users, tickets, report snapshots, files, invoices and activity logs. The UI should make support history easy to scan.',
                'links' => [['name' => 'Laravel', 'url' => 'https://laravel.com/'], ['name' => 'MySQL', 'url' => 'https://www.mysql.com/']],
            ],
        ];
    }

    private function storedPosts(): array
    {
        $path = $this->postsPath();
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    private function postsPath(): string
    {
        return base_path('storage/data/forum-posts.json');
    }

    private function slug(string $value): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? '');
        return trim($slug, '-') ?: 'topic';
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\MemberRepository;
use App\Models\NewsletterOfferRepository;
use App\Models\PayPalSettingsRepository;
use App\Models\ToolLeadRepository;
use App\Models\ToolUsageRepository;
use App\Services\AuditLogger;
use App\Services\LeadMailer;

final class ToolsController extends Controller
{
    public function hub(): void
    {
        $this->render('pages/tools', [
            'title' => 'Free Website Growth Tools | Crest Web Media',
            'metaDescription' => 'Explore free SEO, SERP, PPC, AI, speed, security and website growth tools from Crest Web Media.',
            'tools' => $this->toolDirectory(),
        ]);
    }

    public function index(array $data = []): void
    {
        $this->render('pages/security-tools', array_replace([
            'title' => 'Free Penetration Testing Tools | Crest Web Media',
            'metaDescription' => 'Free defensive website security tools for headers, DNS, TLS, security.txt, passwords, hashes and JWT decoding.',
        ], $this->toolAccessData(), $data));
    }

    public function seoTools(array $data = []): void
    {
        $this->render('pages/seo-tools', array_replace([
            'title' => 'Free On-Page SEO Audit Tool (28+ Checks) | Crest Web Media',
            'metaDescription' => 'The deepest free on-page & technical SEO audit in Ireland: 28+ weighted checks — titles, meta, heading hierarchy, schema, Core Web Vitals, readability, keyword & content analysis — with a prioritised fix list and white-label PDF export.',
        ], $this->toolAccessData(), $data));
    }

    public function serpChecker(array $data = []): void
    {
        $this->render('pages/serp-checker', array_replace([
            'title' => 'Free SERP Checker | Crest Web Media',
            'metaDescription' => 'Free SERP checker for keyword visibility, ranking position, competitor pages and search result opportunities.',
        ], $this->toolAccessData(), $data));
    }

    public function securityHeaders(array $data = []): void
    {
        $this->render('pages/tool-security-headers', array_replace([
            'title' => 'Free Security Headers Checker | Crest Web Media',
            'metaDescription' => 'Scan HTTP security headers — HSTS, CSP, COOP, CORP, frame and MIME protection, cookie flags and stack disclosure — and download a white-label PDF report.',
        ], $this->toolAccessData(), $data));
    }

    public function dnsEmail(array $data = []): void
    {
        $this->render('pages/tool-dns-email', array_replace([
            'title' => 'Free DNS & Email Security Checker | Crest Web Media',
            'metaDescription' => 'Check MX, SPF, DMARC, CAA and nameserver records for email deliverability and spoofing protection, with a downloadable white-label report.',
        ], $this->toolAccessData(), $data));
    }

    public function tlsSsl(array $data = []): void
    {
        $this->render('pages/tool-tls', array_replace([
            'title' => 'Free TLS / SSL Certificate Checker | Crest Web Media',
            'metaDescription' => 'Read TLS/SSL certificate issuer, subject, validity and expiry health, and download a white-label PDF report.',
        ], $this->toolAccessData(), $data));
    }

    public function securityTxt(array $data = []): void
    {
        $this->render('pages/tool-security-txt', array_replace([
            'title' => 'Free security.txt & Robots Discovery | Crest Web Media',
            'metaDescription' => 'Discover security.txt and robots.txt disclosure files and download a white-label report.',
        ], $this->toolAccessData(), $data));
    }

    public function techStack(array $data = []): void
    {
        $this->render('pages/tool-tech-stack', array_replace([
            'title' => 'Free Website Technology Checker | Crest Web Media',
            'metaDescription' => 'Detect the CMS, frameworks, JavaScript libraries, analytics, CDN and server behind any website, and download a white-label report.',
        ], $this->toolAccessData(), $data));
    }

    public function analyzeTechStack(): void
    {
        if (!$this->hasToolAccess()) {
            $this->techStack(['accessError' => 'Register and verify your email to reveal technology results.']);
            return;
        }

        if (Security::hitRateLimit('tech_stack', 10, 900)) {
            http_response_code(429);
            $this->techStack(['toolError' => 'Too many scans. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->techStack(['toolError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $url = $this->normalizePublicUrl((string) ($_POST['target_url'] ?? ''));
        if ($url === null) {
            http_response_code(422);
            $this->techStack(['toolError' => 'Enter a public HTTPS or HTTP URL. Private networks, localhost, credentials and custom ports are blocked for safety.']);
            return;
        }

        if (!$this->consumeFreeScan('tech_stack')) {
            http_response_code(402);
            $this->techStack(['toolError' => $this->growthLabLimitMessage()]);
            return;
        }

        $headers = $this->fetchHeaders($url);
        $html = $this->fetchHtml($url);
        if ($headers === [] && $html === '') {
            $this->techStack(['toolError' => 'Could not read that site. Try the exact homepage URL.']);
            return;
        }

        (new AuditLogger())->log('tools.tech_stack_analyzed', ['host' => parse_url($url, PHP_URL_HOST)]);
        $this->techStack([
            'targetUrl' => $url,
            'report' => $this->detectTech($url, $headers, $html),
        ]);
    }

    public function pagespeed(array $data = []): void
    {
        $this->render('pages/tool-pagespeed', array_replace([
            'title' => 'Free PageSpeed & Core Web Vitals Checker | Crest Web Media',
            'metaDescription' => 'Check real Google PageSpeed and Core Web Vitals (LCP, CLS, TBT) for any page, mobile-first, with a downloadable white-label report.',
        ], $this->toolAccessData(), $data));
    }

    public function analyzePagespeed(): void
    {
        if (!$this->hasToolAccess()) {
            $this->pagespeed(['accessError' => 'Register and verify your email to reveal PageSpeed results.']);
            return;
        }

        if (Security::hitRateLimit('pagespeed', 8, 900)) {
            http_response_code(429);
            $this->pagespeed(['toolError' => 'Too many PageSpeed checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->pagespeed(['toolError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $url = $this->normalizePublicUrl((string) ($_POST['target_url'] ?? ''));
        if ($url === null) {
            http_response_code(422);
            $this->pagespeed(['toolError' => 'Enter a public HTTPS or HTTP URL. Private networks, localhost, credentials and custom ports are blocked for safety.']);
            return;
        }

        if (!$this->consumeFreeScan('pagespeed')) {
            http_response_code(402);
            $this->pagespeed(['toolError' => $this->growthLabLimitMessage()]);
            return;
        }

        $report = $this->pagespeedReport($url);
        if ($report === null) {
            $this->pagespeed(['toolError' => 'Could not get PageSpeed data for that URL right now. Google may be busy — try again in a moment.']);
            return;
        }

        (new AuditLogger())->log('tools.pagespeed_analyzed', ['host' => parse_url($url, PHP_URL_HOST)]);
        $this->pagespeed(['targetUrl' => $url, 'report' => $report]);
    }

    /**
     * Real Google PageSpeed Insights (Lighthouse + field CrUX data).
     * Works without an API key at low quota; a GOOGLE_PSI_KEY env var or admin
     * setting raises the limit. Never stores the key in code.
     *
     * @return array<string, mixed>|null
     */
    private function pagespeedReport(string $url): ?array
    {
        $params = [
            'url' => $url,
            'strategy' => 'mobile',
            'category' => 'performance',
        ];
        $key = $this->psiKey();
        if ($key !== '') {
            $params['key'] = $key;
        }
        $endpoint = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?' . http_build_query($params);

        $context = stream_context_create([
            'http' => ['method' => 'GET', 'timeout' => 25, 'ignore_errors' => true, 'header' => "Accept: application/json\r\n"],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $raw = @file_get_contents($endpoint, false, $context, 0, 3000000);
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $this->parsePagespeed($data, $url) : null;
    }

    /**
     * @param array<string, mixed> $data Raw PageSpeed Insights v5 response.
     * @return array<string, mixed>|null
     */
    private function parsePagespeed(array $data, string $url): ?array
    {
        $lh = $data['lighthouseResult'] ?? null;
        if (!is_array($lh)) {
            return null;
        }

        $score = (int) round(((float) ($lh['categories']['performance']['score'] ?? 0)) * 100);
        $audits = is_array($lh['audits'] ?? null) ? $lh['audits'] : [];

        $disp = static fn (string $k): string => (string) ($audits[$k]['displayValue'] ?? 'n/a');
        $num = static fn (string $k) => $audits[$k]['numericValue'] ?? null;

        $lcpMs = $num('largest-contentful-paint');
        $clsVal = $num('cumulative-layout-shift');
        $tbtMs = $num('total-blocking-time');

        // Field (real-user CrUX) data when available.
        $field = $data['loadingExperience']['metrics'] ?? [];
        $fieldLabel = static function (array $field, string $metric): ?string {
            $cat = $field[$metric]['category'] ?? null;
            return $cat ? ucfirst(strtolower(str_replace('_', ' ', (string) $cat))) : null;
        };
        $fieldLcp = $fieldLabel($field, 'LARGEST_CONTENTFUL_PAINT_MS');
        $fieldInp = $fieldLabel($field, 'INTERACTION_TO_NEXT_PAINT') ?? $fieldLabel($field, 'EXPERIMENTAL_INTERACTION_TO_NEXT_PAINT');

        $facts = [
            ['label' => 'LCP', 'value' => $disp('largest-contentful-paint')],
            ['label' => 'CLS', 'value' => $disp('cumulative-layout-shift')],
            ['label' => 'Total Blocking Time', 'value' => $disp('total-blocking-time')],
            ['label' => 'First Contentful Paint', 'value' => $disp('first-contentful-paint')],
            ['label' => 'Speed Index', 'value' => $disp('speed-index')],
            ['label' => 'Time to Interactive', 'value' => $disp('interactive')],
        ];
        if ($fieldLcp !== null) {
            $facts[] = ['label' => 'Real-user LCP', 'value' => $fieldLcp];
        }
        if ($fieldInp !== null) {
            $facts[] = ['label' => 'Real-user INP', 'value' => $fieldInp];
        }

        $checks = [
            ['label' => 'Performance score (mobile)', 'present' => $score >= 90, 'value' => $score . ' / 100', 'advice' => 'Aim for 90+; work through the opportunities below, biggest first.'],
            ['label' => 'Largest Contentful Paint (LCP)', 'present' => $lcpMs !== null && $lcpMs <= 2500, 'value' => $disp('largest-contentful-paint') . ' (good ≤ 2.5s)', 'advice' => 'Optimise and preload the hero image, cut server response time and render-blocking CSS.'],
            ['label' => 'Cumulative Layout Shift (CLS)', 'present' => $clsVal !== null && $clsVal <= 0.1, 'value' => $disp('cumulative-layout-shift') . ' (good ≤ 0.1)', 'advice' => 'Set width/height on images and reserve space for ads, embeds and late-loading elements.'],
            ['label' => 'Total Blocking Time (INP proxy)', 'present' => $tbtMs !== null && $tbtMs <= 200, 'value' => $disp('total-blocking-time') . ' (good ≤ 200ms)', 'advice' => 'Reduce and defer JavaScript, remove unused third-party scripts and split long tasks.'],
        ];

        // Top opportunities → recommendations.
        $recommendations = [];
        foreach ($audits as $audit) {
            if (!is_array($audit)) {
                continue;
            }
            $isOpportunity = (($audit['details']['type'] ?? '') === 'opportunity');
            $auditScore = $audit['score'] ?? null;
            if ($isOpportunity && $auditScore !== null && $auditScore < 0.9 && !empty($audit['title'])) {
                $save = $audit['displayValue'] ?? '';
                $recommendations[] = trim($audit['title'] . ($save !== '' ? ' — ' . $save : ''));
            }
        }
        if ($recommendations === []) {
            $recommendations[] = 'No major opportunities flagged — keep images optimised, scripts lean and hosting fast.';
        }

        return [
            'tool' => 'PageSpeed & Core Web Vitals',
            'icon' => 'fa-gauge-high',
            'target' => $url,
            'score' => $score,
            'summary' => 'Real Google Lighthouse performance and Core Web Vitals (mobile).',
            'facts' => $facts,
            'checks' => $checks,
            'recommendations' => array_slice($recommendations, 0, 8),
        ];
    }

    private function psiKey(): string
    {
        $env = (string) (getenv('GOOGLE_PSI_KEY') ?: '');
        if ($env !== '') {
            return trim($env);
        }

        try {
            $settings = (new PayPalSettingsRepository())->current();
            return trim((string) ($settings['google_psi_key'] ?? ''));
        } catch (\Throwable) {
            return '';
        }
    }

    public function toolsPricing(): void
    {
        $plans = (new \App\Models\MembershipPlanRepository())->activePlans();

        $this->render('pages/tools-pricing', [
            'title' => 'Growth Lab Pro — Website Tools Membership | Crest Web Media',
            'metaDescription' => 'Unlimited SEO, security and technical website tools with white-label PDF reports. €25/month or €200/year. Cancel anytime.',
            'plans' => $plans,
            'planRepo' => new \App\Models\MembershipPlanRepository(),
            'csrf' => Security::csrfToken(),
        ]);
    }

    public function growthConsultant(): void
    {
        $this->interactiveTool('growth-consultant');
    }

    public function quoteCalculator(): void
    {
        $this->interactiveTool('quote-calculator');
    }

    public function securityBadge(): void
    {
        $this->interactiveTool('security-badge');
    }

    /**
     * Embeddable "Powered by Crest" security badge (GET /badge.svg?grade=A).
     * Served as a cacheable SVG; each request logs the referring host so badge
     * adoption can be tracked from the activity log. Grade is whitelisted.
     */
    public function badgeSvg(): void
    {
        $grade = strtoupper(substr(trim((string) ($_GET['grade'] ?? 'A')), 0, 1));
        if (!in_array($grade, ['A', 'B', 'C', 'D', 'F'], true)) {
            $grade = 'A';
        }

        $colors = [
            'A' => '#19f79a',
            'B' => '#00e5ff',
            'C' => '#ffc53d',
            'D' => '#ff9f43',
            'F' => '#ff4d81',
        ];
        $color = $colors[$grade];

        $referer = parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST);
        if (is_string($referer) && $referer !== '') {
            (new AuditLogger())->log('badge.impression', ['host' => substr($referer, 0, 190), 'grade' => $grade]);
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="178" height="40" role="img" aria-label="Security ' . $grade . ' — Crest Web Media">'
            . '<rect width="178" height="40" rx="8" fill="#060c18"/>'
            . '<rect x="0.5" y="0.5" width="177" height="39" rx="7.5" fill="none" stroke="' . $color . '" stroke-opacity="0.55"/>'
            . '<path d="M20 8l9 4v6c0 6-3.9 10.3-9 12-5.1-1.7-9-6-9-12v-6l9-4z" fill="none" stroke="' . $color . '" stroke-width="1.6"/>'
            . '<text x="20" y="25.5" font-family="Segoe UI,Arial,sans-serif" font-size="12" font-weight="700" fill="' . $color . '" text-anchor="middle">' . $grade . '</text>'
            . '<text x="38" y="17" font-family="Segoe UI,Arial,sans-serif" font-size="9" fill="#9db4d0" letter-spacing="1.2">SECURITY CHECKED</text>'
            . '<text x="38" y="30" font-family="Segoe UI,Arial,sans-serif" font-size="11" font-weight="700" fill="#f3f8ff">Crest Web Media</text>'
            . '</svg>';

        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Cache-Control: public, max-age=86400, s-maxage=86400');
        echo $svg;
    }

    public function clientPortalPreview(): void
    {
        $this->interactiveTool('client-portal');
    }

    public function ppcRoiCalculator(): void
    {
        $this->interactiveTool('ppc-roi');
    }

    public function speedSimulator(): void
    {
        $this->interactiveTool('speed-simulator');
    }

    public function automationFinder(): void
    {
        $this->interactiveTool('automation-finder');
    }

    private function toolDirectory(): array
    {
        return [
            [
                'title' => 'SEO Audit Tool',
                'url' => '/seo-tools',
                'icon' => 'fa-solid fa-chart-line',
                'category' => 'SEO',
                'summary' => 'Check titles, meta descriptions, headings, schema, content depth, links, images and indexability signals.',
                'accent' => 'cyan',
            ],
            [
                'title' => 'SERP Checker',
                'url' => '/serp-checker',
                'icon' => 'fa-solid fa-ranking-star',
                'category' => 'Rankings',
                'summary' => 'Review keyword visibility, competitor pages and ranking opportunities before planning content or campaigns.',
                'accent' => 'violet',
            ],
            [
                'title' => 'AI Content Assistant',
                'url' => '/ai-content-assistant',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'category' => 'Content',
                'summary' => 'Generate SEO meta descriptions, title tags, blog outlines, FAQs, product copy, social posts and CTAs from a single keyword.',
                'accent' => 'violet',
            ],
            [
                'title' => 'Ethical Hacking Toolkit',
                'url' => '/ethical-hacking-toolkit',
                'icon' => 'fa-solid fa-shield-halved',
                'category' => 'Security',
                'summary' => 'Defensive self-audit: scan for exposed .env, .git, backups and phpinfo, plus headers, TLS, DNS and cookie hardening in one place.',
                'accent' => 'green',
            ],
            [
                'title' => 'Security Headers Checker',
                'url' => '/tools/security-headers',
                'icon' => 'fa-solid fa-lock',
                'category' => 'Security',
                'summary' => 'Score HSTS, CSP, COOP, CORP, frame and MIME protection, cookie flags and stack disclosure.',
                'accent' => 'green',
            ],
            [
                'title' => 'DNS & Email Security',
                'url' => '/tools/dns-email',
                'icon' => 'fa-solid fa-envelope-circle-check',
                'category' => 'Security',
                'summary' => 'Check MX, SPF, DMARC, CAA and nameservers for deliverability and anti-spoofing.',
                'accent' => 'green',
            ],
            [
                'title' => 'TLS / SSL Certificate',
                'url' => '/tools/tls-ssl',
                'icon' => 'fa-solid fa-certificate',
                'category' => 'Security',
                'summary' => 'Read certificate issuer, subject, validity window and days remaining to expiry.',
                'accent' => 'green',
            ],
            [
                'title' => 'Website Technology Checker',
                'url' => '/tools/tech-stack',
                'icon' => 'fa-solid fa-microchip',
                'category' => 'Intelligence',
                'summary' => 'Detect the CMS, frameworks, libraries, analytics, CDN and server behind any website.',
                'accent' => 'cyan',
            ],
            [
                'title' => 'security.txt & Discovery',
                'url' => '/tools/security-txt',
                'icon' => 'fa-solid fa-file-shield',
                'category' => 'Security',
                'summary' => 'Discover responsible-disclosure and crawler files exposed over HTTPS.',
                'accent' => 'green',
            ],
            [
                'title' => 'AI Website Growth Consultant',
                'url' => '/ai-website-growth-consultant',
                'icon' => 'fa-solid fa-brain',
                'category' => 'AI',
                'summary' => 'Generate a practical growth plan covering SEO, PPC, AI automation, conversion fixes and development.',
                'accent' => 'violet',
            ],
            [
                'title' => 'Quote Calculator',
                'url' => '/instant-website-quote-calculator',
                'icon' => 'fa-solid fa-calculator',
                'category' => 'Planning',
                'summary' => 'Estimate scope for websites, ecommerce, apps, portals, SEO, PPC, AI automation and support retainers.',
                'accent' => 'cyan',
            ],
            [
                'title' => 'PPC ROI Calculator',
                'url' => '/ppc-roi-calculator',
                'icon' => 'fa-solid fa-bullseye',
                'category' => 'Paid Search',
                'summary' => 'Model ad budget, CPC, landing-page conversion rate, revenue potential and campaign return.',
                'accent' => 'green',
            ],
            [
                'title' => 'PageSpeed & Core Web Vitals',
                'url' => '/tools/pagespeed',
                'icon' => 'fa-solid fa-gauge-high',
                'category' => 'Performance',
                'summary' => 'Real Google Lighthouse performance and Core Web Vitals (LCP, CLS, blocking time) for any page.',
                'accent' => 'cyan',
            ],
            [
                'title' => 'Website Speed Simulator',
                'url' => '/before-after-speed-simulator',
                'icon' => 'fa-solid fa-gauge-high',
                'category' => 'Performance',
                'summary' => 'Compare before and after performance scores and estimate how faster pages can lift leads.',
                'accent' => 'cyan',
            ],
            [
                'title' => 'AI Automation Finder',
                'url' => '/ai-automation-finder',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'category' => 'Automation',
                'summary' => 'Find repeatable workflows that can be improved with AI-assisted intake, routing, summaries and actions.',
                'accent' => 'violet',
            ],
            [
                'title' => 'Client Portal Preview',
                'url' => '/client-portal-preview',
                'icon' => 'fa-solid fa-table-columns',
                'category' => 'Portal',
                'summary' => 'Preview a premium client portal experience for tickets, reports, rankings, milestones, files and support.',
                'accent' => 'green',
            ],
        ];
    }

    public function sendAccessCode(): void
    {
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->redirectToTool('Access token expired. Please try again.', false);
        }

        if (Security::hitRateLimit('tool_access_code', 5, 900)) {
            http_response_code(429);
            $this->redirectToTool('Too many code requests. Please wait a few minutes.', false);
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            $_SESSION['pending_tool_lead'] = ['name' => $name, 'email' => $email];
            $this->redirectToTool('Enter a valid name and email address.', false);
        }

        $code = (string) random_int(100000, 999999);
        $_SESSION['tool_access_pending'] = [
            'name' => $name,
            'email' => $email,
            'code_hash' => password_hash($code, PASSWORD_DEFAULT),
            'expires_at' => time() + 900,
        ];
        $_SESSION['pending_tool_lead'] = ['name' => $name, 'email' => $email];

        // Capture the lead the moment a code is requested, so it lands in the
        // backend even if the visitor never verifies (or email delivery fails).
        try {
            (new ToolLeadRepository())->store(['name' => $name, 'email' => $email, 'source' => 'tool-access-requested']);
        } catch (\Throwable) {
            // non-fatal
        }

        $sent = false;
        try {
            $result = (new LeadMailer())->sendCode($email, $name, $code);
            $sent = is_array($result) ? (bool) ($result['ok'] ?? false) : (bool) $result;
        } catch (\Throwable) {
            $sent = false;
        }
        (new AuditLogger())->log('tools.access_code_sent', ['email' => $email, 'sent' => $sent]);

        $this->redirectToTool(
            $sent
                ? 'Code sent. Check your email (and spam folder) and enter it below.'
                : 'We could not email your code right now. Please contact us or create a free account to sign in instead.',
            $sent
        );
    }

    public function verifyAccessCode(): void
    {
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->redirectToTool('Access token expired. Please try again.', false);
        }

        $pending = $_SESSION['tool_access_pending'] ?? null;
        $code = trim((string) ($_POST['code'] ?? ''));
        if (!is_array($pending) || time() > (int) ($pending['expires_at'] ?? 0) || !password_verify($code, (string) ($pending['code_hash'] ?? ''))) {
            http_response_code(422);
            $this->redirectToTool('Invalid or expired sign-in code.', false);
        }

        $lead = [
            'name' => (string) $pending['name'],
            'email' => (string) $pending['email'],
            'source' => 'tool-access',
        ];
        $_SESSION['tool_lead'] = $lead + ['verified_at' => time()];
        unset($_SESSION['tool_access_pending'], $_SESSION['pending_tool_lead']);

        (new ToolLeadRepository())->store($lead);
        (new LeadMailer())->sendOffer($lead['email'], $lead['name'], (new NewsletterOfferRepository())->current());
        (new AuditLogger())->log('tools.access_verified', ['email' => $lead['email']]);

        $this->redirectToTool('Verified. Results are unlocked and the offer email has been sent.', true);
    }

    public function auditSeo(): void
    {
        if (!$this->hasToolAccess()) {
            $this->seoTools(['accessError' => 'Register and verify your email to reveal SEO audit results.']);
            return;
        }

        if (Security::hitRateLimit('seo_audit', 10, 900)) {
            http_response_code(429);
            $this->seoTools(['error' => 'Too many SEO audits. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->seoTools(['error' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $url = $this->normalizePublicUrl((string) ($_POST['target_url'] ?? ''));
        $keyword = trim((string) ($_POST['keyword'] ?? ''));
        if ($url === null) {
            http_response_code(422);
            $this->seoTools(['error' => 'Enter a public HTTPS or HTTP URL. Private networks, localhost, credentials and custom ports are blocked for safety.']);
            return;
        }

        if (!$this->consumeFreeScan('seo_audit')) {
            http_response_code(402);
            $this->seoTools(['error' => $this->growthLabLimitMessage()]);
            return;
        }

        $html = $this->fetchHtml($url);
        if ($html === '') {
            $this->seoTools(['error' => 'Could not read that page. Try the exact page URL.']);
            return;
        }

        (new AuditLogger())->log('tools.seo_audited', ['host' => parse_url($url, PHP_URL_HOST)]);
        $seoResult = $this->seoAudit($url, $html, $keyword);
        $report = [
            'tool' => 'On-Page SEO',
            'icon' => 'fa-chart-line',
            'target' => $url,
            'score' => $seoResult['score'],
            'summary' => 'On-page and technical SEO signals for ' . ($keyword !== '' ? '“' . $keyword . '”' : 'this page') . '.',
            'facts' => [
                ['label' => 'Words', 'value' => (string) $seoResult['words']],
                ['label' => 'Readability', 'value' => $seoResult['readability'] . ' (' . $seoResult['reading_label'] . ')'],
                ['label' => 'Schema', 'value' => $seoResult['schema_types'] !== [] ? implode(', ', array_slice($seoResult['schema_types'], 0, 3)) : 'None'],
                ['label' => 'Internal / external links', 'value' => $seoResult['internal_links'] . ' / ' . $seoResult['external_links']],
                ['label' => 'Images', 'value' => (string) ($seoResult['image_count'] ?? 0)],
                ['label' => 'Page weight', 'value' => $seoResult['page_weight_kb'] . ' KB'],
                ['label' => 'Content ratio', 'value' => $seoResult['content_ratio'] . '%'],
                ['label' => 'Keyword density', 'value' => ($seoResult['keyword_density'] ?? 0) . '%'],
            ],
            'checks' => $seoResult['checks'],
        ];

        $emailed = $this->emailAuditReport($report, '/seo-tools');

        $this->seoTools([
            'targetUrl' => $url,
            'keyword' => $keyword,
            'seoResult' => $seoResult,
            'report' => $report,
            'reportEmailed' => $emailed,
        ]);
    }

    /**
     * Auto-emails a copy of an audit report to the verified tool-access email,
     * so the person keeps the findings. Throttled to once per host per 6 hours
     * (per session) so repeat scans don't spam. Entirely best-effort: a missing
     * SMTP setup never affects the on-screen result.
     *
     * @param array<string, mixed> $report
     */
    private function emailAuditReport(array $report, string $toolPath = '/seo-tools'): bool
    {
        Security::ensureSession();
        $email = (string) ($_SESSION['tool_lead']['email'] ?? '');
        $name = (string) ($_SESSION['tool_lead']['name'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $host = (string) (parse_url((string) ($report['target'] ?? ''), PHP_URL_HOST) ?: ($report['target'] ?? ''));
        $key = strtolower(($report['tool'] ?? '') . '|' . $host);
        $recent = $_SESSION['audit_emailed'] ?? [];
        if (isset($recent[$key]) && (time() - (int) $recent[$key]) < 21600) {
            return false; // already emailed this host recently
        }

        $reportUrl = rtrim((string) ($this->config['url'] ?? ''), '/') . $toolPath;
        try {
            $sent = (new LeadMailer())->sendAuditReport($email, $name, $report, $reportUrl);
        } catch (\Throwable) {
            $sent = false;
        }

        $recent[$key] = time();
        $_SESSION['audit_emailed'] = $recent;

        return $sent;
    }

    public function checkSerp(): void
    {
        if (!$this->hasToolAccess()) {
            $this->serpChecker(['accessError' => 'Register and verify your email to reveal SERP results.']);
            return;
        }

        if (Security::hitRateLimit('serp_checker', 8, 900)) {
            http_response_code(429);
            $this->serpChecker(['error' => 'Too many SERP checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->serpChecker(['error' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $keyword = trim((string) ($_POST['keyword'] ?? ''));
        $target = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? 'Ireland'));

        if (strlen($keyword) < 2 || strlen($keyword) > 120 || $target === null) {
            http_response_code(422);
            $this->serpChecker(['error' => 'Enter a keyword and a valid public domain.']);
            return;
        }

        if (!$this->consumeFreeScan('serp_checker')) {
            http_response_code(402);
            $this->serpChecker(['error' => $this->growthLabLimitMessage()]);
            return;
        }

        $report = $this->serpReport($keyword, $target, $location);
        (new AuditLogger())->log('tools.serp_checked', ['keyword' => $keyword, 'target' => $target, 'location' => $location]);

        $this->serpChecker([
            'keyword' => $keyword,
            'domain' => $target,
            'location' => $location,
            'serpResult' => $report,
        ]);
    }

    public function ethicalHackingTools(array $data = []): void
    {
        $this->render('pages/ethical-hacking-tools', array_replace([
            'title' => 'Ethical Hacking Tools Library (Pro) | Crest Web Media',
            'metaDescription' => 'A curated portal of the best free, open-source ethical hacking and penetration-testing tools for websites — with official download links, what each tool does and step-by-step usage guides. Growth Lab Pro members only.',
            'toolCatalog' => $this->ethicalHackingCatalog(),
        ], $this->toolAccessData(), $data));
    }

    /**
     * Curated catalogue of well-known, free / open-source security tools. We link
     * to each project's OFFICIAL download page rather than re-hosting binaries —
     * that keeps downloads authentic and avoids redistributing security software.
     *
     * @return array<int, array<string, mixed>>
     */
    private function ethicalHackingCatalog(): array
    {
        return [
            [
                'category' => 'Recon & OSINT',
                'color' => '#00e5ff',
                'items' => [
                    [
                        'name' => 'Nmap',
                        'tagline' => 'Network & port scanner',
                        'license' => 'Open source (NPSL)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'The industry-standard scanner for discovering live hosts, open ports, running services and OS fingerprints. The first step of almost every assessment.',
                        'features' => ['Host discovery & port scanning', 'Service/version detection', 'Nmap Scripting Engine (NSE)', 'OS fingerprinting'],
                        'steps' => ['Install, then confirm scope you are authorised to test.', 'Discover open ports & services: nmap -sV example.com', 'Run safe vuln scripts: nmap --script vuln example.com', 'Review the report and prioritise exposed services.'],
                        'download' => 'https://nmap.org/download.html',
                        'docs' => 'https://nmap.org/book/man.html',
                    ],
                    [
                        'name' => 'theHarvester',
                        'tagline' => 'Email & subdomain OSINT',
                        'license' => 'Open source (GPL)',
                        'platforms' => 'Cross-platform (Python)',
                        'what' => 'Gathers emails, subdomains, hosts and names from public sources (search engines, certificate transparency) to map an organisation\'s footprint.',
                        'features' => ['Subdomain enumeration', 'Email harvesting', 'Multiple public data sources', 'Passive — no packets to the target'],
                        'steps' => ['pip install theHarvester', 'theHarvester -d example.com -b all', 'Feed discovered subdomains into your scanner.', 'Remove any exposed staging/admin hosts you find.'],
                        'download' => 'https://github.com/laramies/theHarvester',
                        'docs' => 'https://github.com/laramies/theHarvester#readme',
                    ],
                ],
            ],
            [
                'category' => 'Web Application Testing',
                'color' => '#ff6b8b',
                'items' => [
                    [
                        'name' => 'OWASP ZAP',
                        'tagline' => 'Web app vulnerability scanner',
                        'license' => 'Free / open source',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'The OWASP flagship web proxy and scanner: intercept traffic, spider a site and run automated scans for XSS, injection and misconfiguration. The best free Burp alternative.',
                        'features' => ['Intercepting proxy', 'Automated active/passive scans', 'AJAX spider', 'Scriptable & CI-friendly'],
                        'steps' => ['Install and set your browser to use ZAP\'s proxy.', 'Browse the target so ZAP maps it (spider).', 'Run an Active Scan on your own site only.', 'Triage alerts by risk and fix highest first.'],
                        'download' => 'https://www.zaproxy.org/download/',
                        'docs' => 'https://www.zaproxy.org/getting-started/',
                    ],
                    [
                        'name' => 'Burp Suite Community',
                        'tagline' => 'Web proxy & tester',
                        'license' => 'Free (Community Edition)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'The most widely used web-security toolkit. The free Community Edition gives you the intercepting proxy, repeater and decoder for manual testing.',
                        'features' => ['Intercepting proxy', 'Repeater for crafting requests', 'Decoder & comparer', 'Huge extension ecosystem'],
                        'steps' => ['Install and launch a temporary project.', 'Proxy your browser through Burp (127.0.0.1:8080).', 'Capture a request and send it to Repeater.', 'Modify inputs to test validation on your own app.'],
                        'download' => 'https://portswigger.net/burp/communitydownload',
                        'docs' => 'https://portswigger.net/burp/documentation',
                    ],
                    [
                        'name' => 'Nikto',
                        'tagline' => 'Web server scanner',
                        'license' => 'Open source (GPL)',
                        'platforms' => 'Cross-platform (Perl)',
                        'what' => 'Fast web-server scanner that checks for thousands of dangerous files, outdated software and common server misconfigurations.',
                        'features' => ['6700+ dangerous-file checks', 'Outdated-component detection', 'Server misconfig checks', 'Plain-text or HTML reports'],
                        'steps' => ['Install via your package manager or clone from GitHub.', 'nikto -h https://example.com', 'Review flagged files & headers.', 'Remove leftover files and patch old software.'],
                        'download' => 'https://github.com/sullo/nikto',
                        'docs' => 'https://github.com/sullo/nikto/wiki',
                    ],
                    [
                        'name' => 'sqlmap',
                        'tagline' => 'SQL injection testing',
                        'license' => 'Open source (GPL)',
                        'platforms' => 'Cross-platform (Python)',
                        'what' => 'Automates detection and exploitation of SQL-injection flaws so you can prove — and then fix — database vulnerabilities in your own apps.',
                        'features' => ['Detects many injection types', 'Supports major databases', 'Safe test flags', 'DB fingerprinting'],
                        'steps' => ['Only test an app you own or are contracted to assess.', 'sqlmap -u "https://example.com/item?id=1" --batch', 'If injectable, note the parameter.', 'Fix with parameterised queries / prepared statements.'],
                        'download' => 'https://sqlmap.org/',
                        'docs' => 'https://github.com/sqlmapproject/sqlmap/wiki',
                    ],
                    [
                        'name' => 'WPScan',
                        'tagline' => 'WordPress security scanner',
                        'license' => 'Free for personal use',
                        'platforms' => 'Cross-platform (Ruby)',
                        'what' => 'Scans WordPress sites for vulnerable core, plugins and themes, weak users and exposed configuration — essential if you run WordPress.',
                        'features' => ['Plugin/theme vuln database', 'User enumeration', 'Weak-password checks', 'Config exposure detection'],
                        'steps' => ['Get a free API token from wpscan.com.', 'wpscan --url https://example.com --api-token YOUR_TOKEN', 'Update anything flagged as vulnerable.', 'Harden logins and hide version info.'],
                        'download' => 'https://wpscan.com/wordpress-security-scanner/',
                        'docs' => 'https://github.com/wpscanteam/wpscan',
                    ],
                    [
                        'name' => 'Nuclei',
                        'tagline' => 'Template-based vuln scanner',
                        'license' => 'Open source (MIT)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'A fast, community-driven scanner that runs thousands of YAML vulnerability templates against your targets — great for catching known CVEs quickly.',
                        'features' => ['Thousands of community templates', 'Very fast, low false positives', 'CI/CD friendly', 'Custom template support'],
                        'steps' => ['Install the single binary from GitHub releases.', 'nuclei -u https://example.com', 'Keep templates updated: nuclei -update-templates', 'Patch anything matched by a template.'],
                        'download' => 'https://github.com/projectdiscovery/nuclei/releases',
                        'docs' => 'https://docs.projectdiscovery.io/tools/nuclei',
                    ],
                    [
                        'name' => 'ffuf',
                        'tagline' => 'Content & directory fuzzer',
                        'license' => 'Open source (MIT)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'A blazing-fast web fuzzer for discovering hidden directories, files and parameters that should not be publicly reachable.',
                        'features' => ['Directory & file discovery', 'Parameter fuzzing', 'Very fast (Go)', 'Flexible filtering'],
                        'steps' => ['Download the binary from GitHub releases.', 'ffuf -u https://example.com/FUZZ -w wordlist.txt', 'Review 200/301 responses.', 'Lock down anything sensitive that responds.'],
                        'download' => 'https://github.com/ffuf/ffuf/releases',
                        'docs' => 'https://github.com/ffuf/ffuf#readme',
                    ],
                ],
            ],
            [
                'category' => 'Password & Authentication',
                'color' => '#ffc66f',
                'items' => [
                    [
                        'name' => 'John the Ripper',
                        'tagline' => 'Password strength auditor',
                        'license' => 'Open source (GPL)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'Tests how resistant your own password hashes are to cracking, so you can enforce stronger policies. Use only on hashes you are authorised to audit.',
                        'features' => ['Many hash formats', 'Wordlist & incremental modes', 'Rules engine', 'GPU builds available'],
                        'steps' => ['Export hashes you own to a file.', 'john --wordlist=rockyou.txt hashes.txt', 'john --show hashes.txt to see cracked ones.', 'Force resets & stronger rules for weak accounts.'],
                        'download' => 'https://www.openwall.com/john/',
                        'docs' => 'https://www.openwall.com/john/doc/',
                    ],
                    [
                        'name' => 'Hashcat',
                        'tagline' => 'GPU password recovery',
                        'license' => 'Open source (MIT)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'The fastest password-recovery tool, GPU-accelerated. Ideal for auditing the real-world strength of stored credentials you are responsible for.',
                        'features' => ['GPU acceleration', '300+ hash types', 'Mask & rule attacks', 'Distributed cracking'],
                        'steps' => ['Identify the hash mode for your hashes.', 'hashcat -m 0 -a 0 hashes.txt wordlist.txt', 'Review cracked results.', 'Mandate longer passphrases / MFA where weak.'],
                        'download' => 'https://hashcat.net/hashcat/',
                        'docs' => 'https://hashcat.net/wiki/',
                    ],
                    [
                        'name' => 'THC-Hydra',
                        'tagline' => 'Login brute-force tester',
                        'license' => 'Open source (AGPL)',
                        'platforms' => 'Cross-platform',
                        'what' => 'Tests login endpoints against weak credentials across many protocols, so you can confirm rate-limiting and lockout actually work on your services.',
                        'features' => ['50+ protocols', 'Parallel connections', 'Custom credential lists', 'Login-form support'],
                        'steps' => ['Only target your own auth endpoints.', 'hydra -l admin -P list.txt example.com http-post-form ...', 'Confirm lockout / rate-limits trigger.', 'Add throttling, MFA and account lockout.'],
                        'download' => 'https://github.com/vanhauser-thc/thc-hydra',
                        'docs' => 'https://github.com/vanhauser-thc/thc-hydra#readme',
                    ],
                ],
            ],
            [
                'category' => 'Network & Traffic',
                'color' => '#23ff9a',
                'items' => [
                    [
                        'name' => 'Wireshark',
                        'tagline' => 'Packet analyzer',
                        'license' => 'Open source (GPL)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'The world\'s most popular network protocol analyzer — capture and inspect traffic to spot cleartext data, misconfigurations and suspicious connections.',
                        'features' => ['Live capture & deep inspection', 'Hundreds of protocols', 'Powerful display filters', 'TLS/SSL debugging'],
                        'steps' => ['Install and choose a capture interface.', 'Filter, e.g. http or tls, to focus.', 'Look for cleartext credentials or tokens.', 'Move anything sensitive onto HTTPS/TLS.'],
                        'download' => 'https://www.wireshark.org/download.html',
                        'docs' => 'https://www.wireshark.org/docs/',
                    ],
                    [
                        'name' => 'Metasploit Framework',
                        'tagline' => 'Exploitation framework',
                        'license' => 'Open source (BSD)',
                        'platforms' => 'Windows · macOS · Linux',
                        'what' => 'The standard framework for validating vulnerabilities safely in a lab, verifying patches and building repeatable proof-of-concept tests.',
                        'features' => ['Huge exploit & payload library', 'Auxiliary scanners', 'Post-exploitation modules', 'Lab-friendly workflow'],
                        'steps' => ['Install and run against a lab VM you control.', 'msfconsole, then search a known CVE.', 'Confirm whether your patched host is affected.', 'Document and remediate findings.'],
                        'download' => 'https://github.com/rapid7/metasploit-framework',
                        'docs' => 'https://docs.metasploit.com/',
                    ],
                ],
            ],
            [
                'category' => 'Platforms & Distros',
                'color' => '#b18cff',
                'items' => [
                    [
                        'name' => 'Kali Linux',
                        'tagline' => 'Pen-testing OS',
                        'license' => 'Free / open source',
                        'platforms' => 'ISO · VM · WSL · ARM',
                        'what' => 'A Debian-based distribution that ships hundreds of the tools above pre-installed and configured — the fastest way to get a full testing lab running.',
                        'features' => ['600+ pre-installed tools', 'VM & WSL images', 'Regular rolling updates', 'Great documentation'],
                        'steps' => ['Download the VM or ISO image.', 'Run it in a VM isolated from production.', 'Update: sudo apt update && sudo apt full-upgrade', 'Use it only against systems you may test.'],
                        'download' => 'https://www.kali.org/get-kali/',
                        'docs' => 'https://www.kali.org/docs/',
                    ],
                    [
                        'name' => 'OWASP Juice Shop',
                        'tagline' => 'Legal practice target',
                        'license' => 'Open source (MIT)',
                        'platforms' => 'Docker · Node · cloud',
                        'what' => 'A deliberately insecure web app you run yourself to practise every technique legally and safely — the perfect place to learn before touching real systems.',
                        'features' => ['100+ built-in challenges', 'Runs locally in Docker', 'Score board & hints', 'Maps to OWASP Top 10'],
                        'steps' => ['docker run -p 3000:3000 bkimminich/juice-shop', 'Open http://localhost:3000', 'Work through the challenges to learn.', 'Apply the lessons to harden your real apps.'],
                        'download' => 'https://owasp.org/www-project-juice-shop/',
                        'docs' => 'https://pwning.owasp-juice.shop/',
                    ],
                ],
            ],
        ];
    }

    public function ethicalHackingToolkit(array $data = []): void
    {
        $this->render('pages/ethical-hacking-toolkit', array_replace([
            'title' => 'Ethical Hacking Toolkit | Crest Web Media',
            'metaDescription' => 'A free defensive ethical-hacking toolkit: scan your own site for exposed sensitive files, weak security headers, TLS, DNS/email spoofing, cookie flags and disclosure — with a prioritised hardening report.',
            'toolkitTools' => $this->toolkitTools(),
        ], $this->toolAccessData(), $data));
    }

    public function analyzeExposure(): void
    {
        if (!$this->hasToolAccess()) {
            $this->ethicalHackingToolkit(['accessError' => 'Register and verify your email to run the exposure scan.']);
            return;
        }

        if (Security::hitRateLimit('exposure_scan', 6, 900)) {
            http_response_code(429);
            $this->ethicalHackingToolkit(['error' => 'Too many exposure scans. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->ethicalHackingToolkit(['error' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $host = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        if ($host === null) {
            http_response_code(422);
            $this->ethicalHackingToolkit(['error' => 'Enter a valid public domain you own or are authorised to test. Private networks and localhost are blocked.']);
            return;
        }

        if (!$this->consumeFreeScan('exposure_scan')) {
            http_response_code(402);
            $this->ethicalHackingToolkit(['error' => $this->growthLabLimitMessage()]);
            return;
        }

        (new AuditLogger())->log('tools.exposure_scanned', ['host' => $host]);
        $result = $this->exposureReport($host);

        $this->ethicalHackingToolkit([
            'exposureHost' => $host,
            'report' => [
                'tool' => 'Attack Surface & File Exposure',
                'icon' => 'fa-shield-halved',
                'target' => $host,
                'score' => $result['score'],
                'summary' => 'Publicly reachable sensitive files, disclosure headers and directory listings on ' . $host . '.',
                'checks' => $result['checks'],
            ],
        ]);
    }

    /**
     * Scans a bounded set of commonly-leaked sensitive paths on a host you
     * control, plus server-disclosure headers and directory-listing exposure.
     * Every request is a single SSRF-safe GET to the target host only.
     *
     * @return array{score: int, checks: array<int, array<string, mixed>>}
     */
    private function exposureReport(string $host): array
    {
        // Sensitive paths that should never be public. Kept small and bounded.
        $paths = [
            ['path' => '/.env', 'label' => 'Environment file (.env)', 'fix' => 'Move secrets out of the web root and block dotfiles in your server config.'],
            ['path' => '/.git/config', 'label' => 'Git repository (.git/config)', 'fix' => 'Never deploy the .git directory; deny access to /.git in your server config.'],
            ['path' => '/.git/HEAD', 'label' => 'Git HEAD (.git/HEAD)', 'fix' => 'Remove the .git directory from production and deny /.git access.'],
            ['path' => '/config.php.bak', 'label' => 'Config backup (config.php.bak)', 'fix' => 'Delete editor/backup files (.bak, .old, ~) from the server.'],
            ['path' => '/wp-config.php.bak', 'label' => 'WordPress config backup', 'fix' => 'Remove wp-config.php.bak and any config backups from the web root.'],
            ['path' => '/backup.zip', 'label' => 'Site backup archive (backup.zip)', 'fix' => 'Store backups outside the public web root, never at a guessable URL.'],
            ['path' => '/backup.sql', 'label' => 'Database dump (backup.sql)', 'fix' => 'Keep SQL dumps off the web server entirely.'],
            ['path' => '/phpinfo.php', 'label' => 'phpinfo() page', 'fix' => 'Delete phpinfo.php — it leaks your full server configuration.'],
            ['path' => '/.htaccess', 'label' => 'Apache config (.htaccess)', 'fix' => 'Ensure .htaccess returns 403; Apache blocks it by default — check overrides.'],
            ['path' => '/.DS_Store', 'label' => 'macOS .DS_Store', 'fix' => 'Remove .DS_Store files; they leak your directory structure.'],
            ['path' => '/server-status', 'label' => 'Apache server-status', 'fix' => 'Restrict mod_status /server-status to localhost only.'],
            ['path' => '/.well-known/security.txt', 'label' => 'security.txt present', 'fix' => 'Add a security.txt so researchers can report issues responsibly.', 'want' => true],
        ];

        $checks = [];
        $exposed = 0;
        $scanned = 0;

        foreach ($paths as $target) {
            if ($scanned >= 14) {
                break; // hard cap on outbound requests
            }
            $scanned++;
            $res = $this->fetchSmallText('https://' . $host . $target['path']);
            $reachable = $res['status'] >= 200 && $res['status'] < 300 && $res['body'] !== '';

            if (!empty($target['want'])) {
                // A "want" item (security.txt) is good when it IS present.
                $checks[] = [
                    'label' => $target['label'],
                    'present' => $reachable,
                    'value' => $reachable ? 'Published for responsible disclosure (HTTP ' . $res['status'] . ')' : 'Not found',
                    'advice' => $target['fix'] ?? '',
                ];
                continue;
            }

            if ($reachable) {
                $exposed++;
            }
            $checks[] = [
                'label' => $target['label'],
                'present' => !$reachable, // "pass" means NOT exposed
                'value' => $reachable
                    ? 'EXPOSED — HTTP ' . $res['status'] . ' at ' . $target['path']
                    : 'Not publicly reachable (HTTP ' . ($res['status'] ?: 'no response') . ')',
                'advice' => $target['fix'] ?? '',
            ];
        }

        // Server-disclosure check from the homepage response headers.
        $headers = $this->fetchHeaders('https://' . $host . '/');
        $server = trim((string) ($headers['server'] ?? ''));
        $powered = trim((string) ($headers['x-powered-by'] ?? ''));
        $discloses = ($server !== '' && preg_match('/\d/', $server)) || $powered !== '';
        $checks[] = [
            'label' => 'Software version disclosure',
            'present' => !$discloses,
            'value' => $discloses
                ? trim(($server !== '' ? 'Server: ' . excerpt($server, 60) : '') . ' ' . ($powered !== '' ? 'X-Powered-By: ' . excerpt($powered, 60) : ''))
                : 'No obvious server or framework version leaked',
            'advice' => 'Hide software version numbers in Server and X-Powered-By headers to slow targeted attacks.',
        ];

        // Directory-listing exposure on the homepage path.
        $root = $this->fetchSmallText('https://' . $host . '/');
        $listing = stripos($root['body'], 'Index of /') !== false;
        $checks[] = [
            'label' => 'Directory listing exposure',
            'present' => !$listing,
            'value' => $listing ? 'A directory listing appears to be enabled' : 'No open directory listing detected',
            'advice' => 'Disable auto-indexing (e.g. Apache "Options -Indexes") so folders cannot be browsed.',
        ];

        // Score: start at 100; exposed secrets hurt most, other failures less.
        $fails = count(array_filter($checks, static fn ($c) => empty($c['present'])));
        $score = max(0, 100 - ($exposed * 22) - (($fails - $exposed) * 8));

        return ['score' => $score, 'checks' => $checks];
    }

    /**
     * The defensive tools grouped for the ethical-hacking toolkit hub.
     *
     * @return array<int, array<string, mixed>>
     */
    private function toolkitTools(): array
    {
        return [
            ['title' => 'Sensitive File Exposure', 'url' => '#exposure-scan', 'icon' => 'fa-shield-halved', 'summary' => 'Scan your site for exposed .env, .git, backups, phpinfo, server-status and directory listings.', 'accent' => 'red'],
            ['title' => 'Security Headers', 'url' => '/tools/security-headers', 'icon' => 'fa-lock', 'summary' => 'Grade HSTS, CSP, COOP, CORP, frame and MIME protection, cookie flags and disclosure.', 'accent' => 'green'],
            ['title' => 'DNS & Email Spoofing', 'url' => '/tools/dns-email', 'icon' => 'fa-envelope-circle-check', 'summary' => 'Check MX, SPF, DMARC and CAA to stop spoofing and improve deliverability.', 'accent' => 'green'],
            ['title' => 'TLS / SSL Certificate', 'url' => '/tools/tls-ssl', 'icon' => 'fa-certificate', 'summary' => 'Read certificate issuer, validity window and days remaining to expiry.', 'accent' => 'green'],
            ['title' => 'security.txt & Discovery', 'url' => '/tools/security-txt', 'icon' => 'fa-file-shield', 'summary' => 'Check responsible-disclosure and crawler-discovery files.', 'accent' => 'cyan'],
            ['title' => 'Password & Token Lab', 'url' => '/free-penetration-testing-tools', 'icon' => 'fa-key', 'summary' => 'Test passphrase strength, decode JWTs, hash text and build a hardened CSP — all locally.', 'accent' => 'violet'],
            ['title' => 'Hacking Tools Library (Pro)', 'url' => '/ethical-hacking-tools', 'icon' => 'fa-toolbox', 'summary' => 'Members-only portal: download the best free pen-testing tools with official links and step-by-step usage guides.', 'accent' => 'red'],
        ];
    }

    public function contentAssistant(array $data = []): void
    {
        $this->render('pages/content-assistant', array_replace([
            'title' => 'AI Content Assistant | Crest Web Media',
            'metaDescription' => 'Generate SEO meta descriptions, title tags, blog outlines, FAQs, product copy, social posts and CTAs in seconds with the Crest Web Media AI content assistant.',
            'contentTypes' => \App\Services\ContentAssistantService::TYPES,
            'contentTones' => \App\Services\ContentAssistantService::TONES,
            'aiLive' => (new \App\Models\AiSettingsRepository())->isLive(),
        ], $this->toolAccessData(), $data));
    }

    public function generateContent(): void
    {
        if (!$this->hasToolAccess()) {
            $this->contentAssistant(['accessError' => 'Register and verify your email to use the AI content assistant.']);
            return;
        }

        if (Security::hitRateLimit('content_assistant', 15, 900)) {
            http_response_code(429);
            $this->contentAssistant(['error' => 'Too many generations. Please wait a few minutes and try again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->contentAssistant(['error' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $type = (string) ($_POST['type'] ?? 'meta_description');
        $topic = trim((string) ($_POST['topic'] ?? ''));
        if (strlen($topic) < 3 || strlen($topic) > 160) {
            http_response_code(422);
            $this->contentAssistant(['error' => 'Enter a topic or keyword between 3 and 160 characters.']);
            return;
        }

        if (!$this->consumeFreeScan('content_assistant')) {
            http_response_code(402);
            $this->contentAssistant(['error' => $this->growthLabLimitMessage()]);
            return;
        }

        $result = (new \App\Services\ContentAssistantService())->generate($type, $topic, [
            'tone' => (string) ($_POST['tone'] ?? 'Professional'),
            'audience' => (string) ($_POST['audience'] ?? ''),
            'keyword' => (string) ($_POST['keyword'] ?? ''),
        ]);
        (new AuditLogger())->log('tools.content_generated', ['type' => $result['type'], 'source' => $result['source']]);

        $this->contentAssistant([
            'contentResult' => $result,
            'contentInput' => ['type' => $type, 'topic' => $topic, 'tone' => (string) ($_POST['tone'] ?? 'Professional'), 'audience' => (string) ($_POST['audience'] ?? ''), 'keyword' => (string) ($_POST['keyword'] ?? '')],
        ]);
    }

    public function analyzeHeaders(): void
    {
        if (!$this->hasToolAccess()) {
            $this->securityHeaders(['accessError' => 'Register and verify your email to reveal security header results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_headers', 10, 900)) {
            http_response_code(429);
            $this->securityHeaders(['toolError' => 'Too many scans. Please wait a few minutes before running another check.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->securityHeaders(['toolError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $url = $this->normalizePublicUrl((string) ($_POST['target_url'] ?? ''));
        if ($url === null) {
            http_response_code(422);
            $this->securityHeaders(['toolError' => 'Enter a public HTTPS or HTTP URL. Private networks, localhost, credentials and custom ports are blocked for safety.']);
            return;
        }

        if (!$this->consumeFreeScan('security_headers')) {
            http_response_code(402);
            $this->securityHeaders(['toolError' => $this->growthLabLimitMessage()]);
            return;
        }

        $headers = $this->fetchHeaders($url);
        if ($headers === []) {
            $this->securityHeaders(['toolError' => 'Could not read response headers from that site. Try the exact homepage URL.']);
            return;
        }

        (new AuditLogger())->log('tools.headers_analyzed', ['host' => parse_url($url, PHP_URL_HOST)]);

        $result = $this->scoreHeaders($headers);
        $this->securityHeaders([
            'targetUrl' => $url,
            'report' => [
                'tool' => 'Security Headers',
                'icon' => 'fa-lock',
                'target' => $url,
                'score' => $result['score'],
                'summary' => 'HTTP security-header posture for browser hardening and stack disclosure.',
                'checks' => $result['checks'],
                'facts' => $result['facts'] ?? [],
            ],
        ]);
    }

    public function analyzeDns(): void
    {
        if (!$this->hasToolAccess()) {
            $this->dnsEmail(['accessError' => 'Register and verify your email to reveal DNS results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_dns', 12, 900)) {
            http_response_code(429);
            $this->dnsEmail(['dnsError' => 'Too many DNS checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->dnsEmail(['dnsError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $host = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        if ($host === null) {
            http_response_code(422);
            $this->dnsEmail(['dnsError' => 'Enter a valid public domain. Private networks and localhost are blocked.']);
            return;
        }

        if (!$this->consumeFreeScan('dns_email')) {
            http_response_code(402);
            $this->dnsEmail(['dnsError' => $this->growthLabLimitMessage()]);
            return;
        }

        (new AuditLogger())->log('tools.dns_analyzed', ['host' => $host]);
        $result = $this->dnsReport($host);
        $this->dnsEmail([
            'dnsHost' => $host,
            'report' => [
                'tool' => 'DNS & Email Security',
                'icon' => 'fa-envelope-circle-check',
                'target' => $host,
                'score' => $result['score'],
                'summary' => 'DNS resilience and email anti-spoofing posture (SPF, DMARC, CAA, MX, NS).',
                'checks' => $result['checks'],
            ],
        ]);
    }

    public function analyzeTls(): void
    {
        if (!$this->hasToolAccess()) {
            $this->tlsSsl(['accessError' => 'Register and verify your email to reveal TLS results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_tls', 10, 900)) {
            http_response_code(429);
            $this->tlsSsl(['tlsError' => 'Too many TLS checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->tlsSsl(['tlsError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $host = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        if ($host === null) {
            http_response_code(422);
            $this->tlsSsl(['tlsError' => 'Enter a valid public domain. Private networks and localhost are blocked.']);
            return;
        }

        if (!$this->consumeFreeScan('tls_certificate')) {
            http_response_code(402);
            $this->tlsSsl(['tlsError' => $this->growthLabLimitMessage()]);
            return;
        }

        $tls = $this->tlsReport($host);
        if ($tls === []) {
            $this->tlsSsl(['tlsError' => 'Could not read a TLS certificate on port 443 for that host.']);
            return;
        }

        (new AuditLogger())->log('tools.tls_analyzed', ['host' => $host]);
        $days = (int) $tls['days_remaining'];
        $this->tlsSsl([
            'tlsHost' => $host,
            'report' => [
                'tool' => 'TLS / SSL Certificate',
                'icon' => 'fa-certificate',
                'target' => $host,
                'score' => $tls['score'],
                'summary' => 'HTTPS certificate validity, issuer and expiry health.',
                'facts' => [
                    ['label' => 'Subject', 'value' => (string) $tls['subject']],
                    ['label' => 'Issuer', 'value' => (string) $tls['issuer']],
                    ['label' => 'Valid to', 'value' => (string) $tls['valid_to']],
                    ['label' => 'Days left', 'value' => (string) $days],
                ],
                'checks' => [
                    ['label' => 'Certificate is valid and trusted', 'present' => true, 'value' => 'Chain verified for ' . $host],
                    ['label' => 'Comfortable renewal window', 'present' => $days > 14, 'value' => $days . ' days remaining (' . $tls['valid_from'] . ' to ' . $tls['valid_to'] . ')', 'advice' => 'Renew or enable auto-renewal — under 15 days remaining is risky.'],
                    ['label' => 'Long-lived validity buffer', 'present' => $days > 30, 'value' => $days > 30 ? 'Over 30 days of validity' : 'Fewer than 30 days left', 'advice' => 'Automate renewal (e.g. certbot) so certificates never approach expiry.'],
                ],
            ],
        ]);
    }

    public function analyzeWellKnown(): void
    {
        if (!$this->hasToolAccess()) {
            $this->securityTxt(['accessError' => 'Register and verify your email to reveal discovery results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_well_known', 10, 900)) {
            http_response_code(429);
            $this->securityTxt(['wellKnownError' => 'Too many discovery checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->securityTxt(['wellKnownError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $host = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        if ($host === null) {
            http_response_code(422);
            $this->securityTxt(['wellKnownError' => 'Enter a valid public domain. Private networks and localhost are blocked.']);
            return;
        }

        if (!$this->consumeFreeScan('well_known_discovery')) {
            http_response_code(402);
            $this->securityTxt(['wellKnownError' => $this->growthLabLimitMessage()]);
            return;
        }

        (new AuditLogger())->log('tools.well_known_analyzed', ['host' => $host]);
        $result = $this->wellKnownReport($host);
        $this->securityTxt([
            'wellKnownHost' => $host,
            'report' => [
                'tool' => 'security.txt & Discovery',
                'icon' => 'fa-file-shield',
                'target' => $host,
                'score' => $result['score'],
                'summary' => 'Responsible-disclosure and crawler discovery files exposed over HTTPS.',
                'checks' => $result['checks'],
            ],
        ]);
    }

    private function normalizePublicUrl(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . $input;
        }

        $parts = parse_url($input);
        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            return null;
        }

        if (!empty($parts['user']) || !empty($parts['pass'])) {
            return null;
        }

        $port = $parts['port'] ?? null;
        if ($port !== null && !in_array((int) $port, [80, 443], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
            return null;
        }

        $records = dns_get_record($host, DNS_A + DNS_AAAA);
        if ($records === false || $records === []) {
            return null;
        }

        foreach ($records as $record) {
            $ip = (string) ($record['ip'] ?? $record['ipv6'] ?? '');
            if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return null;
            }
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return strtolower((string) $parts['scheme']) . '://' . $host . $path . $query;
    }

    private function interactiveTool(string $key): void
    {
        $tools = [
            'growth-consultant' => [
                'title' => 'AI Website Growth Consultant | Crest Web Media',
                'metaDescription' => 'Interactive AI-style website growth consultant for SEO, PPC, AI automation, website and app development ideas.',
                'eyebrow' => 'AI Growth Consultant',
                'h1' => 'Find the Fastest Way to Turn Your Website Into Leads',
                'intro' => 'Answer a few questions and get a practical growth plan covering SEO pages, PPC, AI automation, conversion fixes and website/app development.',
                'icon' => 'fa-solid fa-brain',
                'mode' => 'growth-consultant',
            ],
            'quote-calculator' => [
                'title' => 'Instant Website Quote Calculator | Crest Web Media',
                'metaDescription' => 'Estimate website, app, SEO, PPC, AI automation and support project scope instantly.',
                'eyebrow' => 'Instant Quote',
                'h1' => 'Website, App and Growth Quote Calculator',
                'intro' => 'Build a quick estimate for pages, ecommerce, app features, AI, SEO, PPC and monthly support.',
                'icon' => 'fa-solid fa-calculator',
                'mode' => 'quote-calculator',
            ],
            'security-badge' => [
                'title' => 'Security Score Badge Generator | Crest Web Media',
                'metaDescription' => 'Generate a website security score badge from your security audit signals.',
                'eyebrow' => 'Security Badge',
                'h1' => 'Generate a Security Score Badge',
                'intro' => 'Create a polished badge after checking headers, TLS, DNS and disclosure files, then request a full audit.',
                'icon' => 'fa-solid fa-shield-halved',
                'mode' => 'security-badge',
            ],
            'client-portal' => [
                'title' => 'Client Portal Preview | Crest Web Media',
                'metaDescription' => 'Preview a professional client portal for tickets, SEO reports, rankings, files, invoices and project milestones.',
                'eyebrow' => 'Client Portal',
                'h1' => 'Preview the Client Portal Experience',
                'intro' => 'A premium portal concept for support tickets, SEO reports, SERP movement, milestones, files and growth actions.',
                'icon' => 'fa-solid fa-table-columns',
                'mode' => 'client-portal',
            ],
            'ppc-roi' => [
                'title' => 'PPC ROI Calculator | Crest Web Media',
                'metaDescription' => 'Estimate pay per click leads, cost per lead, revenue and return on ad spend.',
                'eyebrow' => 'PPC ROI',
                'h1' => 'PPC ROI Calculator',
                'intro' => 'Estimate clicks, leads, cost per lead, sales value and return before launching Google Ads.',
                'icon' => 'fa-solid fa-bullseye',
                'mode' => 'ppc-roi',
            ],
            'speed-simulator' => [
                'title' => 'Before/After Website Speed Simulator | Crest Web Media',
                'metaDescription' => 'Simulate website speed improvements and see how performance can affect leads and conversion.',
                'eyebrow' => 'Speed Simulator',
                'h1' => 'Before and After Website Speed Simulator',
                'intro' => 'Model how speed improvements can change user experience, conversion confidence and lead potential.',
                'icon' => 'fa-solid fa-gauge-high',
                'mode' => 'speed-simulator',
            ],
            'automation-finder' => [
                'title' => 'AI Automation Finder | Crest Web Media',
                'metaDescription' => 'Find practical AI automation ideas for leads, support, invoices, reporting, bookings and CRM workflows.',
                'eyebrow' => 'AI Automation Finder',
                'h1' => 'Find AI Automations Your Business Can Use',
                'intro' => 'Select time-consuming workflows and get a practical automation map for your website, CRM and team.',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'mode' => 'automation-finder',
            ],
        ];

        $tool = $tools[$key] ?? $tools['growth-consultant'];
        $this->render('pages/interactive-tool', array_replace([
            'title' => $tool['title'],
            'metaDescription' => $tool['metaDescription'],
            'tool' => $tool,
        ], $this->toolAccessData()));
    }

    private function toolAccessData(): array
    {
        Security::ensureSession();

        $message = $_SESSION['tool_access_message'] ?? null;
        $error = $_SESSION['tool_access_error'] ?? null;
        unset($_SESSION['tool_access_message'], $_SESSION['tool_access_error']);

        return [
            'csrf' => Security::csrfToken(),
            'toolLead' => $_SESSION['tool_lead'] ?? null,
            'pendingToolLead' => $_SESSION['pending_tool_lead'] ?? [],
            'scanUsage' => !empty($_SESSION['tool_lead']['email']) ? (new ToolUsageRepository())->status((string) $_SESSION['tool_lead']['email']) : null,
            'paypalSettings' => (new PayPalSettingsRepository())->current(),
            'member' => $_SESSION['member'] ?? null,
            'memberIsPro' => MemberRepository::isPro($_SESSION['member'] ?? null),
            'accessMessage' => $message,
            'accessError' => $error,
        ];
    }

    private function hasToolAccess(): bool
    {
        Security::ensureSession();

        return !empty($_SESSION['tool_lead']['email']);
    }

    private function consumeFreeScan(string $tool): bool
    {
        Security::ensureSession();

        // Growth Lab Pro members get unlimited scans — bypass the daily free cap.
        if (MemberRepository::isPro($_SESSION['member'] ?? null)) {
            return true;
        }

        $email = (string) ($_SESSION['tool_lead']['email'] ?? '');
        if ($email === '') {
            return false;
        }

        $usage = (new ToolUsageRepository())->consume($email, $tool);
        return (bool) ($usage['allowed'] ?? false);
    }

    private function growthLabLimitMessage(): string
    {
        return 'You have used your 3 free scans for today. Upgrade to Growth Lab Pro (€25/month or €200/year) at /tools-pricing for unlimited scans, white-label PDF reports and continuous background monitoring.';
    }

    private function redirectToTool(string $message, bool $success): never
    {
        Security::ensureSession();

        if ($success) {
            $_SESSION['tool_access_message'] = $message;
            unset($_SESSION['tool_access_error']);
        } else {
            $_SESSION['tool_access_error'] = $message;
            unset($_SESSION['tool_access_message']);
        }

        $returnTo = (string) ($_POST['return_to'] ?? '/seo-tools');
        $allowedToolPages = [
            '/seo-tools',
            '/serp-checker',
            '/free-penetration-testing-tools',
            '/ai-website-growth-consultant',
            '/instant-website-quote-calculator',
            '/security-score-badge-generator',
            '/client-portal-preview',
            '/ppc-roi-calculator',
            '/before-after-speed-simulator',
            '/ai-automation-finder',
        ];
        if (!in_array($returnTo, $allowedToolPages, true)) {
            $returnTo = '/seo-tools';
        }

        $this->redirect($returnTo . '#tool-access');
    }

    private function normalizePublicHost(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        if (str_contains($input, '://')) {
            $parts = parse_url($input);
            $input = is_array($parts) ? (string) ($parts['host'] ?? '') : '';
        }

        $host = strtolower(trim($input, " \t\n\r\0\x0B/"));
        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
            return null;
        }

        if (!preg_match('/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $host)) {
            return null;
        }

        return $this->hostResolvesPublicly($host) ? $host : null;
    }

    private function hostResolvesPublicly(string $host): bool
    {
        $records = dns_get_record($host, DNS_A + DNS_AAAA);
        if ($records === false || $records === []) {
            return false;
        }

        foreach ($records as $record) {
            $ip = (string) ($record['ip'] ?? $record['ipv6'] ?? '');
            if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    private function fetchHeaders(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 4,
                'ignore_errors' => true,
                'follow_location' => 0,
                'header' => "User-Agent: CrestWebMediaSecurityChecker/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @get_headers($url, true, $context);
        if (!is_array($raw)) {
            return [];
        }

        $headers = [];
        foreach ($raw as $name => $value) {
            if (is_int($name)) {
                $headers['status'][] = (string) $value;
                continue;
            }
            $headers[strtolower((string) $name)] = is_array($value) ? implode(', ', $value) : (string) $value;
        }

        return $headers;
    }

    private function fetchHtml(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 6,
                'ignore_errors' => true,
                'follow_location' => 0,
                'header' => "User-Agent: CrestWebMediaSEOAudit/1.0\r\nAccept: text/html,*/*;q=0.5\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $html = @file_get_contents($url, false, $context, 0, 850000);
        return is_string($html) ? $html : '';
    }

    private function scoreHeaders(array $headers): array
    {
        $checks = [
            'strict-transport-security' => ['label' => 'HTTP Strict Transport Security (HSTS)', 'weight' => 16, 'advice' => 'Add "Strict-Transport-Security: max-age=63072000; includeSubDomains; preload" to force HTTPS.'],
            'content-security-policy' => ['label' => 'Content Security Policy (CSP)', 'weight' => 16, 'advice' => 'Add a Content-Security-Policy header to block XSS and unapproved script sources.'],
            'x-frame-options' => ['label' => 'Clickjacking protection', 'weight' => 12, 'advice' => 'Add "X-Frame-Options: DENY" (or a frame-ancestors CSP directive).'],
            'x-content-type-options' => ['label' => 'MIME sniffing protection', 'weight' => 8, 'advice' => 'Add "X-Content-Type-Options: nosniff".'],
            'referrer-policy' => ['label' => 'Referrer privacy policy', 'weight' => 8, 'advice' => 'Add "Referrer-Policy: strict-origin-when-cross-origin".'],
            'permissions-policy' => ['label' => 'Browser permissions policy', 'weight' => 8, 'advice' => 'Add a Permissions-Policy header to disable unused APIs (camera, mic, geolocation).'],
            'cross-origin-opener-policy' => ['label' => 'Cross-Origin-Opener-Policy (COOP)', 'weight' => 6, 'advice' => 'Add "Cross-Origin-Opener-Policy: same-origin" to isolate your browsing context.'],
            'cross-origin-resource-policy' => ['label' => 'Cross-Origin-Resource-Policy (CORP)', 'weight' => 5, 'advice' => 'Add "Cross-Origin-Resource-Policy: same-origin" where appropriate.'],
        ];

        $score = 6;
        $rows = [];
        foreach ($checks as $header => $check) {
            $present = !empty($headers[$header]);
            $score += $present ? $check['weight'] : 0;
            $rows[] = [
                'label' => $check['label'],
                'present' => $present,
                'value' => $present ? (string) $headers[$header] : 'Missing',
                'advice' => $check['advice'],
            ];
        }

        // Information-disclosure checks (present here is "good" = header absent).
        foreach (['server' => 'Server banner', 'x-powered-by' => 'X-Powered-By disclosure'] as $header => $label) {
            $leaks = !empty($headers[$header]);
            $score += $leaks ? 0 : 6;
            $rows[] = [
                'label' => $label . ' hidden',
                'present' => !$leaks,
                'value' => $leaks ? 'Exposed: ' . $headers[$header] : 'Not disclosed',
                'advice' => 'Hide the ' . $header . ' header so you do not advertise your stack version.',
            ];
        }

        // Cookie flag hygiene when a Set-Cookie is present.
        if (!empty($headers['set-cookie'])) {
            $cookie = strtolower((string) $headers['set-cookie']);
            $secure = str_contains($cookie, 'secure') && str_contains($cookie, 'httponly');
            $score += $secure ? 4 : 0;
            $rows[] = [
                'label' => 'Secure cookie flags',
                'present' => $secure,
                'value' => $secure ? 'HttpOnly + Secure present' : 'Missing HttpOnly and/or Secure',
                'advice' => 'Set Secure, HttpOnly and SameSite on session cookies.',
            ];
        }

        $facts = [
            ['label' => 'HTTP status', 'value' => (string) ($headers['status'][0] ?? 'Unknown')],
            ['label' => 'Server', 'value' => (string) ($headers['server'] ?? 'Not disclosed')],
        ];

        return [
            'score' => min(100, $score),
            'status' => $headers['status'][0] ?? 'No status line',
            'checks' => $rows,
            'facts' => $facts,
        ];
    }

    private function dnsReport(string $host): array
    {
        $txt = @dns_get_record($host, DNS_TXT) ?: [];
        $dmarc = @dns_get_record('_dmarc.' . $host, DNS_TXT) ?: [];
        $records = [
            'A' => @dns_get_record($host, DNS_A) ?: [],
            'AAAA' => @dns_get_record($host, DNS_AAAA) ?: [],
            'MX' => @dns_get_record($host, DNS_MX) ?: [],
            'NS' => @dns_get_record($host, DNS_NS) ?: [],
            'CAA' => defined('DNS_CAA') ? (@dns_get_record($host, DNS_CAA) ?: []) : [],
            'TXT' => $txt,
            'DMARC' => $dmarc,
        ];

        $spf = array_values(array_filter($txt, static fn (array $row): bool => str_starts_with((string) ($row['txt'] ?? ''), 'v=spf1')));
        $dmarcPolicy = $dmarc[0]['txt'] ?? '';
        $score = 20;
        $score += $records['MX'] !== [] ? 15 : 0;
        $score += $spf !== [] ? 20 : 0;
        $score += $dmarc !== [] ? 25 : 0;
        $score += $records['CAA'] !== [] ? 10 : 0;
        $score += $records['NS'] !== [] ? 10 : 0;

        return [
            'score' => min(100, $score),
            'checks' => [
                ['label' => 'A/AAAA public records', 'present' => ($records['A'] !== [] || $records['AAAA'] !== []), 'value' => $this->recordSummary(array_merge($records['A'], $records['AAAA']), ['ip', 'ipv6']), 'advice' => 'Publish an A or AAAA record so the domain resolves to your server.'],
                ['label' => 'Mail exchanger (MX) records', 'present' => $records['MX'] !== [], 'value' => $this->recordSummary($records['MX'], ['target']), 'advice' => 'Add MX records if this domain sends or receives email.'],
                ['label' => 'SPF email protection', 'present' => $spf !== [], 'value' => $spf[0]['txt'] ?? 'Missing', 'advice' => 'Add an SPF TXT record (v=spf1 ... -all) to stop spoofing.'],
                ['label' => 'DMARC email policy', 'present' => $dmarc !== [], 'value' => $dmarcPolicy ?: 'Missing', 'advice' => 'Add a _dmarc TXT record (v=DMARC1; p=quarantine) to protect your brand.'],
                ['label' => 'CAA certificate control', 'present' => $records['CAA'] !== [], 'value' => $this->recordSummary($records['CAA'], ['value', 'tag']), 'advice' => 'Add CAA records to control which CAs may issue certificates for you.'],
                ['label' => 'Nameservers', 'present' => $records['NS'] !== [], 'value' => $this->recordSummary($records['NS'], ['target']), 'advice' => 'Ensure at least two nameservers for DNS resilience.'],
            ],
        ];
    }

    /**
     * Heuristic technology detection from response headers and HTML signatures
     * (Wappalyzer-style, using only open signals — no external service).
     *
     * @return array<string, mixed>
     */
    private function detectTech(string $url, array $headers, string $html): array
    {
        $haystack = strtolower($html);
        $found = [];

        $signatures = [
            'WordPress' => ['/wp-content/', '/wp-includes/', 'wp-json'],
            'WooCommerce' => ['woocommerce'],
            'Elementor' => ['elementor'],
            'Shopify' => ['cdn.shopify.com', 'shopify'],
            'Wix' => ['wix.com', 'wixstatic'],
            'Squarespace' => ['squarespace'],
            'Drupal' => ['/sites/default/files', 'drupal-settings-json'],
            'Joomla' => ['/media/jui/', 'joomla'],
            'React' => ['data-reactroot', '__react', '/react'],
            'Vue.js' => ['data-v-', 'vue.js', '__vue__'],
            'Angular' => ['ng-version', 'angular'],
            'Next.js' => ['/_next/', '__next_data__'],
            'Bootstrap' => ['bootstrap.min.css', 'class="container"'],
            'Tailwind CSS' => ['tailwind'],
            'jQuery' => ['jquery'],
            'Google Analytics' => ['google-analytics.com', 'gtag(', 'googletagmanager.com'],
            'Google Tag Manager' => ['googletagmanager.com/gtm.js'],
            'Meta Pixel' => ['connect.facebook.net', 'fbq('],
            'Cloudflare' => ['cdnjs.cloudflare.com', '__cf'],
            'Font Awesome' => ['font-awesome', 'fontawesome'],
            'Stripe' => ['js.stripe.com'],
            'PayPal' => ['paypal.com/sdk', 'paypalobjects'],
        ];

        foreach ($signatures as $tech => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) {
                    $found[$tech] = true;
                    break;
                }
            }
        }

        // Header-based signals.
        $server = strtolower((string) ($headers['server'] ?? ''));
        $poweredBy = strtolower((string) ($headers['x-powered-by'] ?? ''));
        $headerSignals = [
            'Nginx' => str_contains($server, 'nginx'),
            'Apache' => str_contains($server, 'apache'),
            'LiteSpeed' => str_contains($server, 'litespeed'),
            'Cloudflare' => str_contains($server, 'cloudflare') || isset($headers['cf-ray']),
            'PHP' => str_contains($poweredBy, 'php'),
            'ASP.NET' => str_contains($poweredBy, 'asp.net'),
            'Express' => str_contains($poweredBy, 'express'),
        ];
        foreach ($headerSignals as $tech => $hit) {
            if ($hit) {
                $found[$tech] = true;
            }
        }

        $generator = $this->metaContent($html, 'generator');
        $techList = array_keys($found);
        sort($techList);

        $checks = [];
        foreach ($techList as $tech) {
            $checks[] = ['label' => $tech, 'present' => true, 'value' => 'Detected'];
        }
        if ($checks === []) {
            $checks[] = ['label' => 'No common technologies matched', 'present' => false, 'value' => 'The site may be hand-coded or hide its signatures.'];
        }

        $facts = [
            ['label' => 'Server', 'value' => (string) ($headers['server'] ?? 'Not disclosed')],
            ['label' => 'Powered by', 'value' => (string) ($headers['x-powered-by'] ?? 'Not disclosed')],
            ['label' => 'Generator', 'value' => $generator !== '' ? $generator : 'Not disclosed'],
            ['label' => 'Technologies', 'value' => (string) count($techList)],
        ];

        // Score is informational: more clearly-identified tech = higher confidence.
        $score = min(100, 40 + count($techList) * 8);

        return [
            'tool' => 'Technology Stack',
            'icon' => 'fa-microchip',
            'target' => $url,
            'score' => $score,
            'summary' => 'Detected CMS, frameworks, libraries, analytics, CDN and server from public signals.',
            'facts' => $facts,
            'checks' => $checks,
            'recommendations' => [
                'Remove Server and X-Powered-By headers so you do not advertise exact versions.',
                'Keep every detected platform, plugin and library patched to its latest version.',
                'Audit third-party scripts (analytics, pixels) for privacy and performance impact.',
            ],
        ];
    }

    private function tlsReport(string $host): array
    {
        if (!function_exists('stream_socket_client') || !function_exists('openssl_x509_parse')) {
            return [];
        }

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $host,
                'SNI_enabled' => true,
            ],
        ]);

        $socket = @stream_socket_client('ssl://' . $host . ':443', $errno, $error, 5, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            return [];
        }

        $params = stream_context_get_params($socket);
        fclose($socket);
        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        if (!$cert) {
            return [];
        }

        $parsed = openssl_x509_parse($cert);
        if (!is_array($parsed)) {
            return [];
        }

        $validTo = (int) ($parsed['validTo_time_t'] ?? 0);
        $days = $validTo > 0 ? (int) floor(($validTo - time()) / 86400) : 0;

        return [
            'score' => $days > 60 ? 100 : ($days > 14 ? 75 : ($days > 0 ? 45 : 0)),
            'subject' => $parsed['subject']['CN'] ?? $host,
            'issuer' => $parsed['issuer']['O'] ?? ($parsed['issuer']['CN'] ?? 'Unknown issuer'),
            'valid_from' => isset($parsed['validFrom_time_t']) ? date('Y-m-d', (int) $parsed['validFrom_time_t']) : 'Unknown',
            'valid_to' => $validTo > 0 ? date('Y-m-d', $validTo) : 'Unknown',
            'days_remaining' => max(0, $days),
        ];
    }

    private function wellKnownReport(string $host): array
    {
        $targets = [
            'security.txt' => 'https://' . $host . '/.well-known/security.txt',
            'robots.txt' => 'https://' . $host . '/robots.txt',
        ];

        $rows = [];
        foreach ($targets as $label => $url) {
            $result = $this->fetchSmallText($url);
            $rows[] = [
                'label' => $label,
                'present' => $result['status'] >= 200 && $result['status'] < 300 && $result['body'] !== '',
                'value' => $result['status'] ? 'HTTP ' . $result['status'] . ': ' . excerpt($result['body'] ?: 'No readable body', 140) : 'Not reachable',
            ];
        }

        return [
            'score' => count(array_filter($rows, static fn (array $row): bool => $row['present'])) * 50,
            'checks' => $rows,
        ];
    }

    private function fetchSmallText(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 4,
                'ignore_errors' => true,
                'follow_location' => 0,
                'header' => "User-Agent: CrestWebMediaSecurityChecker/1.0\r\nAccept: text/plain,*/*;q=0.5\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context, 0, 5000);
        $status = 0;
        foreach ($http_response_header ?? [] as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches)) {
                $status = (int) $matches[1];
                break;
            }
        }

        return ['status' => $status, 'body' => is_string($body) ? trim($body) : ''];
    }

    private function recordSummary(array $records, array $keys): string
    {
        if ($records === []) {
            return 'Missing';
        }

        $values = [];
        foreach ($records as $record) {
            foreach ($keys as $key) {
                if (!empty($record[$key])) {
                    $values[] = (string) $record[$key];
                }
            }
        }

        return $values === [] ? 'Present' : implode(', ', array_slice(array_unique($values), 0, 5));
    }

    private function seoAudit(string $url, string $html, string $keyword): array
    {
        $bodyHtml = preg_replace('#<(script|style|noscript|template)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($bodyHtml)));
        $textLower = strtolower($text);
        $keywordLower = strtolower(trim($keyword));

        $title = $this->firstMatch('/<title[^>]*>(.*?)<\/title>/is', $html);
        $description = $this->metaContent($html, 'description');
        $robots = $this->metaContent($html, 'robots');
        $canonical = $this->linkHref($html, 'canonical');
        $h1s = $this->tagTexts($html, 'h1');
        $h2s = $this->tagTexts($html, 'h2');
        $h3s = $this->tagTexts($html, 'h3');

        // Full heading sequence for hierarchy analysis.
        $headingSeq = [];
        if (preg_match_all('/<h([1-6])\b[^>]*>(.*?)<\/h\1>/is', $html, $hm, PREG_SET_ORDER)) {
            foreach ($hm as $h) {
                $headingSeq[] = ['level' => (int) $h[1], 'text' => trim((string) preg_replace('/\s+/', ' ', strip_tags($h[2])))];
            }
        }

        $images = preg_match_all('/<img\b[^>]*>/i', $html, $imgM) ? $imgM[0] : [];
        $missingAlt = 0;
        $missingDims = 0;
        $lazyImages = 0;
        foreach ($images as $img) {
            if (!preg_match('/\salt=["\'][^"\']*[^"\'\s][^"\']*["\']/i', $img)) {
                $missingAlt++;
            }
            if (!preg_match('/\swidth=/i', $img) || !preg_match('/\sheight=/i', $img)) {
                $missingDims++;
            }
            if (preg_match('/\sloading=["\']lazy["\']/i', $img)) {
                $lazyImages++;
            }
        }

        // Link analysis: internal/external, nofollow, generic & empty anchors.
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
        $internal = 0;
        $external = 0;
        $nofollow = 0;
        $genericAnchors = 0;
        $emptyAnchors = 0;
        $generic = ['click here', 'read more', 'here', 'more', 'link', 'this', 'learn more'];
        if (preg_match_all('/<a\b([^>]*)>(.*?)<\/a>/is', $html, $am, PREG_SET_ORDER)) {
            foreach ($am as $a) {
                $attrs = $a[1];
                if (!preg_match('/href=["\']([^"\']+)["\']/i', $attrs, $hrefM)) {
                    continue;
                }
                $href = trim($hrefM[1]);
                if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    continue;
                }
                $lh = strtolower((string) parse_url($href, PHP_URL_HOST));
                if ($lh === '' || $lh === $host) {
                    $internal++;
                } else {
                    $external++;
                }
                if (preg_match('/rel=["\'][^"\']*nofollow[^"\']*["\']/i', $attrs)) {
                    $nofollow++;
                }
                $anchor = strtolower(trim((string) preg_replace('/\s+/', ' ', strip_tags($a[2]))));
                if ($anchor === '') {
                    $emptyAnchors++;
                } elseif (in_array($anchor, $generic, true)) {
                    $genericAnchors++;
                }
            }
        }

        // Structured data: parse JSON-LD @type values.
        $schemaTypes = [];
        if (preg_match_all('/<script\b[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $sm)) {
            foreach ($sm[1] as $block) {
                $decoded = json_decode(trim($block), true);
                if (is_array($decoded)) {
                    array_walk_recursive($decoded, static function ($v, $k) use (&$schemaTypes): void {
                        if ($k === '@type' && is_string($v)) {
                            $schemaTypes[] = $v;
                        }
                    });
                }
            }
        }
        $schemaTypes = array_values(array_unique($schemaTypes));
        $schemaCount = count($schemaTypes);

        // Meta / technical signals.
        $ogTitle = $this->metaContent($html, 'og:title');
        $ogDesc = $this->metaContent($html, 'og:description');
        $ogImage = $this->metaContent($html, 'og:image');
        $twitterCard = $this->metaContent($html, 'twitter:card');
        $hasViewport = (bool) preg_match('/<meta\b[^>]*name=["\']viewport["\']/i', $html);
        $hasLang = (bool) preg_match('/<html\b[^>]*\blang=/i', $html);
        $hasCharset = (bool) preg_match('/<meta\b[^>]*charset=/i', $html);
        $hasFavicon = (bool) preg_match('/<link\b[^>]*rel=["\'][^"\']*icon[^"\']*["\']/i', $html);
        $hasHreflang = (bool) preg_match('/<link\b[^>]*rel=["\']alternate["\'][^>]*hreflang=/i', $html);

        // Mixed content on HTTPS pages.
        $isHttps = str_starts_with(strtolower($url), 'https://');
        $mixed = 0;
        if ($isHttps && preg_match_all('/(?:src|href)=["\']http:\/\/[^"\']+["\']/i', $html, $mixM)) {
            $mixed = count($mixM[0]);
        }

        // Content metrics.
        $words = $text === '' ? 0 : str_word_count($text);
        $sentences = max(1, preg_match_all('/[.!?]+(\s|$)/', $text));
        $syllables = $this->countSyllables($text);
        $flesch = $words > 0
            ? round(206.835 - 1.015 * ($words / $sentences) - 84.6 * ($syllables / max(1, $words)), 1)
            : 0.0;
        $flesch = max(0.0, min(100.0, $flesch));
        $readLabel = $this->readingEaseLabel($flesch);
        $htmlBytes = strlen($html);
        $contentRatio = $htmlBytes > 0 ? round((strlen($text) / $htmlBytes) * 100, 1) : 0.0;

        // First 100 words for "keyword early" checks.
        $first100 = strtolower(implode(' ', array_slice(preg_split('/\s+/', $text) ?: [], 0, 100)));

        // Keyword prominence.
        $keywordCount = $keywordLower !== '' ? substr_count($textLower, $keywordLower) : 0;
        $density = $words > 0 && $keywordLower !== '' ? round(($keywordCount / max(1, $words)) * 100, 2) : 0.0;
        $kwInTitle = $keywordLower !== '' && str_contains(strtolower($title), $keywordLower);
        $kwInDesc = $keywordLower !== '' && str_contains(strtolower($description), $keywordLower);
        $kwInH1 = $keywordLower !== '' && str_contains(strtolower(implode(' ', $h1s)), $keywordLower);
        $kwInHeads = $keywordLower !== '' && str_contains(strtolower(implode(' ', array_merge($h2s, $h3s))), $keywordLower);
        $kwInFirst = $keywordLower !== '' && str_contains($first100, $keywordLower);
        $kwInUrl = $keywordLower !== '' && str_contains(strtolower(str_replace(['-', '_', '%20'], ' ', (string) parse_url($url, PHP_URL_PATH))), $keywordLower);

        // Top terms & phrases (content analysis — the premium bit most tools skip).
        $topTerms = $this->topTerms($textLower, 1, 10);
        $topPhrases = $this->topTerms($textLower, 2, 6);

        // URL quality.
        $path = (string) parse_url($url, PHP_URL_PATH);
        $urlClean = !preg_match('/[A-Z_]/', $path) && strlen($url) <= 90 && parse_url($url, PHP_URL_QUERY) === null;

        $titleLen = strlen($title);
        $descLen = strlen($description);

        // ---- Weighted checks (weight: 3 critical, 2 important, 1 minor) ----
        $c = [];
        $c[] = ['label' => 'Title tag', 'weight' => 3, 'present' => $title !== '' && $titleLen >= 15 && $titleLen <= 65, 'value' => $title !== '' ? $title . ' (' . $titleLen . ' chars)' : 'Missing', 'advice' => 'Write a unique 15–65 character title with the primary keyword near the front.'];
        $c[] = ['label' => 'Meta description', 'weight' => 3, 'present' => $description !== '' && $descLen >= 70 && $descLen <= 165, 'value' => $description !== '' ? $descLen . ' chars' : 'Missing', 'advice' => 'Add a compelling 120–160 character meta description with a call to action.'];
        $c[] = ['label' => 'Exactly one H1', 'weight' => 3, 'present' => count($h1s) === 1, 'value' => count($h1s) . ' H1(s)' . (count($h1s) ? ': ' . implode(' | ', array_slice($h1s, 0, 2)) : ''), 'advice' => 'Use exactly one H1 that states the page topic.'];
        $c[] = ['label' => 'Heading hierarchy', 'weight' => 2, 'present' => $this->headingHierarchyOk($headingSeq), 'value' => count($headingSeq) . ' headings, ' . (count($h2s)) . ' H2 / ' . count($h3s) . ' H3', 'advice' => 'Start with H1, do not skip levels (H1→H2→H3), and use headings to structure content.'];
        $c[] = ['label' => 'Content depth', 'weight' => 2, 'present' => $words >= 600, 'value' => $words . ' words', 'advice' => 'Aim for 600+ words of genuinely useful content for competitive terms.'];
        $c[] = ['label' => 'Readability', 'weight' => 1, 'present' => $flesch >= 45, 'value' => $flesch . ' (' . $readLabel . ')', 'advice' => 'Aim for a Flesch reading ease of 50–70: shorter sentences and simpler words read better and convert better.'];
        $c[] = ['label' => 'Canonical URL', 'weight' => 2, 'present' => $canonical !== '', 'value' => $canonical ?: 'Missing', 'advice' => 'Add a self-referencing canonical link to avoid duplicate-content dilution.'];
        $c[] = ['label' => 'Indexable (no noindex)', 'weight' => 3, 'present' => !str_contains(strtolower($robots), 'noindex'), 'value' => $robots !== '' ? $robots : 'No noindex directive', 'advice' => 'Remove any noindex directive if this page should rank.'];
        $c[] = ['label' => 'Structured data (schema)', 'weight' => 2, 'present' => $schemaCount > 0, 'value' => $schemaCount > 0 ? implode(', ', array_slice($schemaTypes, 0, 6)) : 'None', 'advice' => 'Add JSON-LD schema (Organization, Service, FAQ, Breadcrumb, Article) for rich results.'];
        $c[] = ['label' => 'Image alt text', 'weight' => 2, 'present' => $missingAlt === 0, 'value' => count($images) . ' images, ' . $missingAlt . ' missing alt', 'advice' => 'Add descriptive alt text to every meaningful image.'];
        $c[] = ['label' => 'Image dimensions set (CLS)', 'weight' => 1, 'present' => $missingDims === 0, 'value' => $missingDims . ' of ' . count($images) . ' missing width/height', 'advice' => 'Set explicit width and height on images to prevent layout shift (a Core Web Vital).'];
        $c[] = ['label' => 'HTTPS (no mixed content)', 'weight' => 3, 'present' => $isHttps && $mixed === 0, 'value' => !$isHttps ? 'Not HTTPS' : ($mixed === 0 ? 'Secure, no mixed content' : $mixed . ' insecure http:// resources'), 'advice' => 'Serve over HTTPS and load every asset over https:// to avoid mixed-content warnings.'];
        $c[] = ['label' => 'Mobile viewport', 'weight' => 3, 'present' => $hasViewport, 'value' => $hasViewport ? 'Set' : 'Missing', 'advice' => 'Add <meta name="viewport" content="width=device-width, initial-scale=1">.'];
        $c[] = ['label' => 'Charset declared', 'weight' => 1, 'present' => $hasCharset, 'value' => $hasCharset ? 'Set' : 'Missing', 'advice' => 'Declare <meta charset="utf-8"> early in the head.'];
        $c[] = ['label' => 'Language declared', 'weight' => 1, 'present' => $hasLang, 'value' => $hasLang ? 'html lang set' : 'Missing', 'advice' => 'Set a lang attribute on <html> (e.g. lang="en").'];
        $c[] = ['label' => 'Favicon', 'weight' => 1, 'present' => $hasFavicon, 'value' => $hasFavicon ? 'Present' : 'Missing', 'advice' => 'Add a favicon / site icon link for brand and trust signals.'];
        $c[] = ['label' => 'Open Graph (social)', 'weight' => 1, 'present' => $ogTitle !== '' && $ogImage !== '' && $ogDesc !== '', 'value' => $ogTitle !== '' ? 'og:title + image' . ($ogDesc !== '' ? ' + description' : '') : 'Missing', 'advice' => 'Add og:title, og:description and og:image for rich social sharing.'];
        $c[] = ['label' => 'Twitter card', 'weight' => 1, 'present' => $twitterCard !== '', 'value' => $twitterCard !== '' ? $twitterCard : 'Missing', 'advice' => 'Add a twitter:card meta tag (summary_large_image).'];
        $c[] = ['label' => 'Descriptive link anchors', 'weight' => 1, 'present' => $genericAnchors === 0 && $emptyAnchors === 0, 'value' => $genericAnchors . ' generic, ' . $emptyAnchors . ' empty of ' . ($internal + $external) . ' links', 'advice' => 'Replace "click here"/"read more" and empty anchors with descriptive, keyword-relevant text.'];
        $c[] = ['label' => 'Internal linking', 'weight' => 2, 'present' => $internal >= 3, 'value' => $internal . ' internal, ' . $external . ' external', 'advice' => 'Link to at least 3–5 relevant internal pages to spread authority and help crawling.'];
        $c[] = ['label' => 'Clean URL', 'weight' => 1, 'present' => $urlClean, 'value' => strlen($url) . ' chars' . (preg_match('/[A-Z_]/', $path) ? ', has uppercase/underscore' : '') . (parse_url($url, PHP_URL_QUERY) !== null ? ', has query string' : ''), 'advice' => 'Use short, lowercase, hyphenated URLs without query parameters where possible.'];

        // Keyword-specific checks only when a focus keyword is provided.
        if ($keywordLower !== '') {
            $c[] = ['label' => 'Keyword in title', 'weight' => 3, 'present' => $kwInTitle, 'value' => $kwInTitle ? 'Yes' : 'Not found', 'advice' => 'Include the focus keyword in the title tag, ideally near the front.'];
            $c[] = ['label' => 'Keyword in H1', 'weight' => 2, 'present' => $kwInH1, 'value' => $kwInH1 ? 'Yes' : 'Not found', 'advice' => 'Use the focus keyword naturally in the H1.'];
            $c[] = ['label' => 'Keyword in first 100 words', 'weight' => 2, 'present' => $kwInFirst, 'value' => $kwInFirst ? 'Yes' : 'Not found', 'advice' => 'Mention the focus keyword early — within the first paragraph.'];
            $c[] = ['label' => 'Keyword in meta description', 'weight' => 1, 'present' => $kwInDesc, 'value' => $kwInDesc ? 'Yes' : 'Not found', 'advice' => 'Include the focus keyword in the meta description (it bolds in results).'];
            $c[] = ['label' => 'Keyword in subheadings', 'weight' => 1, 'present' => $kwInHeads, 'value' => $kwInHeads ? 'Yes' : 'Not found', 'advice' => 'Use the keyword or close variants in some H2/H3 subheadings.'];
            $c[] = ['label' => 'Keyword in URL', 'weight' => 1, 'present' => $kwInUrl, 'value' => $kwInUrl ? 'Yes' : 'Not found', 'advice' => 'Where practical, include the focus keyword in the URL slug.'];
            $c[] = ['label' => 'Keyword density (0.5–2.5%)', 'weight' => 1, 'present' => $density >= 0.5 && $density <= 2.5, 'value' => $keywordCount . ' uses, ' . $density . '%', 'advice' => 'Keep density natural (about 0.5–2.5%). Over-stuffing hurts; too little misses relevance.'];
        }

        // Weighted score.
        $totalWeight = array_sum(array_map(static fn (array $x): int => $x['weight'], $c));
        $gotWeight = array_sum(array_map(static fn (array $x): int => $x['present'] ? $x['weight'] : 0, $c));
        $score = $totalWeight > 0 ? (int) round(($gotWeight / $totalWeight) * 100) : 0;

        $failed = array_values(array_filter($c, static fn (array $x): bool => !$x['present']));
        usort($failed, static fn (array $a, array $b): int => $b['weight'] <=> $a['weight']);

        return [
            'score' => $score,
            'title' => $title,
            'description' => $description,
            'h1s' => $h1s,
            'h2s' => array_slice($h2s, 0, 8),
            'words' => $words,
            'internal_links' => $internal,
            'external_links' => $external,
            'keyword_density' => $density,
            'checks' => $c,
            'readability' => $flesch,
            'reading_label' => $readLabel,
            'schema_types' => $schemaTypes,
            'top_terms' => $topTerms,
            'top_phrases' => $topPhrases,
            'page_weight_kb' => round($htmlBytes / 1024, 1),
            'content_ratio' => $contentRatio,
            'image_count' => count($images),
            'nofollow_links' => $nofollow,
            'priority_fixes' => array_slice(array_map(static fn (array $x): array => ['label' => $x['label'], 'advice' => $x['advice']], $failed), 0, 6),
        ];
    }

    /**
     * Rough syllable count for a body of text (for Flesch reading ease).
     */
    private function countSyllables(string $text): int
    {
        $total = 0;
        foreach (preg_split('/[^a-z]+/i', strtolower($text)) ?: [] as $word) {
            if ($word === '') {
                continue;
            }
            $word = preg_replace('/e$/', '', $word);
            $count = preg_match_all('/[aeiouy]+/', (string) $word);
            $total += max(1, (int) $count);
        }

        return $total;
    }

    private function readingEaseLabel(float $score): string
    {
        return match (true) {
            $score >= 80 => 'very easy',
            $score >= 60 => 'easy',
            $score >= 50 => 'fairly easy',
            $score >= 30 => 'difficult',
            default => 'very difficult',
        };
    }

    /**
     * Top N most-frequent 1- or 2-word terms, excluding common stop words.
     *
     * @return array<int, array{term: string, count: int}>
     */
    private function topTerms(string $textLower, int $n, int $limit): array
    {
        static $stop = null;
        if ($stop === null) {
            $stop = array_flip(explode(' ', 'the a an and or but of to in on for with at by from is are was were be been being this that these those it its as we you your our their his her they them he she i me my will can do does did has have had not no yes if then than so such more most very just also into out up down over under about after before between while all any each other some more one two three new get got make made use used using how what why when where who which'));
        }
        $tokens = array_values(array_filter(
            preg_split('/[^a-z0-9]+/', $textLower) ?: [],
            static fn (string $w): bool => strlen($w) >= 3 && !isset($stop[$w]) && !ctype_digit($w)
        ));

        $freq = [];
        $count = count($tokens);
        for ($i = 0; $i + $n - 1 < $count; $i++) {
            $gram = implode(' ', array_slice($tokens, $i, $n));
            $freq[$gram] = ($freq[$gram] ?? 0) + 1;
        }
        arsort($freq);

        $out = [];
        foreach (array_slice($freq, 0, $limit, true) as $term => $ct) {
            if ($ct < 2) {
                continue;
            }
            $out[] = ['term' => $term, 'count' => $ct];
        }

        return $out;
    }

    /**
     * @param array<int, array{level: int, text: string}> $seq
     */
    private function headingHierarchyOk(array $seq): bool
    {
        if ($seq === []) {
            return false;
        }
        if ($seq[0]['level'] !== 1) {
            return false;
        }
        $prev = 1;
        foreach ($seq as $h) {
            if ($h['level'] - $prev > 1) {
                return false;
            }
            $prev = $h['level'];
        }

        return true;
    }

    private function serpReport(string $keyword, string $targetHost, string $location): array
    {
        // Prefer a real SERP API (ZenSERP) when a key is configured; otherwise
        // fall back to the free live scrape below.
        $apiResult = $this->zenserpReport($keyword, $targetHost, $location);
        if ($apiResult !== null) {
            return $apiResult;
        }

        $query = trim($keyword . ' ' . $location);
        $url = 'https://lite.duckduckgo.com/lite/?q=' . rawurlencode($query);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 6,
                'ignore_errors' => true,
                'header' => "User-Agent: CrestWebMediaSERPChecker/1.0\r\nAccept: text/html,*/*;q=0.5\r\n",
            ],
        ]);
        $html = @file_get_contents($url, false, $context, 0, 450000);
        $results = [];
        if (is_string($html) && preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $href = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $title = trim(preg_replace('/\s+/', ' ', strip_tags($match[2])) ?? '');
                if ($title === '' || str_starts_with($href, '/')) {
                    continue;
                }
                if (str_contains($href, 'uddg=')) {
                    parse_str((string) parse_url($href, PHP_URL_QUERY), $parts);
                    $href = isset($parts['uddg']) ? urldecode((string) $parts['uddg']) : $href;
                }
                $host = strtolower((string) parse_url($href, PHP_URL_HOST));
                if ($host === '' || str_contains($host, 'duckduckgo.com')) {
                    continue;
                }
                $results[] = ['title' => $title, 'url' => $href, 'host' => preg_replace('/^www\./', '', $host)];
                if (count($results) >= 10) {
                    break;
                }
            }
        }

        $normalizedTarget = preg_replace('/^www\./', '', strtolower($targetHost));
        $position = null;
        foreach ($results as $index => $result) {
            if ($result['host'] === $normalizedTarget || str_ends_with($result['host'], '.' . $normalizedTarget)) {
                $position = $index + 1;
                break;
            }
        }

        return [
            'engine' => 'DuckDuckGo Lite live results',
            'query' => $query,
            'position' => $position,
            'results' => $results,
            'opportunities' => $this->serpOpportunities($position, $results),
        ];
    }

    /**
     * Live Google results via the ZenSERP API when a key is configured.
     * The key is read from the ZENSERP_API_KEY env var or the admin payment/
     * integration settings — it is never stored in the codebase.
     *
     * @return array<string, mixed>|null Null when no key or the call fails.
     */
    private function zenserpReport(string $keyword, string $targetHost, string $location): ?array
    {
        $apiKey = $this->serpApiKey();
        if ($apiKey === '') {
            return null;
        }

        $params = [
            'apikey' => $apiKey,
            'q' => $keyword,
            'num' => '20',
            'hl' => 'en',
            'gl' => 'ie',
            'location' => $location !== '' ? $location : 'Ireland',
        ];
        $endpoint = 'https://app.zenserp.com/api/v2/search?' . http_build_query($params);

        $context = stream_context_create([
            'http' => ['method' => 'GET', 'timeout' => 8, 'ignore_errors' => true, 'header' => "Accept: application/json\r\n"],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $raw = @file_get_contents($endpoint, false, $context, 0, 600000);
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        $organic = $data['organic'] ?? null;
        if (!is_array($organic)) {
            return null;
        }

        $results = [];
        foreach ($organic as $item) {
            $href = (string) ($item['url'] ?? '');
            $host = strtolower((string) parse_url($href, PHP_URL_HOST));
            if ($href === '' || $host === '') {
                continue;
            }
            $results[] = [
                'title' => trim((string) ($item['title'] ?? '')),
                'url' => $href,
                'host' => preg_replace('/^www\./', '', $host),
            ];
            if (count($results) >= 20) {
                break;
            }
        }

        $normalizedTarget = preg_replace('/^www\./', '', strtolower($targetHost));
        $position = null;
        foreach ($results as $index => $result) {
            if ($result['host'] === $normalizedTarget || str_ends_with((string) $result['host'], '.' . $normalizedTarget)) {
                $position = $index + 1;
                break;
            }
        }

        return [
            'engine' => 'Google live results (ZenSERP)',
            'query' => trim($keyword . ' — ' . ($location !== '' ? $location : 'Ireland')),
            'position' => $position,
            'results' => array_slice($results, 0, 10),
            'opportunities' => $this->serpOpportunities($position, $results),
        ];
    }

    private function serpApiKey(): string
    {
        $env = (string) (getenv('ZENSERP_API_KEY') ?: '');
        if ($env !== '') {
            return trim($env);
        }

        try {
            $settings = (new PayPalSettingsRepository())->current();
            return trim((string) ($settings['serp_api_key'] ?? ''));
        } catch (\Throwable) {
            return '';
        }
    }

    private function serpOpportunities(?int $position, array $results): array
    {
        $notes = [];
        $notes[] = $position === null ? 'Target domain was not found in the first 10 organic-style results.' : 'Target domain appears at position ' . $position . ' in this live check.';
        $notes[] = count($results) >= 5 ? 'Review title patterns from the top 5 results before writing or revising the page title.' : 'SERP returned limited results; run again later or try a more specific keyword.';
        $notes[] = 'Build a dedicated page for the exact keyword, add FAQ schema, strengthen internal links and compare headings against ranking pages.';
        return $notes;
    }

    private function firstMatch(string $pattern, string $html): string
    {
        return preg_match($pattern, $html, $match) ? trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
    }

    private function metaContent(string $html, string $name): string
    {
        $pattern = '/<meta\b(?=[^>]*(?:name|property)=["\']' . preg_quote($name, '/') . '["\'])[^>]*content=["\']([^"\']*)["\'][^>]*>/i';
        return preg_match($pattern, $html, $match) ? trim(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
    }

    private function linkHref(string $html, string $rel): string
    {
        $pattern = '/<link\b(?=[^>]*rel=["\']' . preg_quote($rel, '/') . '["\'])[^>]*href=["\']([^"\']*)["\'][^>]*>/i';
        return preg_match($pattern, $html, $match) ? trim(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
    }

    private function tagTexts(string $html, string $tag): array
    {
        if (!preg_match_all('/<' . preg_quote($tag, '/') . '\b[^>]*>(.*?)<\/' . preg_quote($tag, '/') . '>/is', $html, $matches)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $value): string => trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            $matches[1]
        )));
    }
}

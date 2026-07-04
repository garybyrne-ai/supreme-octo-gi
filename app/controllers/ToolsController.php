<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
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
            'title' => 'Free SEO Tools | Crest Web Media',
            'metaDescription' => 'Free SEO audit tools for titles, meta descriptions, headings, content, links, schema, images and indexability.',
        ], $this->toolAccessData(), $data));
    }

    public function serpChecker(array $data = []): void
    {
        $this->render('pages/serp-checker', array_replace([
            'title' => 'Free SERP Checker | Crest Web Media',
            'metaDescription' => 'Free SERP checker for keyword visibility, ranking position, competitor pages and search result opportunities.',
        ], $this->toolAccessData(), $data));
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
                'title' => 'Security Testing Tools',
                'url' => '/free-penetration-testing-tools',
                'icon' => 'fa-solid fa-shield-halved',
                'category' => 'Security',
                'summary' => 'Run defensive checks for headers, DNS, TLS, security.txt, passwords, hashes, JWTs and launch readiness.',
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

        (new LeadMailer())->sendCode($email, $name, $code);
        (new AuditLogger())->log('tools.access_code_sent', ['email' => $email]);
        $this->redirectToTool('Code sent. Check your email and enter it below.', true);
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
        $this->seoTools([
            'targetUrl' => $url,
            'keyword' => $keyword,
            'seoResult' => $this->seoAudit($url, $html, $keyword),
        ]);
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

    public function analyzeHeaders(): void
    {
        if (!$this->hasToolAccess()) {
            $this->index(['accessError' => 'Register and verify your email to reveal security header results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_headers', 10, 900)) {
            http_response_code(429);
            $this->index(['toolError' => 'Too many scans. Please wait a few minutes before running another check.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->index(['toolError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $url = $this->normalizePublicUrl((string) ($_POST['target_url'] ?? ''));
        if ($url === null) {
            http_response_code(422);
            $this->index(['toolError' => 'Enter a public HTTPS or HTTP URL. Private networks, localhost, credentials and custom ports are blocked for safety.']);
            return;
        }

        if (!$this->consumeFreeScan('security_headers')) {
            http_response_code(402);
            $this->index(['toolError' => $this->growthLabLimitMessage()]);
            return;
        }

        $headers = $this->fetchHeaders($url);
        if ($headers === []) {
            $this->index(['toolError' => 'Could not read response headers from that site. Try the exact homepage URL.']);
            return;
        }

        (new AuditLogger())->log('tools.headers_analyzed', ['host' => parse_url($url, PHP_URL_HOST)]);

        $this->index([
            'targetUrl' => $url,
            'headerResult' => $this->scoreHeaders($headers),
        ]);
    }

    public function analyzeDns(): void
    {
        if (!$this->hasToolAccess()) {
            $this->index(['accessError' => 'Register and verify your email to reveal DNS results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_dns', 12, 900)) {
            http_response_code(429);
            $this->index(['dnsError' => 'Too many DNS checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->index(['dnsError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $host = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        if ($host === null) {
            http_response_code(422);
            $this->index(['dnsError' => 'Enter a valid public domain. Private networks and localhost are blocked.']);
            return;
        }

        if (!$this->consumeFreeScan('dns_email')) {
            http_response_code(402);
            $this->index(['dnsError' => $this->growthLabLimitMessage()]);
            return;
        }

        (new AuditLogger())->log('tools.dns_analyzed', ['host' => $host]);
        $this->index([
            'dnsHost' => $host,
            'dnsResult' => $this->dnsReport($host),
        ]);
    }

    public function analyzeTls(): void
    {
        if (!$this->hasToolAccess()) {
            $this->index(['accessError' => 'Register and verify your email to reveal TLS results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_tls', 10, 900)) {
            http_response_code(429);
            $this->index(['tlsError' => 'Too many TLS checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->index(['tlsError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $host = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        if ($host === null) {
            http_response_code(422);
            $this->index(['tlsError' => 'Enter a valid public domain. Private networks and localhost are blocked.']);
            return;
        }

        if (!$this->consumeFreeScan('tls_certificate')) {
            http_response_code(402);
            $this->index(['tlsError' => $this->growthLabLimitMessage()]);
            return;
        }

        $report = $this->tlsReport($host);
        if ($report === []) {
            $this->index(['tlsError' => 'Could not read a TLS certificate on port 443 for that host.']);
            return;
        }

        (new AuditLogger())->log('tools.tls_analyzed', ['host' => $host]);
        $this->index([
            'tlsHost' => $host,
            'tlsResult' => $report,
        ]);
    }

    public function analyzeWellKnown(): void
    {
        if (!$this->hasToolAccess()) {
            $this->index(['accessError' => 'Register and verify your email to reveal discovery results.']);
            return;
        }

        if (Security::hitRateLimit('security_tools_well_known', 10, 900)) {
            http_response_code(429);
            $this->index(['wellKnownError' => 'Too many discovery checks. Please wait a few minutes before trying again.']);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->index(['wellKnownError' => 'Your secure form token expired. Please try again.']);
            return;
        }

        $host = $this->normalizePublicHost((string) ($_POST['domain'] ?? ''));
        if ($host === null) {
            http_response_code(422);
            $this->index(['wellKnownError' => 'Enter a valid public domain. Private networks and localhost are blocked.']);
            return;
        }

        if (!$this->consumeFreeScan('well_known_discovery')) {
            http_response_code(402);
            $this->index(['wellKnownError' => $this->growthLabLimitMessage()]);
            return;
        }

        (new AuditLogger())->log('tools.well_known_analyzed', ['host' => $host]);
        $this->index([
            'wellKnownHost' => $host,
            'wellKnownResult' => $this->wellKnownReport($host),
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

        $email = (string) ($_SESSION['tool_lead']['email'] ?? '');
        if ($email === '') {
            return false;
        }

        $usage = (new ToolUsageRepository())->consume($email, $tool);
        return (bool) ($usage['allowed'] ?? false);
    }

    private function growthLabLimitMessage(): string
    {
        return 'You have used your 3 free scans for today. Upgrade with credits or a Growth Lab Pass for deeper data, white-label PDF reports and continuous background monitoring.';
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
            'strict-transport-security' => ['label' => 'HTTP Strict Transport Security', 'weight' => 20],
            'content-security-policy' => ['label' => 'Content Security Policy', 'weight' => 20],
            'x-frame-options' => ['label' => 'Clickjacking protection', 'weight' => 15],
            'x-content-type-options' => ['label' => 'MIME sniffing protection', 'weight' => 10],
            'referrer-policy' => ['label' => 'Referrer privacy policy', 'weight' => 10],
            'permissions-policy' => ['label' => 'Browser permissions policy', 'weight' => 10],
        ];

        $score = 15;
        $rows = [];
        foreach ($checks as $header => $check) {
            $present = !empty($headers[$header]);
            $score += $present ? $check['weight'] : 0;
            $rows[] = [
                'label' => $check['label'],
                'present' => $present,
                'value' => $present ? $headers[$header] : 'Missing',
            ];
        }

        return [
            'score' => min(100, $score),
            'status' => $headers['status'][0] ?? 'No status line',
            'checks' => $rows,
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
                ['label' => 'A/AAAA public records', 'present' => ($records['A'] !== [] || $records['AAAA'] !== []), 'value' => $this->recordSummary(array_merge($records['A'], $records['AAAA']), ['ip', 'ipv6'])],
                ['label' => 'Mail exchanger records', 'present' => $records['MX'] !== [], 'value' => $this->recordSummary($records['MX'], ['target'])],
                ['label' => 'SPF email protection', 'present' => $spf !== [], 'value' => $spf[0]['txt'] ?? 'Missing'],
                ['label' => 'DMARC email policy', 'present' => $dmarc !== [], 'value' => $dmarcPolicy ?: 'Missing'],
                ['label' => 'CAA certificate authority control', 'present' => $records['CAA'] !== [], 'value' => $this->recordSummary($records['CAA'], ['value', 'tag'])],
                ['label' => 'Nameservers', 'present' => $records['NS'] !== [], 'value' => $this->recordSummary($records['NS'], ['target'])],
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
        $text = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('#<(script|style|noscript)\b[^>]*>.*?</\1>#is', ' ', $html))) ?? '');
        $title = $this->firstMatch('/<title[^>]*>(.*?)<\/title>/is', $html);
        $description = $this->metaContent($html, 'description');
        $robots = $this->metaContent($html, 'robots');
        $canonical = $this->linkHref($html, 'canonical');
        $h1s = $this->tagTexts($html, 'h1');
        $h2s = $this->tagTexts($html, 'h2');
        $images = preg_match_all('/<img\b[^>]*>/i', $html, $imageMatches) ? $imageMatches[0] : [];
        $links = preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\']/i', $html, $linkMatches) ? $linkMatches[1] : [];
        $schemaCount = preg_match_all('/<script\b[^>]*type=["\']application\/ld\+json["\']/i', $html);
        $words = $text === '' ? 0 : str_word_count($text);
        $keywordCount = $keyword !== '' ? substr_count(strtolower($text), strtolower($keyword)) : 0;
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $internal = 0;
        $external = 0;
        foreach ($links as $link) {
            $linkHost = parse_url($link, PHP_URL_HOST);
            if ($linkHost === null || $linkHost === false || $linkHost === '' || strtolower((string) $linkHost) === strtolower($host)) {
                $internal++;
            } else {
                $external++;
            }
        }
        $missingAlt = 0;
        foreach ($images as $image) {
            if (!preg_match('/\salt=["\'][^"\']+["\']/i', $image)) {
                $missingAlt++;
            }
        }

        $checks = [
            ['label' => 'Title tag', 'present' => $title !== '' && strlen($title) <= 65, 'value' => $title !== '' ? $title . ' (' . strlen($title) . ' chars)' : 'Missing'],
            ['label' => 'Meta description', 'present' => $description !== '' && strlen($description) <= 165, 'value' => $description !== '' ? $description . ' (' . strlen($description) . ' chars)' : 'Missing'],
            ['label' => 'Single H1', 'present' => count($h1s) === 1, 'value' => count($h1s) . ' found: ' . implode(' | ', array_slice($h1s, 0, 3))],
            ['label' => 'Content depth', 'present' => $words >= 450, 'value' => $words . ' words'],
            ['label' => 'Canonical URL', 'present' => $canonical !== '', 'value' => $canonical ?: 'Missing'],
            ['label' => 'Schema markup', 'present' => $schemaCount > 0, 'value' => $schemaCount . ' JSON-LD block(s)'],
            ['label' => 'Image alt text', 'present' => $missingAlt === 0, 'value' => count($images) . ' images, ' . $missingAlt . ' missing alt text'],
            ['label' => 'Indexability', 'present' => !str_contains(strtolower($robots), 'noindex'), 'value' => $robots ?: 'No noindex directive found'],
            ['label' => 'Keyword usage', 'present' => $keyword === '' || $keywordCount > 0, 'value' => $keyword === '' ? 'No focus keyword provided' : $keywordCount . ' exact mention(s)'],
        ];

        $score = (int) round((count(array_filter($checks, static fn (array $check): bool => $check['present'])) / count($checks)) * 100);

        return [
            'score' => $score,
            'title' => $title,
            'description' => $description,
            'h1s' => $h1s,
            'h2s' => array_slice($h2s, 0, 8),
            'words' => $words,
            'internal_links' => $internal,
            'external_links' => $external,
            'checks' => $checks,
        ];
    }

    private function serpReport(string $keyword, string $targetHost, string $location): array
    {
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

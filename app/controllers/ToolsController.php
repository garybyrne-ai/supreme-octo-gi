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
        $seoResult = $this->seoAudit($url, $html, $keyword);
        $this->seoTools([
            'targetUrl' => $url,
            'keyword' => $keyword,
            'seoResult' => $seoResult,
            'report' => [
                'tool' => 'On-Page SEO',
                'icon' => 'fa-chart-line',
                'target' => $url,
                'score' => $seoResult['score'],
                'summary' => 'On-page and technical SEO signals for ' . ($keyword !== '' ? '“' . $keyword . '”' : 'this page') . '.',
                'facts' => [
                    ['label' => 'Words', 'value' => (string) $seoResult['words']],
                    ['label' => 'Internal links', 'value' => (string) $seoResult['internal_links']],
                    ['label' => 'External links', 'value' => (string) $seoResult['external_links']],
                    ['label' => 'Keyword density', 'value' => ($seoResult['keyword_density'] ?? 0) . '%'],
                ],
                'checks' => $seoResult['checks'],
            ],
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

        $ogTitle = $this->metaContent($html, 'og:title');
        $ogImage = $this->metaContent($html, 'og:image');
        $twitterCard = $this->metaContent($html, 'twitter:card');
        $hasViewport = (bool) preg_match('/<meta\b[^>]*name=["\']viewport["\']/i', $html);
        $hasLang = (bool) preg_match('/<html\b[^>]*\blang=/i', $html);
        $hasFavicon = (bool) preg_match('/<link\b[^>]*rel=["\'][^"\']*icon[^"\']*["\']/i', $html);
        $density = $words > 0 && $keyword !== '' ? round(($keywordCount / max(1, $words)) * 100, 2) : 0.0;

        $checks = [
            ['label' => 'Title tag length', 'present' => $title !== '' && strlen($title) <= 65, 'value' => $title !== '' ? $title . ' (' . strlen($title) . ' chars)' : 'Missing', 'advice' => 'Write a unique 15–65 character title with the primary keyword near the front.'],
            ['label' => 'Meta description', 'present' => $description !== '' && strlen($description) <= 165, 'value' => $description !== '' ? $description . ' (' . strlen($description) . ' chars)' : 'Missing', 'advice' => 'Add a compelling 120–160 character meta description with a call to action.'],
            ['label' => 'Single H1', 'present' => count($h1s) === 1, 'value' => count($h1s) . ' found: ' . implode(' | ', array_slice($h1s, 0, 3)), 'advice' => 'Use exactly one H1 that states the page topic.'],
            ['label' => 'Content depth', 'present' => $words >= 450, 'value' => $words . ' words', 'advice' => 'Aim for 600+ words of genuinely useful content for competitive terms.'],
            ['label' => 'Canonical URL', 'present' => $canonical !== '', 'value' => $canonical ?: 'Missing', 'advice' => 'Add a self-referencing canonical link to avoid duplicate-content dilution.'],
            ['label' => 'Structured data (schema)', 'present' => $schemaCount > 0, 'value' => $schemaCount . ' JSON-LD block(s)', 'advice' => 'Add JSON-LD schema (Organization, Service, FAQ, Breadcrumb) for rich results.'],
            ['label' => 'Image alt text', 'present' => $missingAlt === 0, 'value' => count($images) . ' images, ' . $missingAlt . ' missing alt text', 'advice' => 'Add descriptive alt text to every meaningful image.'],
            ['label' => 'Indexability', 'present' => !str_contains(strtolower($robots), 'noindex'), 'value' => $robots ?: 'No noindex directive found', 'advice' => 'Remove any noindex directive if this page should rank.'],
            ['label' => 'Mobile viewport', 'present' => $hasViewport, 'value' => $hasViewport ? 'Responsive viewport set' : 'Missing viewport meta', 'advice' => 'Add <meta name="viewport" content="width=device-width, initial-scale=1">.'],
            ['label' => 'Language declared', 'present' => $hasLang, 'value' => $hasLang ? 'html lang attribute present' : 'Missing lang attribute', 'advice' => 'Set a lang attribute on <html> (e.g. lang="en").'],
            ['label' => 'Favicon', 'present' => $hasFavicon, 'value' => $hasFavicon ? 'Icon link present' : 'Missing', 'advice' => 'Add a favicon / site icon link for brand and trust signals.'],
            ['label' => 'Open Graph tags', 'present' => $ogTitle !== '' && $ogImage !== '', 'value' => $ogTitle !== '' ? 'og:title + og:image present' : 'Missing Open Graph tags', 'advice' => 'Add og:title, og:description and og:image for rich social sharing.'],
            ['label' => 'Twitter card', 'present' => $twitterCard !== '', 'value' => $twitterCard !== '' ? $twitterCard : 'Missing', 'advice' => 'Add a twitter:card meta tag (summary_large_image).'],
            ['label' => 'Keyword usage', 'present' => $keyword === '' || $keywordCount > 0, 'value' => $keyword === '' ? 'No focus keyword provided' : $keywordCount . ' mention(s), ' . $density . '% density', 'advice' => 'Use the focus keyword in the title, H1, first paragraph and naturally in the body.'],
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
            'keyword_density' => $density,
            'checks' => $checks,
        ];
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

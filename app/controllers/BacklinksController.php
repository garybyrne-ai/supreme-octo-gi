<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\SupportTicketRepository;
use App\Services\AuditLogger;

final class BacklinksController extends Controller
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function plans(): array
    {
        return [
            [
                'slug' => 'irish-starter',
                'name' => 'Irish Starter',
                'price' => '€59',
                'links' => '25 Irish (.ie) backlinks',
                'badge' => 'Rare .ie links',
                'featured' => false,
                'features' => [
                    '25 contextual links from real Irish (.ie) sites',
                    'Manual, white-hat editorial placements',
                    'Niche-relevant Irish blogs &amp; local sites',
                    'Mixed dofollow / nofollow natural profile',
                    'Live URL report you can verify',
                    'Delivered over 3–4 weeks (safe velocity)',
                ],
            ],
            [
                'slug' => 'irish-growth',
                'name' => 'Irish Growth',
                'price' => '€119',
                'links' => '60 Irish + niche backlinks',
                'badge' => 'Most popular',
                'featured' => true,
                'features' => [
                    'Everything in Irish Starter',
                    '60 links: .ie + high-relevance niche sites',
                    'Higher authority (DR 20–50) placements',
                    'Anchor-text diversity planning',
                    'Tiered internal + contextual links',
                    'Priority delivery with weekly updates',
                ],
            ],
            [
                'slug' => 'irish-authority',
                'name' => 'Irish Authority',
                'price' => '€249',
                'links' => '150 mixed authority backlinks',
                'badge' => 'Maximum impact',
                'featured' => false,
                'features' => [
                    'Everything in Irish Growth',
                    '150 links incl. premium .ie &amp; DR 50+ sites',
                    'Digital-PR style editorial mentions',
                    'Full anchor + landing-page strategy',
                    'Competitor gap-based targeting',
                    'Detailed monthly reporting',
                ],
            ],
        ];
    }

    public function index(array $data = []): void
    {
        $this->render('pages/backlinks', array_replace([
            'title' => 'Buy Irish (.ie) Backlinks — SEO Backlink Plans | Crest Web Media',
            'metaDescription' => 'Buy rare, white-hat Irish (.ie) backlinks. 25 Irish backlinks for €59. Manual, niche-relevant, editorial link building with a live URL report.',
            'plans' => self::plans(),
            'csrf' => Security::csrfToken(),
            'captcha' => Security::captchaChallenge('backlinks_order'),
            'schema' => $this->schema(),
        ], $data));
    }

    public function order(): void
    {
        $logger = new AuditLogger();

        if (Security::hitRateLimit('backlinks_order', 6, 900)) {
            http_response_code(429);
            $this->index(['error' => 'Too many order attempts. Please wait a few minutes and try again.', 'captcha' => Security::refreshCaptcha('backlinks_order')]);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->index(['error' => 'Your secure form token expired. Please try again.', 'captcha' => Security::refreshCaptcha('backlinks_order')]);
            return;
        }

        if (!Security::verifyCaptcha('backlinks_order', $_POST['captcha'] ?? null)) {
            http_response_code(422);
            $this->index(['error' => 'Security check failed. Please solve the new captcha.', 'captcha' => Security::refreshCaptcha('backlinks_order')]);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $website = trim((string) ($_POST['website'] ?? ''));
        $planSlug = (string) ($_POST['plan'] ?? '');
        $keywords = trim((string) ($_POST['keywords'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        $plan = null;
        foreach (self::plans() as $candidate) {
            if ($candidate['slug'] === $planSlug) {
                $plan = $candidate;
                break;
            }
        }

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $website === '' || $plan === null) {
            http_response_code(422);
            $this->index([
                'error' => 'Enter a valid name, email, website and choose a plan.',
                'captcha' => Security::refreshCaptcha('backlinks_order'),
                'old' => ['name' => $name, 'email' => $email, 'website' => $website, 'keywords' => $keywords, 'notes' => $notes, 'plan' => $planSlug],
            ]);
            return;
        }

        $message = sprintf(
            "Backlink order\nPlan: %s (%s — %s)\nWebsite: %s\nTarget keywords: %s\nNotes: %s",
            $plan['name'],
            $plan['price'],
            strip_tags((string) $plan['links']),
            $website,
            $keywords !== '' ? $keywords : '(none provided)',
            $notes !== '' ? $notes : '(none)'
        );

        $reference = (new SupportTicketRepository())->create([
            'name' => $name,
            'email' => $email,
            'subject' => 'Backlink order: ' . $plan['name'] . ' (' . $plan['price'] . ')',
            'priority' => 'high',
            'message' => $message,
            'ip_address' => Security::clientIp(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);

        $logger->log('backlinks.order_created', ['reference' => $reference, 'plan' => $plan['slug'], 'email' => $email]);

        $this->index([
            'success' => 'Order received — reference ' . $reference . '. We will email you a secure payment link and confirm the plan within one business day.',
            'captcha' => Security::refreshCaptcha('backlinks_order'),
        ]);
    }

    private function schema(): string
    {
        $items = [];
        foreach (self::plans() as $index => $plan) {
            $items[] = [
                '@type' => 'Offer',
                'position' => $index + 1,
                'name' => $plan['name'] . ' — ' . strip_tags((string) $plan['links']),
                'price' => ltrim((string) $plan['price'], '€'),
                'priceCurrency' => 'EUR',
                'category' => 'SEO backlink building',
            ];
        }

        return (string) json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'serviceType' => 'Irish (.ie) backlink building',
            'provider' => ['@type' => 'Organization', 'name' => 'Crest Web Media'],
            'areaServed' => 'IE',
            'description' => 'White-hat, editorial Irish (.ie) backlink building with live URL reporting.',
            'offers' => ['@type' => 'AggregateOffer', 'priceCurrency' => 'EUR', 'lowPrice' => '59', 'highPrice' => '249', 'offerCount' => count($items), 'offers' => $items],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

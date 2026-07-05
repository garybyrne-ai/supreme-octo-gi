<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\BacklinkPlanRepository;
use App\Models\CouponRepository;
use App\Models\SupportTicketRepository;
use App\Services\AuditLogger;

final class BacklinksController extends Controller
{
    /**
     * Active, backend-managed backlink plans for the public page.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function plans(): array
    {
        return (new BacklinkPlanRepository())->activePlans();
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
        $couponCode = trim((string) ($_POST['coupon'] ?? ''));

        $plan = null;
        foreach (self::plans() as $candidate) {
            if ($candidate['slug'] === $planSlug) {
                $plan = $candidate;
                break;
            }
        }

        $old = ['name' => $name, 'email' => $email, 'website' => $website, 'keywords' => $keywords, 'notes' => $notes, 'plan' => $planSlug, 'coupon' => $couponCode];

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $website === '' || $plan === null) {
            http_response_code(422);
            $this->index([
                'error' => 'Enter a valid name, email, website and choose a plan.',
                'captcha' => Security::refreshCaptcha('backlinks_order'),
                'old' => $old,
            ]);
            return;
        }

        // Optional coupon: validate for backlinks and record on the ticket.
        $couponLine = '';
        $coupons = new CouponRepository();
        if ($couponCode !== '') {
            $check = $coupons->validate($couponCode, 'backlinks');
            if (!($check['ok'] ?? false)) {
                http_response_code(422);
                $this->index([
                    'error' => $check['message'] ?: 'That coupon code is not valid.',
                    'captcha' => Security::refreshCaptcha('backlinks_order'),
                    'old' => $old,
                ]);
                return;
            }
            $applied = $coupons->applyToPrice($check['coupon'], (string) $plan['price']);
            $couponLine = sprintf("\nCoupon: %s (%s) — %s -> %s", $check['coupon']['code'], $applied['label'], $applied['original'], $applied['discounted']);
        }

        $message = sprintf(
            "Backlink order\nPlan: %s (%s — %s)\nWebsite: %s%s\nTarget keywords: %s\nNotes: %s",
            $plan['name'],
            $plan['price'],
            strip_tags((string) $plan['links']),
            $website,
            $couponLine,
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

        if ($couponLine !== '') {
            $coupons->redeem($couponCode);
        }

        $logger->log('backlinks.order_created', ['reference' => $reference, 'plan' => $plan['slug'], 'email' => $email, 'coupon' => $couponCode !== '' ? strtoupper($couponCode) : null]);

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

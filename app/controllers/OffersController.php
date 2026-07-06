<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\CarePlanRepository;
use App\Models\CouponRepository;
use App\Models\SupportTicketRepository;
use App\Services\AuditLogger;

/**
 * Productized revenue offers: Website Care Plans (monthly retainers), the
 * one-off €49 Deep Website Audit and the €149 Speed Rescue service. Orders
 * follow the backlinks pattern — validated form → high-priority support
 * ticket → payment link emailed — with optional coupon codes applied and
 * recorded on the ticket.
 */
final class OffersController extends Controller
{
    /* ---------------------------------------------------------- care plans */

    public function carePlans(array $data = []): void
    {
        $plans = (new CarePlanRepository())->activePlans();

        $this->render('pages/care-plans', array_replace([
            'title' => 'Website Care Plans — Maintenance From €49/month | Crest Web Media',
            'metaDescription' => 'Monthly website maintenance plans: backups, updates, uptime & security monitoring, SEO reports, Google Ads management and e-commerce care. From €49/month.',
            'plans' => $plans,
            'csrf' => Security::csrfToken(),
            'captcha' => Security::captchaChallenge('care_order'),
            'schema' => $this->plansSchema($plans, 'Website maintenance & care plans', 'Monthly website maintenance retainers covering backups, updates, monitoring, SEO and e-commerce care.'),
        ], $data));
    }

    public function orderCarePlan(): void
    {
        $this->handleOrder(
            context: 'care-plans',
            rateKey: 'care_order',
            captchaContext: 'care_order',
            rerender: fn (array $data) => $this->carePlans($data),
            resolvePlan: function (string $slug): ?array {
                $plan = (new CarePlanRepository())->findBySlug($slug);
                if ($plan !== null && !empty($plan['active'])) {
                    return ['name' => $plan['name'], 'price' => $plan['price'] . ($plan['period'] ?? '/month'), 'raw_price' => $plan['price'], 'slug' => $plan['slug']];
                }
                return null;
            },
            subjectPrefix: 'Care plan order'
        );
    }

    /* --------------------------------------------------------------- audit */

    public function audit(array $data = []): void
    {
        $this->render('pages/website-audit', array_replace([
            'title' => 'Full Website Audit — €49 One-Time, White-Label PDF | Crest Web Media',
            'metaDescription' => 'A complete one-off website audit: security, SEO, speed, TLS, DNS/email and technology review delivered as a client-ready white-label PDF. €49, no subscription.',
            'csrf' => Security::csrfToken(),
            'captcha' => Security::captchaChallenge('audit_order'),
            'schema' => $this->singleOfferSchema('Full Website Audit', '49', 'Complete one-off website audit: security, SEO, performance, TLS, DNS and technology review delivered as a white-label PDF report.'),
        ], $data));
    }

    public function orderAudit(): void
    {
        $this->handleOrder(
            context: 'audit',
            rateKey: 'audit_order',
            captchaContext: 'audit_order',
            rerender: fn (array $data) => $this->audit($data),
            resolvePlan: fn (string $slug): ?array => ['name' => 'Full Website Audit', 'price' => '€49 one-time', 'raw_price' => '€49', 'slug' => 'website-audit'],
            subjectPrefix: 'Website audit order'
        );
    }

    /* -------------------------------------------------------- speed rescue */

    public function speedRescue(array $data = []): void
    {
        $this->render('pages/speed-rescue', array_replace([
            'title' => 'Speed Rescue — We Make Your Website Load In Under 2 Seconds | Crest Web Media',
            'metaDescription' => 'Fixed-price website speed optimisation: €149. Images, caching, code and server tuned until your site loads in under 2 seconds — or you don\'t pay.',
            'csrf' => Security::csrfToken(),
            'captcha' => Security::captchaChallenge('speed_order'),
            'schema' => $this->singleOfferSchema('Speed Rescue — Website Speed Optimisation', '149', 'Fixed-price website speed optimisation service. Images, caching, code and server configuration tuned for sub-2-second loads.'),
        ], $data));
    }

    public function orderSpeedRescue(): void
    {
        $this->handleOrder(
            context: 'speed-rescue',
            rateKey: 'speed_order',
            captchaContext: 'speed_order',
            rerender: fn (array $data) => $this->speedRescue($data),
            resolvePlan: fn (string $slug): ?array => ['name' => 'Speed Rescue', 'price' => '€149 fixed', 'raw_price' => '€149', 'slug' => 'speed-rescue'],
            subjectPrefix: 'Speed Rescue order'
        );
    }

    /* -------------------------------------------------------- shared order */

    /**
     * @param callable(array): void $rerender
     * @param callable(string): ?array $resolvePlan
     */
    private function handleOrder(
        string $context,
        string $rateKey,
        string $captchaContext,
        callable $rerender,
        callable $resolvePlan,
        string $subjectPrefix
    ): void {
        $logger = new AuditLogger();

        if (Security::hitRateLimit($rateKey, 6, 900)) {
            http_response_code(429);
            $rerender(['error' => 'Too many order attempts. Please wait a few minutes and try again.', 'captcha' => Security::refreshCaptcha($captchaContext)]);
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $rerender(['error' => 'Your secure form token expired. Please try again.', 'captcha' => Security::refreshCaptcha($captchaContext)]);
            return;
        }

        if (!Security::verifyCaptcha($captchaContext, $_POST['captcha'] ?? null)) {
            http_response_code(422);
            $rerender(['error' => 'Security check failed. Please solve the new captcha.', 'captcha' => Security::refreshCaptcha($captchaContext)]);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $website = trim((string) ($_POST['website'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $couponCode = trim((string) ($_POST['coupon'] ?? ''));
        $plan = $resolvePlan((string) ($_POST['plan'] ?? ''));

        $old = ['name' => $name, 'email' => $email, 'website' => $website, 'notes' => $notes, 'coupon' => $couponCode, 'plan' => $_POST['plan'] ?? ''];

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $website === '' || $plan === null) {
            http_response_code(422);
            $rerender(['error' => 'Enter a valid name, email and website, and choose a plan.', 'captcha' => Security::refreshCaptcha($captchaContext), 'old' => $old]);
            return;
        }

        // Optional coupon: validate for this context and record on the ticket.
        $couponLine = '';
        $coupons = new CouponRepository();
        if ($couponCode !== '') {
            $check = $coupons->validate($couponCode, $context);
            if (!($check['ok'] ?? false)) {
                http_response_code(422);
                $rerender(['error' => $check['message'] ?: 'That coupon code is not valid.', 'captcha' => Security::refreshCaptcha($captchaContext), 'old' => $old]);
                return;
            }
            $applied = $coupons->applyToPrice($check['coupon'], (string) $plan['raw_price']);
            $couponLine = sprintf(
                "\nCoupon: %s (%s) — %s -> %s",
                $check['coupon']['code'],
                $applied['label'],
                $applied['original'],
                $applied['discounted']
            );
        }

        $message = sprintf(
            "%s\nPlan: %s (%s)\nWebsite: %s%s\nNotes: %s",
            $subjectPrefix,
            $plan['name'],
            $plan['price'],
            $website,
            $couponLine,
            $notes !== '' ? $notes : '(none)'
        );

        $reference = (new SupportTicketRepository())->create([
            'name' => $name,
            'email' => $email,
            'subject' => $subjectPrefix . ': ' . $plan['name'] . ' (' . $plan['price'] . ')',
            'priority' => 'high',
            'message' => $message,
            'ip_address' => Security::clientIp(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);

        if ($couponLine !== '') {
            $coupons->redeem($couponCode);
        }

        $logger->log('offers.order_created', ['context' => $context, 'reference' => $reference, 'plan' => $plan['slug'], 'email' => $email, 'coupon' => $couponCode !== '' ? strtoupper($couponCode) : null]);

        $rerender([
            'success' => 'Order received — reference ' . $reference . '. We will email you a secure payment link and confirm scope within one business day.',
            'captcha' => Security::refreshCaptcha($captchaContext),
        ]);
    }

    /* -------------------------------------------------------------- schema */

    /**
     * @param array<int, array<string, mixed>> $plans
     */
    private function plansSchema(array $plans, string $serviceType, string $description): string
    {
        $items = [];
        $prices = [];
        foreach ($plans as $index => $plan) {
            $price = ltrim((string) preg_replace('/[^0-9.]/', '', (string) $plan['price']), '.');
            $prices[] = (float) $price;
            $items[] = [
                '@type' => 'Offer',
                'position' => $index + 1,
                'name' => $plan['name'] . ' — ' . strip_tags((string) ($plan['ideal_for'] ?? '')),
                'price' => $price,
                'priceCurrency' => 'EUR',
                'category' => $serviceType,
            ];
        }

        return (string) json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'serviceType' => $serviceType,
            'provider' => ['@type' => 'Organization', 'name' => 'Crest Web Media'],
            'areaServed' => ['IE', 'GB', 'US', 'EU'],
            'description' => $description,
            'offers' => [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'EUR',
                'lowPrice' => $prices !== [] ? (string) min($prices) : '49',
                'highPrice' => $prices !== [] ? (string) max($prices) : '199',
                'offerCount' => count($items),
                'offers' => $items,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function singleOfferSchema(string $name, string $price, string $description): string
    {
        return (string) json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'serviceType' => $name,
            'provider' => ['@type' => 'Organization', 'name' => 'Crest Web Media'],
            'areaServed' => ['IE', 'GB', 'US', 'EU'],
            'description' => $description,
            'offers' => ['@type' => 'Offer', 'price' => $price, 'priceCurrency' => 'EUR'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

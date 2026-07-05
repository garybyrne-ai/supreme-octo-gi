<?php
/** @var array<int, array<string,mixed>> $plans */
/** @var \App\Models\MembershipPlanRepository $planRepo */
$plans = $plans ?? [];
$bySlug = [];
foreach ($plans as $p) {
    $bySlug[$p['slug']] = $p;
}
$monthly = $bySlug['growth-lab-monthly'] ?? null;
$annual = $bySlug['growth-lab-annual'] ?? null;

$features = [
    'Unlimited security, DNS, TLS &amp; SEO scans (no daily cap)',
    'Every tool: headers, DNS/email, TLS, security.txt, tech-stack, SEO, SERP',
    'White-label reports — your agency name &amp; logo',
    'Download client-ready PDF reports for every scan',
    'Keyword density, Open Graph &amp; technical SEO checks',
    'Priority processing and continuous monitoring',
    'Cancel anytime — no lock-in',
];

$planButtons = function (?array $plan, string $fallbackPrice, string $csrf): void {
    if ($plan === null) {
        echo '<a class="pill-button" href="/contact?plan=growth-lab">Get started (' . e($fallbackPrice) . ') <i class="fa-solid fa-arrow-right"></i></a>';
        return;
    }
    $hasStripe = !empty($plan['stripe_payment_link']) || !empty($plan['stripe_price_id']);
    $hasPaypal = !empty($plan['paypal_subscribe_url']) || !empty($plan['paypal_plan_id']);
    if (!$hasStripe && !$hasPaypal) {
        echo '<a class="pill-button" href="/contact?plan=' . e($plan['slug']) . '">Get started <i class="fa-solid fa-arrow-right"></i></a>';
        return;
    }
    echo '<form method="post" action="/membership/join" class="tools-price-form">';
    echo '<input type="hidden" name="_csrf" value="' . e($csrf) . '">';
    echo '<input type="hidden" name="plan_id" value="' . e((string) $plan['id']) . '">';
    echo '<input type="email" name="email" placeholder="you@company.com" autocomplete="email">';
    if ($hasStripe) {
        echo '<button class="pill-button" type="submit" name="gateway" value="stripe">Pay by card <i class="fa-solid fa-credit-card"></i></button>';
    }
    if ($hasPaypal) {
        echo '<button class="pill-button ghost" type="submit" name="gateway" value="paypal">PayPal <i class="fa-brands fa-paypal"></i></button>';
    }
    echo '</form>';
};
?>
<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Growth Lab Pro</span>
    <h1>The deepest on-page &amp; technical SEO audit. <span>White-label reports.</span> One simple price.</h1>
    <p>Run every on-page SEO, security and technical website tool without limits, remove our branding, add your own logo and download client-ready PDF reports. Built for agencies and freelancers who want depth per euro and reports that look like a €300 audit.</p>
</section>

<section class="section reveal">
    <p class="pricing-upsell-banner"><i class="fa-solid fa-bolt"></i> Start with a <strong>7-day free trial</strong> on monthly, or pay yearly and get <strong>2 months free (save €100)</strong>.</p>

    <div class="tools-price-wrap">
        <article class="tools-price-card cyber-card is-featured">
            <span class="membership-badge">Most popular</span>
            <header class="membership-card-head">
                <h2>Growth Lab Pro</h2>
                <p>Billed monthly</p>
            </header>
            <div class="membership-card-price">
                <strong>€25</strong><span>/month</span>
            </div>
            <p class="membership-trial"><i class="fa-solid fa-bolt"></i> 7-day free trial — cancel anytime</p>
            <ul class="check-list">
                <?php foreach ($features as $f): ?><li><?= $f ?></li><?php endforeach; ?>
            </ul>
            <div class="membership-card-actions">
                <?php $planButtons($monthly, '€25/mo', $csrf); ?>
            </div>
        </article>

        <article class="tools-price-card cyber-card is-featured">
            <span class="membership-badge">Best value</span>
            <header class="membership-card-head">
                <h2>Growth Lab Pro</h2>
                <p>Billed yearly · 2 months free</p>
            </header>
            <div class="membership-card-price">
                <strong>€200</strong><span>/year</span>
            </div>
            <p class="membership-trial"><i class="fa-solid fa-circle-check"></i> 2 months free vs monthly — save €100</p>
            <ul class="check-list">
                <?php foreach ($features as $f): ?><li><?= $f ?></li><?php endforeach; ?>
            </ul>
            <div class="membership-card-actions">
                <?php $planButtons($annual, '€200/yr', $csrf); ?>
            </div>
        </article>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">What you can run</span>
        <h2>A full technical &amp; SEO toolkit</h2>
        <p>On-page &amp; technical SEO, live SERP checks, full security scanning and technology detection — all white-label and exportable.</p>
    </div>
    <div class="tools-related-grid">
        <a class="tools-related-chip" href="/seo-tools"><i class="fa-solid fa-chart-line"></i><span>SEO Audit</span></a>
        <a class="tools-related-chip" href="/serp-checker"><i class="fa-solid fa-ranking-star"></i><span>SERP Checker</span></a>
        <a class="tools-related-chip" href="/tools/security-headers"><i class="fa-solid fa-lock"></i><span>Security Headers</span></a>
        <a class="tools-related-chip" href="/tools/dns-email"><i class="fa-solid fa-envelope-circle-check"></i><span>DNS &amp; Email</span></a>
        <a class="tools-related-chip" href="/tools/tls-ssl"><i class="fa-solid fa-certificate"></i><span>TLS / SSL</span></a>
        <a class="tools-related-chip" href="/tools/tech-stack"><i class="fa-solid fa-microchip"></i><span>Tech Stack</span></a>
        <a class="tools-related-chip" href="/tools/security-txt"><i class="fa-solid fa-file-shield"></i><span>security.txt</span></a>
    </div>
</section>

<section class="section narrow reveal">
    <div class="section-heading"><h2>Membership FAQ</h2></div>
    <div class="faq-list">
        <details><summary>Can I put my own logo on the reports?</summary><p>Yes. On any report, open the report toolbar, add your agency name and a logo URL, then download the PDF. Your branding is saved in your browser for next time.</p></details>
        <details><summary>Do you check backlinks like Ahrefs or SEMrush?</summary><p>No — and we are upfront about that. We do not run a third-party backlink index. What we do is go <strong>deeper than most tools on live on-page &amp; technical SEO</strong>: 28+ weighted checks, readability, content and keyword analysis, schema, Core Web Vitals signals, security and technology detection — all exportable as a white-label PDF. It complements a backlink database rather than replacing it. If you want your own site's backlinks, you can connect Google Search Console (see below).</p></details>
        <details><summary>Can I see my backlinks?</summary><p>You can see <strong>your own site's</strong> backlinks by connecting Google Search Console — Google reports the sites and pages linking to you. We can import that with your permission. We do not (and cannot honestly) provide competitor backlink data, which requires a web-wide crawl database.</p></details>
        <details><summary>Can I cancel anytime?</summary><p>Yes — monthly and yearly plans can be cancelled anytime and you keep access until the end of the paid period.</p></details>
        <details><summary>Do you offer team or agency access?</summary><p>Yes — see the <a href="/membership">membership plans</a> for team seats and agency licensing, or <a href="/contact">contact us</a>.</p></details>
    </div>
</section>

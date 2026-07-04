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
    <h1>SEMrush-style tools. <span>White-label reports.</span> One simple price.</h1>
    <p>Run every SEO, security and technical website tool without limits, remove our branding, add your own logo and download client-ready PDF reports. Perfect for agencies and freelancers.</p>
</section>

<section class="section reveal">
    <div class="tools-price-toggle" data-price-toggle>
        <button type="button" class="is-active" data-billing="monthly">Monthly</button>
        <button type="button" data-billing="annual">Yearly <em>Save €100</em></button>
    </div>

    <div class="tools-price-wrap">
        <article class="tools-price-card cyber-card is-featured" data-billing-view="monthly">
            <span class="membership-badge">Most popular</span>
            <header class="membership-card-head">
                <h2>Growth Lab Pro</h2>
                <p>Billed monthly</p>
            </header>
            <div class="membership-card-price">
                <strong>€25</strong><span>/month</span>
            </div>
            <ul class="check-list">
                <?php foreach ($features as $f): ?><li><?= $f ?></li><?php endforeach; ?>
            </ul>
            <div class="membership-card-actions">
                <?php $planButtons($monthly, '€25/mo', $csrf); ?>
            </div>
        </article>

        <article class="tools-price-card cyber-card is-featured" data-billing-view="annual" hidden>
            <span class="membership-badge">Best value</span>
            <header class="membership-card-head">
                <h2>Growth Lab Pro</h2>
                <p>Billed yearly · 2 months free</p>
            </header>
            <div class="membership-card-price">
                <strong>€200</strong><span>/year</span>
            </div>
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
        <details><summary>Is this really like SEMrush or Ahrefs?</summary><p>Growth Lab Pro focuses on live on-page &amp; technical SEO, SERP checks, full website security scanning and technology detection — with white-label PDF export. It does not resell a third-party backlink index, so it complements rather than replaces a backlink database.</p></details>
        <details><summary>Can I cancel anytime?</summary><p>Yes — monthly and yearly plans can be cancelled anytime and you keep access until the end of the paid period.</p></details>
        <details><summary>Do you offer team or agency access?</summary><p>Yes — see the <a href="/membership">membership plans</a> for team seats and agency licensing, or <a href="/contact">contact us</a>.</p></details>
    </div>
</section>

<script>
(function () {
    var toggle = document.querySelector('[data-price-toggle]');
    if (!toggle) { return; }
    var buttons = toggle.querySelectorAll('[data-billing]');
    var views = document.querySelectorAll('[data-billing-view]');
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var billing = btn.getAttribute('data-billing');
            buttons.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            views.forEach(function (v) { v.hidden = v.getAttribute('data-billing-view') !== billing; });
        });
    });
})();
</script>

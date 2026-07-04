<?php
/** Cross-links between the individual tool pages + a pricing CTA. */
$allTools = [
    ['/tools/security-headers', 'fa-lock', 'Security Headers'],
    ['/tools/dns-email', 'fa-envelope-circle-check', 'DNS & Email'],
    ['/tools/tls-ssl', 'fa-certificate', 'TLS / SSL'],
    ['/tools/security-txt', 'fa-file-shield', 'security.txt'],
    ['/tools/tech-stack', 'fa-microchip', 'Tech Stack'],
    ['/tools/pagespeed', 'fa-gauge-high', 'PageSpeed'],
    ['/seo-tools', 'fa-chart-line', 'SEO Audit'],
    ['/serp-checker', 'fa-ranking-star', 'SERP Checker'],
];
$currentTool = $currentTool ?? '';
?>
<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">More Growth Lab tools</span>
        <h2>Run the full technical &amp; SEO suite</h2>
    </div>
    <div class="tools-related-grid">
        <?php foreach ($allTools as [$url, $icon, $label]): ?>
            <a class="tools-related-chip <?= $url === $currentTool ? 'is-active' : '' ?>" href="<?= e($url) ?>">
                <i class="fa-solid <?= e($icon) ?>"></i><span><?= e($label) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="home-tools-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker">Growth Lab Pro</span>
        <h2>Unlimited scans, white-label PDF reports and continuous monitoring.</h2>
        <p>Go beyond 3 free scans a day. Run every tool without limits, remove the branding, add your own logo and download client-ready PDF reports — from €25/month.</p>
        <div class="button-row">
            <a class="pill-button" href="/tools-pricing">See Pro Pricing <i class="fa-solid fa-arrow-right"></i></a>
            <a class="pill-button ghost" href="/membership">All Memberships <i class="fa-solid fa-id-card"></i></a>
        </div>
    </div>
</section>

<?php
// Growth Lab Pro upsell — shown at the END of every tool page.
$paypalSettings = $paypalSettings ?? [];
$passUrl = ($paypalSettings['growth_lab_19_url'] ?? '') ?: ($paypalSettings['growth_lab_49_url'] ?? '');
$passHref = $passUrl ?: '/tools-pricing';
$passExternal = $passUrl !== '';
$memberIsPro = $memberIsPro ?? false;
if ($memberIsPro) {
    return; // Pro members don't need the upsell
}
?>
<section class="section reveal tool-pro-cta">
    <div class="tool-pro-cta-inner">
        <div class="tool-pro-cta-copy">
            <span class="status-chip"><span></span> Growth Lab Pro</span>
            <h2>Run every tool without limits.</h2>
            <p>Free accounts get 3 scans a day. Go Pro for <strong>unlimited scans across every tool</strong>, white-label PDF reports with your own logo, the AI content assistant, rank tracking and continuous monitoring &mdash; <strong>€25/month or €200/year</strong>. Cancel anytime.</p>
            <ul class="tool-pro-cta-points">
                <li><i class="fa-solid fa-circle-check"></i> Unlimited audits &amp; scans</li>
                <li><i class="fa-solid fa-circle-check"></i> White-label PDF reports</li>
                <li><i class="fa-solid fa-circle-check"></i> Rank tracking &amp; monitoring</li>
            </ul>
        </div>
        <div class="tool-pro-cta-actions">
            <span class="tool-pro-cta-price">&euro;25<b>/mo</b></span>
            <a class="pill-button" href="<?= e($passHref) ?>"<?= $passExternal ? ' target="_blank" rel="noopener"' : '' ?>>Get Growth Lab Pro <i class="fa-solid fa-bolt"></i></a>
            <a class="pill-button ghost" href="/tools-pricing">See Pricing <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>

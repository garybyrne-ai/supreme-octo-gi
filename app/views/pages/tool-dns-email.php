<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Security Tool</span>
    <h1>DNS &amp; Email Security Checker</h1>
    <p>Check A/AAAA, MX, SPF, DMARC, CAA and nameserver records for deliverability and spoofing protection — then download a white-label report.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="section tool-single-wrap">
    <form class="cyber-form glass-tool tool-single-form" method="post" action="/free-penetration-testing-tools/dns">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Check DNS &amp; email records</h2>
        <p>Enter a public domain (no https:// needed).</p>
        <?php if (!empty($dnsError)): ?><div class="notice error"><?= e($dnsError) ?></div><?php endif; ?>
        <label>Domain
            <input name="domain" inputmode="url" placeholder="example.com" value="<?= e($dnsHost ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Run DNS Scan <i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <?php if (!empty($report)): require base_path('app/views/partials/tool-report.php'); endif; ?>
</section>

<?php $currentTool = '/tools/dns-email'; require base_path('app/views/partials/tools-related.php'); ?>

<?php require base_path('app/views/partials/tool-pro-cta.php'); ?>

<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Security Tool</span>
    <h1>TLS / SSL Certificate Checker</h1>
    <p>Read the HTTPS certificate on port 443 — issuer, subject, validity window and days to expiry — then download a white-label report.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="section tool-single-wrap">
    <form class="cyber-form glass-tool tool-single-form" method="post" action="/free-penetration-testing-tools/tls">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Check TLS certificate</h2>
        <p>Enter a public domain served over HTTPS.</p>
        <?php if (!empty($tlsError)): ?><div class="notice error"><?= e($tlsError) ?></div><?php endif; ?>
        <label>Domain
            <input name="domain" inputmode="url" placeholder="example.com" value="<?= e($tlsHost ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Run TLS Scan <i class="fa-solid fa-certificate"></i></button>
    </form>

    <?php if (!empty($report)): require base_path('app/views/partials/tool-report.php'); endif; ?>
</section>

<?php $currentTool = '/tools/tls-ssl'; require base_path('app/views/partials/tools-related.php'); ?>

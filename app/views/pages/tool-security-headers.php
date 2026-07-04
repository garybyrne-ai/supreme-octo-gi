<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Security Tool</span>
    <h1>Security Headers Checker</h1>
    <p>Scan HTTP response headers — HSTS, CSP, COOP, CORP, frame &amp; MIME protection, cookie flags and stack disclosure — then download a white-label PDF report for your client.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="section tool-single-wrap">
    <form class="cyber-form glass-tool tool-single-form" method="post" action="/free-penetration-testing-tools/headers">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Analyze security headers</h2>
        <p>Enter a public website URL. We send a single HEAD request — nothing is stored.</p>
        <?php if (!empty($toolError)): ?><div class="notice error"><?= e($toolError) ?></div><?php endif; ?>
        <label>Website URL
            <input name="target_url" type="url" inputmode="url" placeholder="https://example.com" value="<?= e($targetUrl ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Run Header Scan <i class="fa-solid fa-arrow-right"></i></button>
    </form>

    <?php if (!empty($report)): require base_path('app/views/partials/tool-report.php'); endif; ?>
</section>

<?php $currentTool = '/tools/security-headers'; require base_path('app/views/partials/tools-related.php'); ?>

<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Intelligence Tool</span>
    <h1>Website Technology Checker</h1>
    <p>Detect the CMS, frameworks, JavaScript libraries, analytics, CDN and server behind any website from public signals — then download a white-label report.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="section tool-single-wrap">
    <form class="cyber-form glass-tool tool-single-form" method="post" action="/tools/tech-stack">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Detect the technology stack</h2>
        <p>Enter a public website URL. We read the homepage HTML and response headers only.</p>
        <?php if (!empty($toolError)): ?><div class="notice error"><?= e($toolError) ?></div><?php endif; ?>
        <label>Website URL
            <input name="target_url" type="url" inputmode="url" placeholder="https://example.com" value="<?= e($targetUrl ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Detect Technology <i class="fa-solid fa-microchip"></i></button>
    </form>

    <?php if (!empty($report)): require base_path('app/views/partials/tool-report.php'); endif; ?>
</section>

<?php $currentTool = '/tools/tech-stack'; require base_path('app/views/partials/tools-related.php'); ?>

<?php require base_path('app/views/partials/tool-pro-cta.php'); ?>

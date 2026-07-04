<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Security Tool</span>
    <h1>security.txt &amp; Discovery Checker</h1>
    <p>Check for a responsible-disclosure <code>security.txt</code> and <code>robots.txt</code> over HTTPS — then download a white-label report.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="section tool-single-wrap">
    <form class="cyber-form glass-tool tool-single-form" method="post" action="/free-penetration-testing-tools/well-known">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Discover disclosure files</h2>
        <p>Enter a public domain. We only fetch well-known files — no crawling.</p>
        <?php if (!empty($wellKnownError)): ?><div class="notice error"><?= e($wellKnownError) ?></div><?php endif; ?>
        <label>Domain
            <input name="domain" inputmode="url" placeholder="example.com" value="<?= e($wellKnownHost ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Run Discovery <i class="fa-solid fa-file-shield"></i></button>
    </form>

    <?php if (!empty($report)): require base_path('app/views/partials/tool-report.php'); endif; ?>
</section>

<?php $currentTool = '/tools/security-txt'; require base_path('app/views/partials/tools-related.php'); ?>

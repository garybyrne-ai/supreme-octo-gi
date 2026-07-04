<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Performance Tool</span>
    <h1>PageSpeed &amp; Core Web Vitals Checker</h1>
    <p>Get <strong>real Google Lighthouse</strong> performance and Core Web Vitals (LCP, CLS, blocking time) for any page — mobile-first — then download a white-label PDF report.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="section tool-single-wrap">
    <form class="cyber-form glass-tool tool-single-form" method="post" action="/tools/pagespeed">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Run a PageSpeed test</h2>
        <p>Enter a public page URL. Results come straight from Google's PageSpeed Insights (this can take 10–20 seconds).</p>
        <?php if (!empty($toolError)): ?><div class="notice error"><?= e($toolError) ?></div><?php endif; ?>
        <label>Page URL
            <input name="target_url" type="url" inputmode="url" placeholder="https://example.com/page" value="<?= e($targetUrl ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Run PageSpeed Test <i class="fa-solid fa-gauge-high"></i></button>
    </form>

    <?php if (!empty($report)): require base_path('app/views/partials/tool-report.php'); endif; ?>
</section>

<?php $currentTool = '/tools/pagespeed'; require base_path('app/views/partials/tools-related.php'); ?>

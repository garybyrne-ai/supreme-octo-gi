<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Free SEO Audit</span>
    <h1>SEO Tool Built for Pages That Need to Win Work</h1>
    <p>Audit any public page for titles, meta descriptions, headings, schema, image alt text, internal links, content depth, indexability and keyword usage.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="split-section reveal">
    <form class="cyber-form glass-tool" method="post" action="/seo-tools/audit">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Run SEO Audit</h2>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <label>Page URL
            <input name="target_url" type="url" inputmode="url" placeholder="https://example.com/service-page" value="<?= e($targetUrl ?? '') ?>" required>
        </label>
        <label>Focus Keyword
            <input name="keyword" placeholder="web design ireland" value="<?= e($keyword ?? '') ?>">
        </label>
        <button class="pill-button" type="submit">Audit Page <i class="fa-solid fa-chart-line"></i></button>
    </form>

    <aside class="cyber-card tool-result-card">
        <?php if (!empty($seoResult)): ?>
            <span class="kicker"><?= e($targetUrl ?? '') ?></span>
            <h2><?= e((string) $seoResult['score']) ?>/100</h2>
            <p><?= e((string) $seoResult['words']) ?> words, <?= e((string) $seoResult['internal_links']) ?> internal links, <?= e((string) $seoResult['external_links']) ?> external links.</p>
        <?php else: ?>
            <span class="kicker">SEO Signal</span>
            <h2>Fast page-level SEO intelligence.</h2>
            <p>Use this before publishing landing pages for Ireland, UK, USA, Europe and AI integration services.</p>
        <?php endif; ?>
    </aside>
</section>

<?php if (!empty($report)): ?>
    <section class="section tool-single-wrap"><?php require base_path('app/views/partials/tool-report.php'); ?></section>
<?php endif; ?>

<?php if (!empty($seoResult)): ?>
    <section class="tool-results reveal">
        <article class="cyber-card tool-result-card">
            <h2>Audit Checks</h2>
            <div class="tool-check-list">
                <?php foreach ($seoResult['checks'] as $check): ?>
                    <div class="<?= $check['present'] ? 'is-good' : 'is-missing' ?>">
                        <i class="fa-solid <?= $check['present'] ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                        <span><strong><?= e($check['label']) ?></strong><small><?= e(excerpt($check['value'], 140)) ?></small></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="cyber-card tool-result-card">
            <h2>Search Preview</h2>
            <div class="serp-preview">
                <span><?= e(parse_url($targetUrl ?? '', PHP_URL_HOST) ?: 'example.com') ?></span>
                <strong><?= e($seoResult['title'] ?: 'Missing title tag') ?></strong>
                <p><?= e($seoResult['description'] ?: 'Missing meta description') ?></p>
            </div>
            <h3>Top Headings</h3>
            <div class="tag-cloud">
                <?php foreach (array_merge($seoResult['h1s'], $seoResult['h2s']) as $heading): ?>
                    <span><?= e(excerpt($heading, 70)) ?></span>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
<?php endif; ?>

<section class="tool-workbench reveal" id="seo-local-tools" data-tools-locked="<?= empty($toolLead) ? 'true' : 'false' ?>">
    <div class="cyber-form glass-tool" data-serp-preview-builder>
        <h2>SERP Preview Builder</h2>
        <label>Page Title <input name="title" maxlength="80" placeholder="Web Design Ireland | Crest Web Media"></label>
        <label>URL <input name="url" placeholder="https://example.com/service"></label>
        <label>Description <textarea name="description" rows="4" maxlength="190" placeholder="Write the search snippet here"></textarea></label>
        <button class="pill-button ghost" type="button">Preview Snippet <i class="fa-solid fa-ranking-star"></i></button>
        <output class="hash-output">SERP preview output</output>
    </div>

    <div class="cyber-form glass-tool" data-keyword-density>
        <h2>Keyword Density Analyzer</h2>
        <label>Focus Keyword <input name="keyword" placeholder="web design ireland"></label>
        <label>Page Copy <textarea name="copy" rows="7" placeholder="Paste page copy here"></textarea></label>
        <button class="pill-button ghost" type="button">Analyze Copy <i class="fa-solid fa-chart-simple"></i></button>
        <output class="hash-output">Keyword density output</output>
    </div>

    <div class="cyber-form glass-tool" data-schema-validator>
        <h2>JSON-LD Schema Validator</h2>
        <label>Schema JSON-LD
            <textarea rows="7" placeholder='{"@context":"https://schema.org","@type":"Service","name":"Web Design"}'></textarea>
        </label>
        <button class="pill-button ghost" type="button">Validate Schema <i class="fa-solid fa-code"></i></button>
        <output class="hash-output">Schema validation output</output>
    </div>

    <div class="cyber-form glass-tool" data-robots-builder>
        <h2>Robots Meta Builder</h2>
        <div class="tool-toggle-grid">
            <label><input type="checkbox" name="index" checked> Index</label>
            <label><input type="checkbox" name="follow" checked> Follow</label>
            <label><input type="checkbox" name="archive"> No archive</label>
            <label><input type="checkbox" name="snippet"> No snippet</label>
        </div>
        <button class="pill-button ghost" type="button">Build Tag <i class="fa-solid fa-robot"></i></button>
        <output class="hash-output">Robots meta output</output>
    </div>
</section>

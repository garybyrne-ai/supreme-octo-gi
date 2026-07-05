<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Free SEO Audit</span>
    <h1>The Deepest Free On-Page &amp; Technical SEO Audit in Ireland</h1>
    <p>28+ weighted checks on any public page — titles, meta, heading hierarchy, schema, Core Web Vitals signals, HTTPS, links, readability and full keyword &amp; content analysis — with a prioritised fix list and a white-label PDF you can hand to clients.</p>
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
    <section class="section tool-single-wrap">
        <?php if (!empty($reportEmailed)): ?>
            <p class="tool-inline-note"><i class="fa-solid fa-envelope-circle-check"></i> A copy of this report has been emailed to you for your records.</p>
        <?php endif; ?>
        <?php require base_path('app/views/partials/tool-report.php'); ?>
    </section>
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

<?php if (!empty($seoResult) && (!empty($seoResult['top_terms']) || !empty($seoResult['priority_fixes']))): ?>
    <section class="tool-results reveal seo-analysis-grid">
        <?php if (!empty($seoResult['priority_fixes'])): ?>
        <article class="cyber-card tool-result-card">
            <h2><i class="fa-solid fa-list-check"></i> Priority Fixes</h2>
            <p class="seo-analysis-note">The highest-impact issues first — fix these in order.</p>
            <ol class="seo-priority-list">
                <?php foreach ($seoResult['priority_fixes'] as $fix): ?>
                    <li><strong><?= e($fix['label']) ?></strong><span><?= e($fix['advice']) ?></span></li>
                <?php endforeach; ?>
            </ol>
        </article>
        <?php endif; ?>
        <article class="cyber-card tool-result-card">
            <h2><i class="fa-solid fa-magnifying-glass-chart"></i> Content Analysis</h2>
            <p class="seo-analysis-note">What this page is actually "about" to a search engine — its most prominent terms and phrases.</p>
            <?php if (!empty($seoResult['top_terms'])): ?>
                <h3>Top terms</h3>
                <div class="tag-cloud">
                    <?php foreach ($seoResult['top_terms'] as $t): ?>
                        <span><?= e($t['term']) ?> <b><?= e((string) $t['count']) ?></b></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($seoResult['top_phrases'])): ?>
                <h3>Top phrases</h3>
                <div class="tag-cloud">
                    <?php foreach ($seoResult['top_phrases'] as $t): ?>
                        <span><?= e($t['term']) ?> <b><?= e((string) $t['count']) ?></b></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="seo-analysis-metrics">
                <span>Readability <b><?= e((string) ($seoResult['readability'] ?? 0)) ?></b> <?= e((string) ($seoResult['reading_label'] ?? '')) ?></span>
                <span>Page weight <b><?= e((string) ($seoResult['page_weight_kb'] ?? 0)) ?> KB</b></span>
                <span>Text/HTML ratio <b><?= e((string) ($seoResult['content_ratio'] ?? 0)) ?>%</b></span>
                <?php if (!empty($seoResult['schema_types'])): ?><span>Schema <b><?= e(implode(', ', array_slice($seoResult['schema_types'], 0, 4))) ?></b></span><?php endif; ?>
            </div>
        </article>
    </section>
<?php endif; ?>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">On-page toolkit</span>
        <h2>Free SEO utilities</h2>
        <p>Preview your Google snippet, check keyword density, validate schema and build a robots tag — instant, private and free.</p>
    </div>
</section>

<section class="tool-workbench seo-workbench reveal" id="seo-local-tools" data-tools-locked="<?= empty($toolLead) ? 'true' : 'false' ?>">
    <div class="cyber-form glass-tool seo-tool-card" data-serp-preview-builder>
        <header class="seo-tool-head"><span class="seo-tool-ico"><i class="fa-solid fa-ranking-star"></i></span><div><h3>SERP Preview Builder</h3><small>See how your page looks in Google results</small></div></header>
        <label>Page Title <input name="title" maxlength="80" placeholder="Web Design Ireland | Crest Web Media"></label>
        <label>URL <input name="url" placeholder="https://example.com/service"></label>
        <label>Description <textarea name="description" rows="3" maxlength="190" placeholder="Write the search snippet here"></textarea></label>
        <button class="pill-button" type="button">Preview Snippet <i class="fa-solid fa-eye"></i></button>
        <output class="seo-tool-output">Your Google preview appears here.</output>
    </div>

    <div class="cyber-form glass-tool seo-tool-card" data-keyword-density>
        <header class="seo-tool-head"><span class="seo-tool-ico"><i class="fa-solid fa-chart-simple"></i></span><div><h3>Keyword Density Analyzer</h3><small>Check how often your keyword appears</small></div></header>
        <label>Focus Keyword <input name="keyword" placeholder="web design ireland"></label>
        <label>Page Copy <textarea name="copy" rows="6" placeholder="Paste page copy here"></textarea></label>
        <button class="pill-button" type="button">Analyze Copy <i class="fa-solid fa-magnifying-glass-chart"></i></button>
        <output class="seo-tool-output">Density breakdown appears here.</output>
    </div>

    <div class="cyber-form glass-tool seo-tool-card" data-schema-validator>
        <header class="seo-tool-head"><span class="seo-tool-ico"><i class="fa-solid fa-code"></i></span><div><h3>JSON-LD Schema Validator</h3><small>Check structured data for rich results</small></div></header>
        <label>Schema JSON-LD
            <textarea rows="6" placeholder='{"@context":"https://schema.org","@type":"Service","name":"Web Design"}'></textarea>
        </label>
        <button class="pill-button" type="button">Validate Schema <i class="fa-solid fa-circle-check"></i></button>
        <output class="seo-tool-output">Validation result appears here.</output>
    </div>

    <div class="cyber-form glass-tool seo-tool-card" data-robots-builder>
        <header class="seo-tool-head"><span class="seo-tool-ico"><i class="fa-solid fa-robot"></i></span><div><h3>Robots Meta Builder</h3><small>Control how search engines crawl the page</small></div></header>
        <div class="tool-toggle-grid">
            <label><input type="checkbox" name="index" checked> Index</label>
            <label><input type="checkbox" name="follow" checked> Follow</label>
            <label><input type="checkbox" name="archive"> No archive</label>
            <label><input type="checkbox" name="snippet"> No snippet</label>
        </div>
        <button class="pill-button" type="button">Build Tag <i class="fa-solid fa-wrench"></i></button>
        <output class="seo-tool-output">Generated tag appears here.</output>
    </div>
</section>

<?php require base_path('app/views/partials/tool-pro-cta.php'); ?>

<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Free SERP Checker</span>
    <h1>SERP Checker for Keywords, Domains and Competitors</h1>
    <p>Check live organic-style results, see whether your domain appears in the top 10, and capture quick opportunities before creating a landing page.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="split-section reveal">
    <form class="cyber-form glass-tool" method="post" action="/serp-checker/check">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Check SERP</h2>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <label>Keyword
            <input name="keyword" placeholder="web design ireland" value="<?= e($keyword ?? '') ?>" required>
        </label>
        <label>Target Domain
            <input name="domain" placeholder="crestwebmedia.com" value="<?= e($domain ?? '') ?>" required>
        </label>
        <label>Location Modifier
            <input name="location" placeholder="Ireland" value="<?= e($location ?? 'Ireland') ?>">
        </label>
        <button class="pill-button" type="submit">Check Position <i class="fa-solid fa-ranking-star"></i></button>
    </form>

    <aside class="cyber-card tool-result-card">
        <?php if (!empty($serpResult)): ?>
            <span class="kicker"><?= e($serpResult['engine']) ?></span>
            <h2><?= $serpResult['position'] ? '#' . e((string) $serpResult['position']) : 'Not Top 10' ?></h2>
            <p>Query checked: <?= e($serpResult['query']) ?></p>
        <?php else: ?>
            <span class="kicker">Rank Signal</span>
            <h2>Find what already ranks.</h2>
            <p>Use this before building city, country, service and AI automation pages.</p>
        <?php endif; ?>
    </aside>
</section>

<?php if (!empty($serpResult)): ?>
    <section class="tool-results reveal">
        <article class="cyber-card tool-result-card">
            <h2>Top Results</h2>
            <div class="serp-result-list">
                <?php foreach ($serpResult['results'] as $index => $result): ?>
                    <a href="<?= e($result['url']) ?>" target="_blank" rel="noopener">
                        <b>#<?= $index + 1 ?></b>
                        <span><strong><?= e(excerpt($result['title'], 90)) ?></strong><small><?= e($result['host']) ?></small></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="cyber-card tool-result-card">
            <h2>Opportunities</h2>
            <div class="tool-check-list">
                <?php foreach ($serpResult['opportunities'] as $note): ?>
                    <div class="is-good"><i class="fa-solid fa-lightbulb"></i><span><strong>Action</strong><small><?= e($note) ?></small></span></div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
<?php endif; ?>

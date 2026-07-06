<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Full-Site SEO Crawler</span>
    <h1>Crawl Your Whole Site &amp; Audit Every Page at Once</h1>
    <p>Enter your homepage and we follow your internal links, run the deep 28-point on-page SEO audit on each page, then roll it all up into one site-wide score and a prioritised issue list — showing exactly what to fix and on which pages.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="split-section reveal">
    <form class="cyber-form glass-tool" method="post" action="/site-crawler/run">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Crawl a site</h2>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <label>Start URL (usually your homepage)
            <input name="target_url" type="url" inputmode="url" placeholder="https://yourbusiness.ie" value="<?= e($targetUrl ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Crawl &amp; Audit Site <i class="fa-solid fa-spider"></i></button>
        <p class="crawl-cap-note">
            <?php if (!empty($memberIsPro)): ?>
                <i class="fa-solid fa-crown"></i> Growth Lab Pro: up to <?= e((string) \App\Services\SiteCrawler::HARD_CAP) ?> pages per crawl.
            <?php else: ?>
                <i class="fa-solid fa-circle-info"></i> Free crawl covers up to 8 pages. <a href="/tools-pricing">Growth Lab Pro</a> crawls up to <?= e((string) \App\Services\SiteCrawler::HARD_CAP) ?>.
            <?php endif; ?>
        </p>
    </form>

    <aside class="cyber-card tool-result-card">
        <?php if (!empty($crawl)): ?>
            <span class="kicker"><?= e($crawl['host']) ?></span>
            <h2><?= e((string) $crawl['site_score']) ?>/100</h2>
            <p><?= e((string) $crawl['crawled']) ?> pages crawled · <?= e((string) $crawl['issue_count']) ?> issue types found.</p>
        <?php else: ?>
            <span class="kicker">Site Health</span>
            <h2>See every page's SEO at once.</h2>
            <p>Most audit tools check one page. This crawls your site and finds the pages dragging your rankings down.</p>
        <?php endif; ?>
    </aside>
</section>

<?php if (!empty($crawl)): $crawl = $crawl; ?>
    <?php
        $siteScore = (int) $crawl['site_score'];
        $siteGrade = $siteScore >= 90 ? 'A' : ($siteScore >= 75 ? 'B' : ($siteScore >= 60 ? 'C' : ($siteScore >= 40 ? 'D' : 'F')));
    ?>
    <section class="section reveal">
        <div class="crawl-summary cyber-card">
            <div class="crawl-score grade-<?= e(strtolower($siteGrade)) ?>">
                <strong><?= e((string) $siteScore) ?></strong>
                <span>/ 100 · <?= e($siteGrade) ?></span>
                <small>Site-wide score</small>
            </div>
            <div class="crawl-stat-tiles">
                <div class="crawl-stat"><b><?= e((string) $crawl['crawled']) ?></b><span>Pages crawled</span></div>
                <div class="crawl-stat"><b><?= e((string) $crawl['issue_count']) ?></b><span>Issue types</span></div>
                <div class="crawl-stat"><b><?= e((string) array_sum(array_map(static fn ($i) => (int) $i['pages'], $crawl['issues']))) ?></b><span>Total fixes</span></div>
                <div class="crawl-stat"><b><?= e((string) ($crawl['pages'][0]['score'] ?? 0)) ?></b><span>Homepage score</span></div>
            </div>
        </div>
        <?php if (!empty($crawl['reached_cap'])): ?>
            <p class="crawl-cap-hit"><i class="fa-solid fa-circle-info"></i> The crawl hit its page limit — larger sites are sampled. <?php if (empty($crawlIsPro)): ?><a href="/tools-pricing">Upgrade to Pro</a> to crawl more pages.<?php endif; ?></p>
        <?php endif; ?>
    </section>

    <section class="tool-results reveal">
        <article class="cyber-card tool-result-card">
            <h2><i class="fa-solid fa-triangle-exclamation"></i> Site-wide issues (most impactful first)</h2>
            <p class="seo-analysis-note">Each issue shows how many pages it affects, how to fix it and example pages.</p>
            <?php if (empty($crawl['issues'])): ?>
                <p class="crawl-clean"><i class="fa-solid fa-circle-check"></i> No failing checks across the crawled pages. Excellent.</p>
            <?php else: ?>
                <div class="crawl-issue-list">
                    <?php foreach ($crawl['issues'] as $issue): ?>
                        <details class="crawl-issue">
                            <summary>
                                <span class="crawl-issue-name"><?= e($issue['label']) ?></span>
                                <span class="crawl-issue-count"><?= e((string) $issue['pages']) ?> page<?= $issue['pages'] === 1 ? '' : 's' ?></span>
                            </summary>
                            <div class="crawl-issue-body">
                                <p class="crawl-issue-fix"><b>How to fix:</b> <?= e($issue['advice']) ?></p>
                                <ul class="crawl-issue-examples">
                                    <?php foreach ($issue['examples'] as $ex): ?>
                                        <li>
                                            <a href="<?= e($ex['url']) ?>" target="_blank" rel="noopener"><?= e($ex['url']) ?></a>
                                            <?php if (!empty($ex['value'])): ?><small><?= e(excerpt((string) $ex['value'], 90)) ?></small><?php endif; ?>
                                            <?php if (!empty($ex['details'])): ?>
                                                <ul class="crawl-issue-where">
                                                    <?php foreach ($ex['details'] as $d): ?><li><?= e((string) $d) ?></li><?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="cyber-card tool-result-card">
            <h2><i class="fa-solid fa-list-ol"></i> Pages by score (lowest first)</h2>
            <p class="seo-analysis-note">Fix the weakest pages first for the fastest gains.</p>
            <div class="crawl-page-list">
                <?php foreach ($crawl['worst_pages'] as $page): ?>
                    <?php $pg = (int) $page['score']; $pgGrade = $pg >= 90 ? 'a' : ($pg >= 75 ? 'b' : ($pg >= 60 ? 'c' : ($pg >= 40 ? 'd' : 'f'))); ?>
                    <div class="crawl-page">
                        <span class="crawl-page-score grade-<?= e($pgGrade) ?>"><?= e((string) $pg) ?></span>
                        <div class="crawl-page-meta">
                            <a href="<?= e($page['url']) ?>" target="_blank" rel="noopener"><?= e($page['title'] !== '' ? excerpt($page['title'], 60) : $page['url']) ?></a>
                            <small><?= e($page['url']) ?> · <?= e((string) $page['fail_count']) ?> issue<?= $page['fail_count'] === 1 ? '' : 's' ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="section reveal">
        <div class="cyber-card">
            <h2 class="crawl-all-heading"><i class="fa-solid fa-sitemap"></i> All crawled pages (<?= e((string) $crawl['crawled']) ?>)</h2>
            <div class="crawl-all-grid">
                <?php foreach ($crawl['pages'] as $page): ?>
                    <?php $pg = (int) $page['score']; $pgGrade = $pg >= 90 ? 'a' : ($pg >= 75 ? 'b' : ($pg >= 60 ? 'c' : ($pg >= 40 ? 'd' : 'f'))); ?>
                    <a class="crawl-all-item" href="<?= e($page['url']) ?>" target="_blank" rel="noopener">
                        <span class="crawl-page-score grade-<?= e($pgGrade) ?>"><?= e((string) $pg) ?></span>
                        <span class="crawl-all-url"><?= e((string) (parse_url($page['url'], PHP_URL_PATH) ?: '/')) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require base_path('app/views/partials/tool-pro-cta.php'); ?>

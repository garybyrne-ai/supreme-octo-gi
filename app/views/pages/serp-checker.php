<?php
$isPro = !empty($serpIsPro);
$tracked = $trackedKeywords ?? [];

// Tiny inline sparkline from a position history (lower position = higher line).
$sparkline = static function (array $positions): string {
    $pts = array_values(array_filter($positions, static fn ($p) => $p !== null));
    if (count($pts) < 2) {
        return '';
    }
    $w = 108; $h = 30; $n = count($pts);
    $max = 10; // chart the top-10 band
    $coords = [];
    foreach ($pts as $i => $pos) {
        $pos = max(1, min($max, (int) $pos));
        $x = $n > 1 ? ($i / ($n - 1)) * ($w - 4) + 2 : 2;
        $y = (($pos - 1) / ($max - 1)) * ($h - 6) + 3; // pos 1 near top
        $coords[] = round($x, 1) . ',' . round($y, 1);
    }
    return '<svg class="serp-spark" viewBox="0 0 ' . $w . ' ' . $h . '" width="' . $w . '" height="' . $h . '" aria-hidden="true">'
        . '<polyline fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="' . implode(' ', $coords) . '"/></svg>';
};
?>
<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Enterprise SERP Checker &amp; Rank Tracker</span>
    <h1>SERP Checker &amp; Rank Tracker for Keywords, Domains and Competitors</h1>
    <p>Check live organic position with a visibility tier, estimated click-through rate and the competitors ranking above you &mdash; then track any keyword and watch its position move over time.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<?php if (!empty($serpNotice)): ?><div class="notice success container-narrow"><?= e($serpNotice) ?></div><?php endif; ?>
<?php if (!empty($serpError)): ?><div class="notice error container-narrow"><?= e($serpError) ?></div><?php endif; ?>

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

<?php if (!empty($serpAnalysis)): $a = $serpAnalysis; ?>
    <section class="section reveal">
        <div class="serp-metrics">
            <div class="serp-metric tier-<?= e($a['tier_class']) ?>">
                <span>Visibility</span>
                <strong><?= e($a['tier']) ?></strong>
            </div>
            <div class="serp-metric">
                <span>Position</span>
                <strong><?= $a['position'] ? '#' . e((string) $a['position']) : '—' ?></strong>
            </div>
            <div class="serp-metric">
                <span>Est. organic CTR</span>
                <strong><?= e($a['est_ctr']) ?></strong>
            </div>
            <div class="serp-metric">
                <span>Your pages in top 10</span>
                <strong><?= e((string) $a['presence']) ?></strong>
            </div>
            <div class="serp-metric">
                <span>Visibility score</span>
                <strong><?= e((string) $a['visibility_score']) ?>/100</strong>
            </div>
        </div>

        <?php if (!empty($a['competitors_above'])): ?>
            <div class="cyber-card serp-competitors">
                <h3><i class="fa-solid fa-users-viewfinder"></i> Competitors ranking above you</h3>
                <div class="serp-comp-chips">
                    <?php foreach ($a['competitors_above'] as $comp): ?>
                        <span><?= e($comp) ?></span>
                    <?php endforeach; ?>
                </div>
                <p>Study their titles, content depth and internal linking for this keyword, then out-build the weakest one first.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($keyword) && !empty($domain)): ?>
            <?php if ($isPro): ?>
                <form class="serp-track-form" method="post" action="/serp-checker/track">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="keyword" value="<?= e($keyword) ?>">
                    <input type="hidden" name="domain" value="<?= e($domain) ?>">
                    <input type="hidden" name="location" value="<?= e($location ?? 'Ireland') ?>">
                    <button class="pill-button" type="submit"><i class="fa-solid fa-chart-line"></i> Track this keyword</button>
                    <span>Adds “<?= e($keyword) ?>” to your rank tracker and refreshes automatically.</span>
                </form>
            <?php else: ?>
                <p class="serp-track-upsell"><i class="fa-solid fa-lock"></i> <a href="/tools-pricing">Upgrade to Growth Lab Pro</a> to track this keyword's position over time.</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if (!empty($serpResult)): ?>
    <?php
        $rawDomain = strtolower(trim((string) ($domain ?? '')));
        $targetHost = preg_replace('/^www\./', '', (string) (parse_url(str_contains($rawDomain, '://') ? $rawDomain : 'https://' . $rawDomain, PHP_URL_HOST) ?: $rawDomain));
    ?>
    <section class="section reveal">
        <div class="serp-board cyber-card">
            <header class="serp-board-head">
                <div>
                    <h2>Live SERP &mdash; Top 10</h2>
                    <p><span class="serp-engine-chip"><i class="fa-solid fa-globe"></i> <?= e($serpResult['engine']) ?></span> for <strong><?= e($serpResult['query']) ?></strong></p>
                </div>
                <?php if (!empty($serpResult['position'])): ?>
                    <div class="serp-board-rank"><span>Your rank</span><strong>#<?= e((string) $serpResult['position']) ?></strong></div>
                <?php else: ?>
                    <div class="serp-board-rank is-out"><span>Your rank</span><strong>Not in top 10</strong></div>
                <?php endif; ?>
            </header>
            <ol class="serp-board-list">
                <?php foreach ($serpResult['results'] as $index => $result): ?>
                    <?php $isYou = $targetHost !== '' && ($result['host'] === $targetHost || str_ends_with((string) $result['host'], '.' . $targetHost)); ?>
                    <li class="serp-row<?= $isYou ? ' is-you' : '' ?>">
                        <span class="serp-pos"><?= $index + 1 ?></span>
                        <img class="serp-fav" src="https://www.google.com/s2/favicons?sz=64&amp;domain=<?= e($result['host']) ?>" alt="" width="20" height="20" loading="lazy">
                        <span class="serp-row-body">
                            <a href="<?= e($result['url']) ?>" target="_blank" rel="noopener" class="serp-row-title"><?= e(excerpt($result['title'], 90)) ?></a>
                            <span class="serp-row-url"><?= e($result['host']) ?><?php if ($isYou): ?> <em class="serp-you-badge">You</em><?php endif; ?></span>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php if (!empty($serpResult['opportunities'])): ?>
        <div class="cyber-card serp-opps">
            <h3><i class="fa-solid fa-lightbulb"></i> Ranking opportunities</h3>
            <ul class="serp-opps-list">
                <?php foreach ($serpResult['opportunities'] as $note): ?>
                    <li><i class="fa-solid fa-arrow-trend-up"></i> <?= e($note) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="section reveal" id="rank-tracker">
    <div class="section-heading">
        <span class="kicker">Rank Tracker</span>
        <h2>Track keyword positions over time</h2>
    </div>

    <?php if (!$isPro): ?>
        <div class="portal-lock cyber-card">
            <i class="fa-solid fa-chart-line"></i>
            <h2>Rank tracking is a Growth Lab Pro feature</h2>
            <p>Save up to 60 keywords and watch their organic position, best rank and week-on-week movement update automatically &mdash; alongside unlimited checks, white-label reports and monitoring.</p>
            <div class="growth-cta-actions">
                <a class="pill-button" href="/tools-pricing">Unlock Rank Tracking <i class="fa-solid fa-crown"></i></a>
            </div>
        </div>
    <?php elseif (empty($tracked)): ?>
        <div class="cyber-card serp-tracker-empty">
            <p><i class="fa-solid fa-circle-info"></i> No tracked keywords yet. Run a check above and hit <strong>Track this keyword</strong>, and it will appear here with position history.</p>
        </div>
    <?php else: ?>
        <div class="serp-tracker-table">
            <div class="serp-tracker-head">
                <span>Keyword</span><span>Domain</span><span>Current</span><span>Best</span><span>Change</span><span>Trend</span><span></span>
            </div>
            <?php foreach ($tracked as $t): ?>
                <?php
                    $cur = $t['current_position'];
                    $delta = $t['delta'] ?? null;
                    $deltaClass = $delta === null ? 'flat' : ($delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'));
                    $deltaLabel = $delta === null ? '—' : ($delta > 0 ? '▲ ' . $delta : ($delta < 0 ? '▼ ' . abs($delta) : '—'));
                ?>
                <div class="serp-tracker-row">
                    <span class="serp-tr-kw"><?= e($t['keyword']) ?></span>
                    <span class="serp-tr-dom"><?= e($t['domain']) ?></span>
                    <span><strong><?= $cur ? '#' . e((string) $cur) : 'Not top 10' ?></strong></span>
                    <span><?= $t['best_position'] ? '#' . e((string) $t['best_position']) : '—' ?></span>
                    <span class="serp-delta <?= $deltaClass ?>"><?= e($deltaLabel) ?></span>
                    <span class="serp-tr-spark tier-<?= $cur && $cur <= 3 ? 'excellent' : ($cur ? 'fair' : 'critical') ?>"><?= $sparkline($t['spark'] ?? []) ?></span>
                    <span>
                        <form method="post" action="/serp-checker/track" onsubmit="return confirm('Stop tracking this keyword?');">
                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                            <input type="hidden" name="remove_id" value="<?= e($t['id']) ?>">
                            <button class="serp-tr-remove" type="submit" aria-label="Remove"><i class="fa-solid fa-xmark"></i></button>
                        </form>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="serp-tracker-note"><i class="fa-solid fa-clock-rotate-left"></i> Positions refresh automatically on a schedule. Re-checking a tracked keyword above also records a fresh reading.</p>
    <?php endif; ?>
</section>

<?php require base_path('app/views/partials/tool-pro-cta.php'); ?>

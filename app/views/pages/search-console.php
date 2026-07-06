<?php
/** @var array<string,mixed> $member */
$configured = $configured ?? false;
$connected = $connected ?? false;
$sites = $sites ?? [];
$site = $site ?? '';
$totals = $totals ?? ['clicks' => 0, 'impressions' => 0, 'ctr' => 0.0, 'position' => 0.0];
$topQueries = $topQueries ?? [];
$topPages = $topPages ?? [];
$backlinks = $backlinks ?? [];
$record = $record ?? [];
?>
<section class="subhero">
    <span class="status-chip"><span></span> Growth Lab Pro</span>
    <h1>Search Console <span>Insights</span></h1>
    <p>Your real Google search data — top queries, pages, clicks and impressions — plus your backlinks, all in one place. Last 28 days.</p>
</section>

<?php if (!empty($notice)): ?><div class="notice success" style="max-width:1000px;margin:0 auto 8px"><?= e($notice) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="notice error" style="max-width:1000px;margin:0 auto 8px"><?= e($error) ?></div><?php endif; ?>

<section class="section reveal">
<?php if (!$configured): ?>
    <div class="cyber-card care-includes">
        <h2><i class="fa-solid fa-plug-circle-xmark"></i> Not set up yet</h2>
        <p>The Google connection has not been configured by the site owner. Please check back soon.</p>
    </div>
<?php elseif (!$connected): ?>
    <div class="cyber-card gsc-connect-card">
        <span class="module-orbit large"><i class="fa-brands fa-google"></i></span>
        <h2>Connect your Google Search Console</h2>
        <p>Sign in with the Google account that owns your Search Console property. We request <strong>read-only</strong> access and never post or change anything. You can disconnect any time.</p>
        <a class="pill-button" href="/account/search-console/connect"><i class="fa-brands fa-google"></i> Connect with Google</a>
        <small><i class="fa-solid fa-lock"></i> Read-only. Your data stays in your dashboard.</small>
    </div>
<?php else: ?>
    <?php if (count($sites) > 1 || $site === ''): ?>
    <form class="cyber-form gsc-site-form" method="post" action="/account/search-console/site">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label>Property
            <select name="site" onchange="this.form.submit()">
                <option value="">Choose a property…</option>
                <?php foreach ($sites as $s): ?>
                    <option value="<?= e($s) ?>" <?= $s === $site ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>
    <?php endif; ?>

    <?php if ($site !== ''): ?>
        <div class="gsc-stat-row">
            <article class="cyber-card gsc-stat"><span>Clicks</span><strong><?= e(number_format($totals['clicks'])) ?></strong></article>
            <article class="cyber-card gsc-stat"><span>Impressions</span><strong><?= e(number_format($totals['impressions'])) ?></strong></article>
            <article class="cyber-card gsc-stat"><span>Average CTR</span><strong><?= e((string) $totals['ctr']) ?>%</strong></article>
            <article class="cyber-card gsc-stat"><span>Avg. position</span><strong><?= e((string) $totals['position']) ?></strong></article>
        </div>

        <div class="gsc-grid">
            <article class="cyber-card">
                <h2><i class="fa-solid fa-magnifying-glass"></i> Top queries</h2>
                <?php if (!empty($topQueries)): ?>
                    <div class="gsc-table">
                        <div class="gsc-th"><span>Query</span><span>Clicks</span><span>Impr.</span><span>CTR</span><span>Pos.</span></div>
                        <?php foreach ($topQueries as $r): ?>
                            <div class="gsc-tr">
                                <span title="<?= e($r['key']) ?>"><?= e($r['key']) ?></span>
                                <span><?= e(number_format($r['clicks'])) ?></span>
                                <span><?= e(number_format($r['impressions'])) ?></span>
                                <span><?= e((string) $r['ctr']) ?>%</span>
                                <span><?= e((string) $r['position']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No query data yet for the last 28 days.</p>
                <?php endif; ?>
            </article>

            <article class="cyber-card">
                <h2><i class="fa-solid fa-file-lines"></i> Top pages</h2>
                <?php if (!empty($topPages)): ?>
                    <div class="gsc-table">
                        <div class="gsc-th"><span>Page</span><span>Clicks</span><span>Impr.</span><span>CTR</span><span>Pos.</span></div>
                        <?php foreach ($topPages as $r): ?>
                            <div class="gsc-tr">
                                <span title="<?= e($r['key']) ?>"><?= e(preg_replace('#^https?://[^/]+#', '', $r['key']) ?: $r['key']) ?></span>
                                <span><?= e(number_format($r['clicks'])) ?></span>
                                <span><?= e(number_format($r['impressions'])) ?></span>
                                <span><?= e((string) $r['ctr']) ?>%</span>
                                <span><?= e((string) $r['position']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No page data yet for the last 28 days.</p>
                <?php endif; ?>
            </article>
        </div>
    <?php endif; ?>

    <article class="cyber-card gsc-backlinks">
        <h2><i class="fa-solid fa-link"></i> Backlinks (your linking sites)</h2>
        <p class="seo-analysis-note">Google does not share backlinks through its API — only in the Search Console interface. To see them here, open <strong>Search Console → Links → Top linking sites → Export</strong> and upload that CSV.</p>
        <form class="cyber-form gsc-upload" method="post" action="/account/search-console/backlinks" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label>Top linking sites CSV <input type="file" name="backlinks_csv" accept=".csv" required></label>
            <button class="pill-button ghost" type="submit">Import Backlinks <i class="fa-solid fa-upload"></i></button>
        </form>
        <?php if (!empty($backlinks)): ?>
            <div class="gsc-table backlinks-table">
                <div class="gsc-th"><span>Linking site</span><span>Links</span></div>
                <?php foreach (array_slice($backlinks, 0, 100) as $b): ?>
                    <div class="gsc-tr"><span><?= e($b['site']) ?></span><span><?= e(number_format($b['links'])) ?></span></div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($record['backlinks_imported_at'])): ?><small>Imported <?= e(gmdate('j M Y', (int) strtotime((string) $record['backlinks_imported_at']))) ?> · <?= e((string) count($backlinks)) ?> sites</small><?php endif; ?>
        <?php endif; ?>
    </article>

    <form method="post" action="/account/search-console/disconnect" class="gsc-disconnect" onsubmit="return confirm('Disconnect Google Search Console?');">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="pill-button ghost danger" type="submit"><i class="fa-solid fa-plug-circle-xmark"></i> Disconnect Google</button>
    </form>
<?php endif; ?>
</section>

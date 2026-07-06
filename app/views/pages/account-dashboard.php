<?php
/** @var array<string,mixed> $member */
/** @var bool $isPro */
/** @var array<string,mixed>|null $scanUsage */
/** @var array<int,array<string,mixed>> $savedReports */
$member = $member ?? [];
$isPro = $isPro ?? false;
$scanUsage = $scanUsage ?? ['remaining' => 3, 'used' => 0, 'limit' => 3];
$savedReports = $savedReports ?? [];
$membership = $member['membership'] ?? null;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$toolMenu = [
    ['group' => 'Dashboard', 'items' => [
        ['/account/dashboard', 'fa-gauge-high', 'Overview'],
    ]],
    ['group' => 'SEO & Rankings', 'items' => [
        ['/seo-tools', 'fa-chart-line', 'SEO Audit'],
        ['/site-crawler', 'fa-spider', 'Site Crawler'],
        ['/serp-checker', 'fa-ranking-star', 'SERP Checker'],
        ['/account/search-console', 'fa-magnifying-glass-chart', 'Search Console'],
    ]],
    ['group' => 'Security', 'items' => [
        ['/ethical-hacking-toolkit', 'fa-shield-halved', 'Ethical Hacking Kit'],
        ['/ethical-hacking-tools', 'fa-toolbox', 'Hacking Tools Library'],
        ['/tools/security-headers', 'fa-lock', 'Security Headers'],
        ['/tools/dns-email', 'fa-envelope-circle-check', 'DNS & Email'],
        ['/tools/tls-ssl', 'fa-certificate', 'TLS / SSL'],
        ['/tools/security-txt', 'fa-file-shield', 'security.txt'],
    ]],
    ['group' => 'Content & AI', 'items' => [
        ['/ai-content-assistant', 'fa-wand-magic-sparkles', 'Content Assistant'],
        ['/ai-website-growth-consultant', 'fa-brain', 'AI Growth'],
    ]],
    ['group' => 'Intelligence', 'items' => [
        ['/tools/tech-stack', 'fa-microchip', 'Tech Stack'],
        ['/instant-website-quote-calculator', 'fa-calculator', 'Quote Calculator'],
        ['/ppc-roi-calculator', 'fa-bullseye', 'PPC ROI'],
    ]],
    ['group' => 'Account', 'items' => [
        ['/membership', 'fa-id-card', 'Membership'],
        ['/code-shop', 'fa-cart-shopping', 'Marketplace'],
    ]],
];
?>
<section class="account-shell">
    <aside class="account-sidebar">
        <div class="account-side-user">
            <span class="account-avatar"><?= e(strtoupper(substr((string) ($member['name'] ?? 'U'), 0, 1))) ?></span>
            <div>
                <strong><?= e($member['name'] ?? 'Member') ?></strong>
                <span class="account-plan-chip <?= $isPro ? 'is-pro' : '' ?>"><?= $isPro ? 'Growth Lab Pro' : 'Free plan' ?></span>
            </div>
        </div>
        <nav aria-label="Tools">
            <?php foreach ($toolMenu as $section): ?>
                <p class="account-nav-group"><?= e($section['group']) ?></p>
                <?php foreach ($section['items'] as [$url, $icon, $label]): ?>
                    <a class="<?= $currentPath === $url ? 'is-active' : '' ?>" href="<?= e($url) ?>">
                        <i class="fa-solid <?= e($icon) ?>"></i><span><?= e($label) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="/account/logout" class="account-logout">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button class="pill-button ghost" type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</button>
        </form>
    </aside>

    <div class="account-main">
        <header class="account-topbar">
            <div>
                <span class="status-chip"><span></span> Growth Lab</span>
                <h1>Welcome back, <?= e($member['name'] ?? 'there') ?></h1>
                <p>Run every tool from the menu, then <?= $isPro ? 'save or download' : 'download' ?> your reports.</p>
            </div>
            <a class="pill-button ghost" href="/tools"><i class="fa-solid fa-grip"></i> All tools</a>
        </header>

        <?php if (!empty($notice)): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

        <section class="account-stat-row">
            <article class="cyber-card account-stat">
                <span class="kicker">Membership</span>
                <?php if ($isPro): ?>
                    <strong>Pro active</strong>
                    <p><?= e(ucfirst((string) ($membership['plan_code'] ?? 'growth-lab'))) ?><?php if (!empty($membership['current_period_ends_at'])): ?> · renews <?= e(date('j M Y', strtotime((string) $membership['current_period_ends_at']) ?: time())) ?><?php endif; ?></p>
                <?php else: ?>
                    <strong>Free plan</strong>
                    <p>Upgrade to unlock unlimited scans, white-label reports and saved history.</p>
                    <a class="pill-button" href="/tools-pricing">Upgrade to Pro <i class="fa-solid fa-arrow-right"></i></a>
                <?php endif; ?>
            </article>
            <article class="cyber-card account-stat">
                <span class="kicker">Free scans today</span>
                <strong><?= $isPro ? '∞' : e((string) ($scanUsage['remaining'] ?? 3)) ?></strong>
                <p><?= $isPro ? 'Unlimited scans on Pro.' : e((string) ($scanUsage['used'] ?? 0)) . ' of ' . e((string) ($scanUsage['limit'] ?? 3)) . ' used. Three free scans, then upgrade.' ?></p>
            </article>
            <article class="cyber-card account-stat">
                <span class="kicker">Saved reports</span>
                <strong><?= $isPro ? e((string) count($savedReports)) . ' / 3' : 'Pro only' ?></strong>
                <p><?= $isPro ? 'Your three most recent saved reports.' : 'Free accounts can view and download reports; Pro saves your last three.' ?></p>
            </article>
        </section>

        <?php
            $siteUrl = $siteUrl ?? '';
            $siteHistory = $siteHistory ?? [];
            $siteLatest = $siteLatest ?? null;
            $siteHost = $siteUrl !== '' ? (parse_url($siteUrl, PHP_URL_HOST) ?: $siteUrl) : '';

            // Build a two-line SVG trend chart (SEO score + performance) from the
            // stored daily snapshots. Values are 0–100, so the Y axis is fixed.
            $buildLine = static function (array $history, string $key): string {
                $pts = [];
                $n = count($history);
                if ($n < 1) {
                    return '';
                }
                $w = 620; $h = 200; $padL = 34; $padR = 12; $padT = 12; $padB = 24;
                $plotW = $w - $padL - $padR; $plotH = $h - $padT - $padB;
                foreach ($history as $i => $point) {
                    $v = $point[$key] ?? null;
                    if ($v === null) { continue; }
                    $x = $n > 1 ? $padL + ($i / ($n - 1)) * $plotW : $padL + $plotW / 2;
                    $y = $padT + (1 - max(0, min(100, (int) $v)) / 100) * $plotH;
                    $pts[] = round($x, 1) . ',' . round($y, 1);
                }
                return implode(' ', $pts);
            };
            $seoLine = $buildLine($siteHistory, 'seo');
            $psiLine = $buildLine($siteHistory, 'psi');
        ?>
        <section class="cyber-card account-panel site-analytics">
            <div class="section-heading compact left">
                <span class="status-chip"><span></span> Website Analytics</span>
                <h2><?= $siteHost !== '' ? 'SEO health for ' . e($siteHost) : 'Track your website' ?></h2>
            </div>

            <?php if ($siteUrl === ''): ?>
                <p>Add your website and we'll run on-page SEO and Core Web Vitals on it, then chart the trend over time — free.</p>
                <form method="post" action="/account/site" class="site-analytics-form">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="url" name="website" placeholder="https://yourbusiness.ie" required>
                    <button class="pill-button" type="submit">Save website <i class="fa-solid fa-arrow-right"></i></button>
                </form>
            <?php else: ?>
                <div class="site-analytics-head">
                    <div class="site-metric-tiles">
                        <div class="site-metric"><span>On-page SEO</span><b class="<?= ($siteLatest['seo'] ?? null) === null ? '' : 'grade-num' ?>"><?= $siteLatest && $siteLatest['seo'] !== null ? e((string) $siteLatest['seo']) : '—' ?></b><small>/ 100</small></div>
                        <div class="site-metric"><span>Performance</span><b><?= $siteLatest && $siteLatest['psi'] !== null ? e((string) $siteLatest['psi']) : '—' ?></b><small>/ 100</small></div>
                        <div class="site-metric"><span>Words on page</span><b><?= $siteLatest && $siteLatest['words'] !== null ? e((string) $siteLatest['words']) : '—' ?></b><small>indexed</small></div>
                        <div class="site-metric"><span>Snapshots</span><b><?= e((string) count($siteHistory)) ?></b><small>tracked</small></div>
                    </div>
                    <form method="post" action="/account/site/analyze" class="site-analytics-run">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button class="pill-button" type="submit"><i class="fa-solid fa-rotate"></i> Run analysis now</button>
                    </form>
                </div>

                <?php if (count($siteHistory) < 1): ?>
                    <p class="site-analytics-empty">No data yet. Press <strong>Run analysis now</strong> to take your first snapshot — each run adds a point to the graph below.</p>
                <?php else: ?>
                    <div class="site-chart">
                        <div class="site-chart-legend">
                            <span class="is-seo"><i></i> On-page SEO</span>
                            <span class="is-psi"><i></i> Performance</span>
                        </div>
                        <svg viewBox="0 0 620 200" class="site-chart-svg" role="img" aria-label="Website SEO trend">
                            <?php foreach ([0, 25, 50, 75, 100] as $g): $gy = 12 + (1 - $g / 100) * 164; ?>
                                <line x1="34" x2="608" y1="<?= round($gy, 1) ?>" y2="<?= round($gy, 1) ?>" class="site-chart-grid"></line>
                                <text x="28" y="<?= round($gy + 3, 1) ?>" class="site-chart-axis"><?= $g ?></text>
                            <?php endforeach; ?>
                            <?php if ($psiLine !== ''): ?><polyline points="<?= e($psiLine) ?>" class="site-chart-line psi" fill="none"></polyline><?php endif; ?>
                            <?php if ($seoLine !== ''): ?><polyline points="<?= e($seoLine) ?>" class="site-chart-line seo" fill="none"></polyline><?php endif; ?>
                        </svg>
                        <div class="site-chart-dates">
                            <span><?= e(date('j M', strtotime((string) ($siteHistory[0]['date'] ?? 'now')) ?: time())) ?></span>
                            <span><?= e(date('j M', strtotime((string) ($siteHistory[count($siteHistory) - 1]['date'] ?? 'now')) ?: time())) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <p class="site-analytics-note">
                    <i class="fa-solid fa-circle-info"></i>
                    On-page SEO &amp; performance are measured live and free.
                    <?php if ($isPro): ?>
                        <a href="/account/search-console">Connect Search Console</a> to add real clicks, impressions &amp; average position, and <a href="/serp-checker">track keyword rankings</a> over time.
                    <?php else: ?>
                        <a href="/tools-pricing">Upgrade to Pro</a> for automatic daily tracking, Search Console traffic and keyword rank history.
                    <?php endif; ?>
                    <a href="/account/site" onclick="this.closest('.site-analytics').querySelector('.site-analytics-editrow')?.toggleAttribute('hidden');return false;">Change website</a>
                </p>
                <form method="post" action="/account/site" class="site-analytics-form site-analytics-editrow" hidden>
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="url" name="website" value="<?= e($siteUrl) ?>" placeholder="https://yourbusiness.ie" required>
                    <button class="pill-button ghost" type="submit">Update</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="cyber-card account-panel">
            <div class="section-heading compact left">
                <span class="status-chip"><span></span> Saved reports</span>
                <h2>Report history</h2>
            </div>
            <?php if (!$isPro): ?>
                <div class="account-upsell">
                    <p>Saving reports is a <strong>Growth Lab Pro</strong> feature. On the free plan you can still run every tool and download a white-label PDF of each result — you just can't store history.</p>
                    <a class="pill-button" href="/tools-pricing">See Pro pricing <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            <?php elseif (empty($savedReports)): ?>
                <p>No saved reports yet. Run any tool and press <strong>Save report</strong> to keep it here (last three are retained).</p>
            <?php else: ?>
                <div class="account-report-list">
                    <?php foreach ($savedReports as $r): ?>
                        <article class="account-report">
                            <span class="account-report-score grade-<?= e(strtolower((string) ($r['grade'] ?: '-'))) ?>"><?= e((string) $r['score']) ?></span>
                            <div>
                                <strong><?= e($r['tool']) ?></strong>
                                <small><?= e($r['target']) ?></small>
                                <em><?= e(date('j M Y, H:i', strtotime((string) $r['created_at']) ?: time())) ?></em>
                            </div>
                            <form method="post" action="/account/reports/delete">
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                <button class="pill-button ghost danger" type="submit" aria-label="Delete report"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php $monitors = $monitors ?? []; $monitorTypes = $monitorTypes ?? \App\Models\MonitorRepository::TYPES; ?>
        <section class="cyber-card account-panel">
            <div class="section-heading compact left">
                <span class="status-chip"><span></span> Monitoring</span>
                <h2>Scheduled monitoring &amp; alerts</h2>
            </div>
            <?php if (!$isPro): ?>
                <div class="account-upsell">
                    <p>Let us watch your sites for you. <strong>Growth Lab Pro</strong> re-runs your security, DNS and TLS checks every week and emails you the moment a score drops, a certificate nears expiry, or SPF/DMARC breaks.</p>
                    <a class="pill-button" href="/tools-pricing">Enable monitoring <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            <?php else: ?>
                <form class="monitor-add-form" method="post" action="/account/monitors/add">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <label>Check
                        <select name="type">
                            <?php foreach ($monitorTypes as $key => $label): ?>
                                <option value="<?= e($key) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Website or domain
                        <input name="target" placeholder="example.com" required>
                    </label>
                    <button class="pill-button" type="submit"><i class="fa-solid fa-plus"></i> Add monitor</button>
                </form>
                <?php if (!empty($monitors)): ?>
                    <div class="monitor-list">
                        <?php foreach ($monitors as $m): ?>
                            <?php $status = (string) ($m['last_status'] ?? 'pending'); ?>
                            <article class="monitor-item">
                                <div class="monitor-item-main">
                                    <strong><?= e($monitorTypes[$m['type']] ?? $m['type']) ?></strong>
                                    <small><?= e($m['target']) ?></small>
                                    <em class="monitor-status monitor-status-<?= e($status) ?>"><?= e(ucfirst($status)) ?><?php if ($m['last_score'] !== null): ?> · <?= e((string) $m['last_score']) ?>/100<?php endif; ?></em>
                                    <?php if (!empty($m['last_message'])): ?><span class="monitor-msg"><?= e($m['last_message']) ?></span><?php endif; ?>
                                </div>
                                <form method="post" action="/account/monitors/delete">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="id" value="<?= e($m['id']) ?>">
                                    <button class="pill-button ghost danger" type="submit" aria-label="Remove monitor"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No monitors yet. Add one above — we check weekly and only email you when something needs attention.</p>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <?php $portalLog = $portalLog ?? []; ?>
        <?php if (!empty($portalLog)): ?>
        <?php $typeIcons = ['update' => 'fa-screwdriver-wrench', 'report' => 'fa-file-lines', 'backup' => 'fa-database', 'seo' => 'fa-chart-line', 'note' => 'fa-note-sticky']; ?>
        <section class="cyber-card account-panel work-log-panel">
            <div class="section-heading compact left">
                <span class="status-chip"><span></span> Your Project</span>
                <h2>Work log &amp; reports</h2>
            </div>
            <p>Updates from the Crest Web Media team on your website, SEO and maintenance.</p>
            <div class="work-log-list">
                <?php foreach (array_slice($portalLog, 0, 30) as $entry): ?>
                    <article class="work-log-item">
                        <span class="work-log-icon"><i class="fa-solid <?= e($typeIcons[$entry['type'] ?? 'update'] ?? 'fa-screwdriver-wrench') ?>"></i></span>
                        <div>
                            <strong><?= e($entry['title'] ?? '') ?></strong>
                            <small><?= e(ucfirst((string) ($entry['type'] ?? 'update'))) ?> · <?= e(!empty($entry['date']) ? gmdate('j M Y', (int) strtotime((string) $entry['date'])) : '') ?></small>
                            <?php if (!empty($entry['note'])): ?><p><?= nl2br(e($entry['note'])) ?></p><?php endif; ?>
                            <?php if (!empty($entry['link'])): ?><a class="small-link" href="<?= e($entry['link']) ?>" target="_blank" rel="noopener">Open report <i class="fa-solid fa-arrow-up-right-from-square"></i></a><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php $referral = $referral ?? ['code' => '', 'count' => 0]; ?>
        <?php if (!empty($referral['code'])): ?>
        <?php
            $refBase = rtrim((string) ($config['url'] ?? ''), '/');
            if ($refBase === '') { $refBase = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . (string) ($_SERVER['HTTP_HOST'] ?? 'crestwebmedia.com'); }
            $refLink = $refBase . '/?ref=' . $referral['code'];
        ?>
        <section class="cyber-card account-panel referral-panel">
            <div class="section-heading compact left">
                <span class="status-chip"><span></span> Earn 20%</span>
                <h2>Refer &amp; earn</h2>
            </div>
            <p>Share your link. When someone you refer becomes a Growth Lab Pro member, you earn <strong>20% recurring commission</strong> for as long as they stay subscribed.</p>
            <div class="referral-link-row">
                <input type="text" readonly value="<?= e($refLink) ?>" id="referralLink" aria-label="Your referral link">
                <button type="button" class="pill-button" data-copy-target="#referralLink">Copy link <i class="fa-solid fa-copy"></i></button>
            </div>
            <p class="referral-count"><i class="fa-solid fa-user-group"></i> <strong><?= e((string) $referral['count']) ?></strong> referred so far · your code: <strong><?= e($referral['code']) ?></strong></p>
        </section>
        <?php endif; ?>

        <section class="cyber-card account-panel">
            <div class="section-heading compact left">
                <span class="status-chip"><span></span> Quick access</span>
                <h2>Jump into a tool</h2>
            </div>
            <div class="account-tool-grid">
                <a class="local-service-card" href="/seo-tools"><i class="fa-solid fa-chart-line"></i><h3>SEO Audit</h3><p>Titles, meta, schema, Open Graph, keyword density.</p></a>
                <a class="local-service-card" href="/tools/security-headers"><i class="fa-solid fa-lock"></i><h3>Security Headers</h3><p>HSTS, CSP, COOP, cookies and stack disclosure.</p></a>
                <a class="local-service-card" href="/tools/tech-stack"><i class="fa-solid fa-microchip"></i><h3>Tech Stack</h3><p>Detect CMS, frameworks, analytics, CDN and server.</p></a>
                <a class="local-service-card" href="/serp-checker"><i class="fa-solid fa-ranking-star"></i><h3>SERP Checker</h3><p>Live keyword position and competitor pages.</p></a>
            </div>
        </section>
    </div>
</section>

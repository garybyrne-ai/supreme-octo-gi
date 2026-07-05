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
        ['/serp-checker', 'fa-ranking-star', 'SERP Checker'],
        ['/account/search-console', 'fa-magnifying-glass-chart', 'Search Console'],
    ]],
    ['group' => 'Security', 'items' => [
        ['/ethical-hacking-toolkit', 'fa-shield-halved', 'Ethical Hacking Kit'],
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

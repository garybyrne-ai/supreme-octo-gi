<?php
$isPro = !empty($memberIsPro);
$member = $member ?? null;
$catalog = $toolCatalog ?? [];
$toolCount = 0;
foreach ($catalog as $group) { $toolCount += count($group['items'] ?? []); }

// Short logo marks for the app-style tool tiles. If a real logo SVG is dropped
// into /assets/images/tool-logos/{slug}.svg it is used automatically instead.
$marks = [
    'Nmap' => 'NM', 'theHarvester' => 'tH', 'OWASP ZAP' => 'ZAP', 'Burp Suite Community' => 'B',
    'Nikto' => 'NK', 'sqlmap' => 'SQL', 'WPScan' => 'WP', 'Nuclei' => 'NU', 'ffuf' => 'ff',
    'John the Ripper' => 'JtR', 'Hashcat' => 'HC', 'THC-Hydra' => 'HY', 'Wireshark' => 'WS',
    'Metasploit Framework' => 'M', 'Kali Linux' => 'KL', 'OWASP Juice Shop' => 'JS',
];
$toolLogo = static function (array $tool, array $marks): array {
    $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', (string) ($tool['name'] ?? '')));
    $slug = trim($slug, '-');
    $svg = base_path('public/assets/images/tool-logos/' . $slug . '.svg');
    return [
        'img' => is_file($svg) ? '/assets/images/tool-logos/' . $slug . '.svg' : null,
        'mark' => $marks[$tool['name']] ?? strtoupper(mb_substr((string) ($tool['name'] ?? '?'), 0, 2)),
    ];
};
?>
<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Growth Lab Pro · Members Only</span>
    <h1>Ethical Hacking Tools Library</h1>
    <p>A curated portal of the best free, open-source penetration-testing tools for websites &mdash; <strong><?= e((string) $toolCount) ?> tools</strong> across recon, web-app testing, passwords, network analysis and full platforms. Every entry has an official download link, a plain-English breakdown of what it does, and a step-by-step tutorial on using it safely.</p>
    <p class="tool-inline-note"><i class="fa-solid fa-scale-balanced"></i> For authorised testing only. Use these tools on systems you own or have written permission to assess.</p>
</section>

<?php if (!$isPro): ?>
    <section class="section reveal">
        <div class="portal-lock cyber-card">
            <i class="fa-solid fa-lock"></i>
            <h2>This library is a Growth Lab Pro benefit</h2>
            <p>Unlock direct download links and full step-by-step tutorials for all <?= e((string) $toolCount) ?> tools &mdash; plus unlimited audits, white-label reports, Search Console import, the AI content assistant and continuous monitoring.</p>
            <div class="growth-cta-actions">
                <a class="pill-button" href="/tools-pricing">Unlock With Growth Lab Pro <i class="fa-solid fa-crown"></i></a>
                <?php if (empty($member)): ?>
                    <button class="pill-button ghost" type="button" data-open-register>Create Free Account <i class="fa-solid fa-user-plus"></i></button>
                <?php else: ?>
                    <a class="pill-button ghost" href="/account/dashboard">Your Dashboard <i class="fa-solid fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
            <small>&euro;25/month or &euro;200/year &middot; cancel anytime</small>
        </div>

        <div class="section-heading" style="margin-top:40px">
            <span class="kicker">Preview</span>
            <h2>What's inside the library</h2>
        </div>
        <?php foreach ($catalog as $group): $gc = $group['color'] ?? '#00e5ff'; ?>
            <h3 class="portal-cat-title"><?= e($group['category']) ?></h3>
            <div class="portal-preview-grid">
                <?php foreach (($group['items'] ?? []) as $tool): $logo = $toolLogo($tool, $marks); ?>
                    <div class="cyber-card portal-preview-card">
                        <div class="portal-preview-head">
                            <span class="portal-logo" style="--c:<?= e($gc) ?>">
                                <?php if ($logo['img']): ?><img src="<?= e($logo['img']) ?>" alt="<?= e($tool['name']) ?> logo"><?php else: ?><b><?= e($logo['mark']) ?></b><?php endif; ?>
                            </span>
                            <div>
                                <strong><?= e($tool['name']) ?></strong>
                                <em><?= e($tool['tagline']) ?></em>
                            </div>
                            <span class="portal-locked-chip"><i class="fa-solid fa-lock"></i> Pro</span>
                        </div>
                        <p><?= e($tool['what']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php else: ?>
    <section class="section reveal">
        <p class="tool-inline-note"><i class="fa-solid fa-unlock-keyhole"></i> Pro access unlocked &mdash; download links and full tutorials below. Links open each tool's official source in a new tab.</p>
        <?php foreach ($catalog as $group): $gc = $group['color'] ?? '#00e5ff'; ?>
            <h2 class="portal-cat-title" style="--c:<?= e($gc) ?>"><i class="fa-solid fa-folder-open"></i> <?= e($group['category']) ?></h2>
            <div class="portal-grid">
                <?php foreach (($group['items'] ?? []) as $tool): $logo = $toolLogo($tool, $marks); ?>
                    <article class="cyber-card portal-tool-card">
                        <header class="portal-tool-head">
                            <span class="portal-logo lg" style="--c:<?= e($gc) ?>">
                                <?php if ($logo['img']): ?><img src="<?= e($logo['img']) ?>" alt="<?= e($tool['name']) ?> logo"><?php else: ?><b><?= e($logo['mark']) ?></b><?php endif; ?>
                            </span>
                            <div class="portal-tool-title">
                                <h3><?= e($tool['name']) ?></h3>
                                <em><?= e($tool['tagline']) ?></em>
                            </div>
                            <span class="portal-badge"><i class="fa-solid fa-shield-halved"></i> <?= e($tool['license']) ?></span>
                        </header>
                        <p class="portal-tool-what"><?= e($tool['what']) ?></p>
                        <p class="portal-platforms"><i class="fa-solid fa-desktop"></i> <?= e($tool['platforms']) ?></p>

                        <div class="portal-features">
                            <?php foreach (($tool['features'] ?? []) as $feat): ?>
                                <span><i class="fa-solid fa-check"></i> <?= e($feat) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="portal-guide">
                            <h4><i class="fa-solid fa-graduation-cap"></i> Step-by-step tutorial</h4>
                            <p class="portal-guide-intro">Run these on a target you own or are authorised to test:</p>
                            <ol>
                                <?php foreach (($tool['steps'] ?? []) as $step): ?>
                                    <li><?= e($step) ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>

                        <div class="portal-tool-actions">
                            <a class="pill-button" href="<?= e($tool['download']) ?>" target="_blank" rel="noopener nofollow">Download <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                            <?php if (!empty($tool['docs'])): ?>
                                <a class="pill-button ghost" href="<?= e($tool['docs']) ?>" target="_blank" rel="noopener nofollow">Docs &amp; tutorials <i class="fa-solid fa-book"></i></a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="cyber-card portal-legal">
            <h3><i class="fa-solid fa-circle-info"></i> Responsible use</h3>
            <p>Downloads point to each project's official source, so you always get authentic, unmodified software. These tools are provided for lawful, authorised security testing and education only. Testing systems without permission is illegal in most countries &mdash; always keep written scope. Pair this library with the <a href="/ethical-hacking-toolkit">online exposure scanner</a> for quick self-audits between deep tests.</p>
        </div>
    </section>
<?php endif; ?>

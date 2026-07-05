<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Defensive Security</span>
    <h1>Ethical Hacking Toolkit</h1>
    <p>A free, browser-based toolkit for hardening your own websites the way an attacker would probe them — find exposed files, weak headers, spoofable email, expiring certificates and disclosure leaks, then fix them with a prioritised report.</p>
    <p class="tool-inline-note"><i class="fa-solid fa-scale-balanced"></i> Authorised use only. Scan sites you own or have written permission to test.</p>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">The kit</span>
        <h2>Everything you need for a defensive self-audit</h2>
    </div>
    <div class="toolkit-grid">
        <?php foreach (($toolkitTools ?? []) as $tool): ?>
            <a class="cyber-card toolkit-card accent-<?= e($tool['accent'] ?? 'cyan') ?>" href="<?= e($tool['url']) ?>">
                <i class="fa-solid <?= e($tool['icon']) ?>"></i>
                <h3><?= e($tool['title']) ?></h3>
                <p><?= e($tool['summary']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="split-section reveal" id="exposure-scan">
    <form class="cyber-form glass-tool" method="post" action="/ethical-hacking-toolkit/exposure">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Sensitive File &amp; Attack-Surface Scan</h2>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <p>We request a small, fixed set of commonly-leaked paths (.env, .git, backups, phpinfo, server-status), check for software disclosure and open directory listings, and grade the result.</p>
        <label>Domain to scan
            <input name="domain" placeholder="yourdomain.com" value="<?= e($exposureHost ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Run Exposure Scan <i class="fa-solid fa-shield-halved"></i></button>
        <small><i class="fa-solid fa-lock"></i> Only public HTTPS hosts. Private networks, localhost and custom ports are blocked.</small>
    </form>

    <aside class="cyber-card tool-result-card">
        <?php if (!empty($report)): ?>
            <span class="kicker">Attack Surface</span>
            <h2><?= e((string) $report['score']) ?>/100</h2>
            <p>Scanned <?= e($report['target'] ?? '') ?> for exposed files and disclosure.</p>
        <?php else: ?>
            <span class="kicker">Recon → Harden</span>
            <h2>See what's exposed.</h2>
            <p>Attackers start by looking for forgotten backups, config files and repos. Find them first — then lock them down.</p>
        <?php endif; ?>
    </aside>
</section>

<?php if (!empty($report)): ?>
    <section class="section tool-single-wrap"><?php require base_path('app/views/partials/tool-report.php'); ?></section>
<?php endif; ?>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">Methodology</span>
        <h2>How to run a responsible self-audit</h2>
    </div>
    <div class="toolkit-steps">
        <article class="cyber-card"><b>1</b><h3>Confirm authorisation</h3><p>Only test sites you own or are contracted to assess. Keep written scope for client work.</p></article>
        <article class="cyber-card"><b>2</b><h3>Map the surface</h3><p>Run the exposure scan and TLS, DNS and header checks to see what's reachable and what leaks.</p></article>
        <article class="cyber-card"><b>3</b><h3>Prioritise fixes</h3><p>Close exposed files first, then fix headers, cookie flags and email spoofing. Use the report's ranked list.</p></article>
        <article class="cyber-card"><b>4</b><h3>Re-test &amp; monitor</h3><p>Re-scan after each fix. Growth Lab Pro adds continuous monitoring so regressions email you automatically.</p></article>
    </div>
</section>

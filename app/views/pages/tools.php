<section class="subhero tools-hub-hero">
    <span class="status-chip"><span></span> Growth Tool Suite</span>
    <h1>Free Tools for Smarter Websites, SEO, Security and Growth</h1>
    <p>Explore practical tools for audits, rankings, PPC returns, AI automation ideas, speed improvements, security checks and project planning. Register once for 3 free scans per day, then upgrade to Growth Lab Pro (€25/mo or €200/yr) for unlimited scans and white-label reports.</p>
    <div class="button-row">
        <a class="pill-button" href="#tool-suite">Explore Tools <i class="fa-solid fa-arrow-right"></i></a>
        <a class="pill-button ghost" href="/tools-pricing">See Pro Pricing <i class="fa-solid fa-crown"></i></a>
    </div>
</section>

<section class="tools-hub-strip reveal">
    <article><i class="fa-solid fa-fingerprint"></i><strong>3 free scans/day</strong><span>Email-verified users can run useful checks before upgrading.</span></article>
    <article><i class="fa-solid fa-chart-line"></i><strong>Growth focused</strong><span>SEO, SERP, PPC, speed and conversion signals.</span></article>
    <article><i class="fa-solid fa-file-shield"></i><strong>Paid reports</strong><span>Credits unlock deeper data and white-label PDF reports.</span></article>
    <article><i class="fa-solid fa-robot"></i><strong>Growth Lab Pro</strong><span>€25/mo or €200/yr for unlimited scans &amp; monitoring.</span></article>
</section>

<section class="section reveal" id="tool-suite">
    <div class="section-heading">
        <span class="kicker">Tool Directory</span>
        <h2>Choose the Tool That Matches Your Next Decision</h2>
    </div>
    <div class="tools-hub-grid">
        <?php foreach ($tools as $tool): ?>
            <a class="tools-hub-card accent-<?= e($tool['accent']) ?>" href="<?= e($tool['url']) ?>">
                <span class="tools-hub-card-icon"><i class="<?= e($tool['icon']) ?>"></i></span>
                <em><?= e($tool['category']) ?></em>
                <h3><?= e($tool['title']) ?></h3>
                <p><?= e($tool['summary']) ?></p>
                <b>Open Tool <i class="fa-solid fa-arrow-right"></i></b>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="tools-hub-cta reveal">
    <div>
        <span class="kicker">Need the Work Done?</span>
        <h2>Use the tools, then let Crest Web Media turn the result into rankings, leads and revenue.</h2>
        <p>Send the output from any tool or upgrade to Growth Lab when you need deeper data, exportable reports, monitoring and implementation support behind the recommendations.</p>
    </div>
    <div class="button-row">
        <a class="pill-button" href="/contact">Send Project Brief <i class="fa-solid fa-paper-plane"></i></a>
        <a class="pill-button ghost" href="/support">Ask Support <i class="fa-solid fa-headset"></i></a>
    </div>
</section>

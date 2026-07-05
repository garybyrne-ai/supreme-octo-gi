<section class="subhero tools-hero">
    <span class="status-chip"><span></span> <?= e($tool['eyebrow']) ?></span>
    <h1><?= e($tool['h1']) ?></h1>
    <p><?= e($tool['intro']) ?></p>
    <div class="button-row">
        <a class="pill-button" href="#tool">Use Tool <i class="<?= e($tool['icon']) ?>"></i></a>
        <a class="pill-button ghost" href="/contact">Send Project Brief <i class="fa-solid fa-paper-plane"></i></a>
    </div>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="interactive-suite reveal" id="tool" data-interactive-tool="<?= e($tool['mode']) ?>" data-tools-locked="<?= empty($toolLead) ? 'true' : 'false' ?>">
    <form class="cyber-form interactive-form">
        <h2><?= e($tool['eyebrow']) ?></h2>
        <?php if ($tool['mode'] === 'growth-consultant'): ?>
            <label>Business Type <input name="business" placeholder="Local service, SaaS, ecommerce, agency"></label>
            <label>Main Goal <select name="goal"><option>More SEO leads</option><option>More paid leads</option><option>Better website conversion</option><option>AI automation</option><option>New app or portal</option></select></label>
            <label>Target Region <input name="region" placeholder="Ireland, UK, USA, Europe"></label>
            <label>Current Problem <textarea name="problem" rows="4" placeholder="What is slowing growth right now?"></textarea></label>
        <?php elseif ($tool['mode'] === 'quote-calculator'): ?>
            <label>Pages <input name="pages" type="number" min="1" max="80" value="8"></label>
            <label>Project Type <select name="type"><option>Business Website</option><option>Ecommerce</option><option>Web App / Portal</option><option>SEO + PPC Growth System</option></select></label>
            <label><input name="seo" type="checkbox" checked> SEO setup</label>
            <label><input name="ai" type="checkbox"> AI chatbot or workflow automation</label>
            <label><input name="ppc" type="checkbox"> PPC landing page and tracking</label>
        <?php elseif ($tool['mode'] === 'security-badge'): ?>
            <label>Website <input name="site" placeholder="example.com"></label>
            <label>Header Score <input name="headers" type="number" min="0" max="100" value="80"></label>
            <label>TLS Score <input name="tls" type="number" min="0" max="100" value="90"></label>
            <label>DNS Score <input name="dns" type="number" min="0" max="100" value="75"></label>
        <?php elseif ($tool['mode'] === 'client-portal'): ?>
            <label>Client Name <input name="client" placeholder="Your Business"></label>
            <label>Active Service <select name="service"><option>SEO Growth</option><option>Website Development</option><option>App Development</option><option>PPC Campaign</option><option>Security Support</option></select></label>
            <label>Monthly Focus <input name="focus" placeholder="Rankings, leads, support, launch"></label>
        <?php elseif ($tool['mode'] === 'ppc-roi'): ?>
            <label>Monthly Ad Budget <input name="budget" type="number" min="100" value="1500"></label>
            <label>Average CPC <input name="cpc" type="number" min="0.1" step="0.1" value="3"></label>
            <label>Landing Page Conversion Rate % <input name="conversion" type="number" min="0.1" step="0.1" value="5"></label>
            <label>Average Sale Value <input name="value" type="number" min="1" value="800"></label>
        <?php elseif ($tool['mode'] === 'speed-simulator'): ?>
            <label>Current Performance Score <input name="current" type="number" min="0" max="100" value="45"></label>
            <label>Target Performance Score <input name="target" type="number" min="0" max="100" value="90"></label>
            <label>Monthly Visitors <input name="visitors" type="number" min="1" value="2500"></label>
            <label>Current Conversion Rate % <input name="conversion" type="number" min="0.1" step="0.1" value="2"></label>
        <?php else: ?>
            <label><input name="lead" type="checkbox" checked> Lead capture and qualification</label>
            <label><input name="support" type="checkbox"> Support replies and ticket summaries</label>
            <label><input name="crm" type="checkbox"> CRM updates and follow-up drafts</label>
            <label><input name="reports" type="checkbox"> Reports, notes and internal summaries</label>
            <label><input name="booking" type="checkbox"> Booking, reminders and onboarding</label>
        <?php endif; ?>
        <button class="pill-button" type="submit">Generate Result <i class="fa-solid fa-bolt"></i></button>
    </form>

    <aside class="cyber-card interactive-result">
        <span class="kicker">Result</span>
        <h2>Ready when you are.</h2>
        <p>Fill the form and generate a tailored result. Verified tool users see the full output here.</p>
        <div class="result-output"></div>
        <div class="button-row">
            <a class="pill-button" href="/contact">Turn This Into a Project <i class="fa-solid fa-arrow-right"></i></a>
            <a class="pill-button ghost" href="/support">Ask Support <i class="fa-solid fa-headset"></i></a>
        </div>
    </aside>
</section>

<?php if (($tool['mode'] ?? '') === 'security-badge'): ?>
<section class="section narrow reveal badge-embed-section">
    <div class="cyber-card badge-embed-card">
        <span class="kicker">Free — Embed On Your Website</span>
        <h2>Show visitors your site is security checked</h2>
        <p>Copy this snippet into your footer. The badge is free, loads fast, and links back to your live security profile. Pick the grade your site earned above.</p>
        <div class="badge-embed-controls">
            <label>Badge grade
                <select id="badgeEmbedGrade">
                    <option value="A">A — Excellent</option>
                    <option value="B">B — Good</option>
                    <option value="C">C — Fair</option>
                    <option value="D">D — Needs work</option>
                </select>
            </label>
            <span class="badge-embed-preview"><img id="badgeEmbedPreview" src="/badge.svg?grade=A" alt="Security checked by Crest Web Media" width="178" height="40" loading="lazy"></span>
        </div>
        <textarea id="badgeEmbedCode" class="badge-embed-code" rows="3" readonly spellcheck="false"></textarea>
        <button type="button" class="pill-button" id="badgeEmbedCopy">Copy Embed Code <i class="fa-solid fa-copy"></i></button>
        <small>Honesty rule: only display the grade your website actually scored in the <a href="/tools/security-headers">security scan</a>. We spot-check embedded badges.</small>
    </div>
</section>
<script>
(function () {
    var grade = document.getElementById('badgeEmbedGrade');
    var code = document.getElementById('badgeEmbedCode');
    var preview = document.getElementById('badgeEmbedPreview');
    var copy = document.getElementById('badgeEmbedCopy');
    if (!grade || !code) { return; }
    var base = window.location.origin;
    var build = function () {
        var g = grade.value;
        preview.src = '/badge.svg?grade=' + g;
        code.value = '<a href="' + base + '/tools/security-headers?ref=badge" rel="noopener" title="Website security checked by Crest Web Media">'
            + '<img src="' + base + '/badge.svg?grade=' + g + '" alt="Security grade ' + g + ' — checked by Crest Web Media" width="178" height="40" loading="lazy"></a>';
    };
    grade.addEventListener('change', build);
    copy.addEventListener('click', function () {
        code.select();
        try { navigator.clipboard.writeText(code.value); } catch (e) { document.execCommand('copy'); }
        copy.innerHTML = 'Copied! <i class="fa-solid fa-check"></i>';
        window.setTimeout(function () { copy.innerHTML = 'Copy Embed Code <i class="fa-solid fa-copy"></i>'; }, 1800);
    });
    build();
})();
</script>
<?php endif; ?>

<section class="tool-grid featured-tool-grid reveal">
    <a class="cyber-card glass-feature" href="/seo-tools"><i class="fa-solid fa-chart-line"></i><h3>SEO Audit</h3><p>Check pages before building campaigns.</p></a>
    <a class="cyber-card glass-feature" href="/serp-checker"><i class="fa-solid fa-ranking-star"></i><h3>SERP Checker</h3><p>Find ranking opportunities.</p></a>
    <a class="cyber-card glass-feature" href="/free-penetration-testing-tools"><i class="fa-solid fa-shield-halved"></i><h3>Security Tools</h3><p>Check technical trust signals.</p></a>
</section>

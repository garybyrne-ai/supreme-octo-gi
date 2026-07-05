<?php
/** @var array<int, array<string,mixed>> $clients */
$clients = $clients ?? [];
?>
<section class="subhero">
    <span class="status-chip"><span></span> Members Only</span>
    <h1>Our Case Studies Are <span>Client-Confidential</span></h1>
    <p>We build websites, run SEO audits and automate business workflows for real, named companies. Their detailed results are commercially sensitive — so full case studies are kept behind a free client &amp; partner login instead of being crawled by bots and competitors.</p>
    <div class="button-row">
        <a class="pill-button" href="#" data-open-account="register">Create Free Account <i class="fa-solid fa-user-plus"></i></a>
        <a class="pill-button ghost" href="#" data-open-account="login">I Already Have Access <i class="fa-solid fa-arrow-right-to-bracket"></i></a>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">Why It's Private</span>
        <h2>Three honest reasons this is behind a login</h2>
    </div>
    <div class="gate-reasons">
        <article class="cyber-card">
            <i class="fa-solid fa-user-shield"></i>
            <h3>Client confidentiality</h3>
            <p>Case studies name real clients and their internal numbers. We keep that off the open web out of respect for the businesses that trust us.</p>
        </article>
        <article class="cyber-card">
            <i class="fa-solid fa-robot"></i>
            <h3>Bot &amp; scraper protection</h3>
            <p>Detailed strategy write-ups are exactly what competitors and AI scrapers harvest. A quick login keeps the work with genuine prospects.</p>
        </article>
        <article class="cyber-card">
            <i class="fa-solid fa-handshake-angle"></i>
            <h3>Serious enquiries only</h3>
            <p>A 30-second free sign-up filters tyre-kickers from real buyers — so you get our full attention when you reach out.</p>
        </article>
    </div>
</section>

<?php if (!empty($clients)): ?>
<section class="section reveal clients-section">
    <div class="section-heading">
        <span class="kicker">A Glimpse</span>
        <h2>Some of the brands we've delivered for</h2>
        <p>Sign in to read exactly what we built, audited and automated for each of them.</p>
    </div>
    <div class="clients-wall">
        <?php foreach ($clients as $client): ?>
            <div class="client-logo-tile" title="<?= e($client['name']) ?>">
                <img src="<?= e($client['logo']) ?>" alt="<?= e($client['name']) ?> logo" loading="lazy" width="220" height="82">
            </div>
        <?php endforeach; ?>
    </div>
    <div class="clients-cta">
        <a class="pill-button" href="#" data-open-account="register">Unlock The Case Studies <i class="fa-solid fa-lock-open"></i></a>
    </div>
</section>
<?php endif; ?>

<?php
/** @var array<string,mixed> $county */
/** @var array<string, array<string,mixed>> $services */
/** @var array<int, array<string,string>> $nearby */
$services = $services ?? [];
$nearby = $nearby ?? [];
?>
<section class="subhero location-detail-hero">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span aria-hidden="true">/</span>
        <a href="/locations">Locations</a><span aria-hidden="true">/</span>
        <span><?= e($county['name']) ?></span>
    </nav>
    <span class="status-chip"><span></span> <?= e($county['province']) ?>, Ireland</span>
    <h1>Web Design &amp; Digital Services in <span><?= e($county['name']) ?></span></h1>
    <p>Helping businesses across County <?= e($county['name']) ?> win more customers online with fast websites, strong local SEO, ecommerce and profitable Google Ads.</p>
    <div class="button-row">
        <a class="pill-button" href="/contact?area=<?= e($county['slug']) ?>">Start a <?= e($county['name']) ?> Project <i class="fa-solid fa-arrow-right"></i></a>
        <a class="pill-button ghost" href="/locations">All Counties <i class="fa-solid fa-map-location-dot"></i></a>
    </div>
</section>

<section class="section reveal">
    <div class="local-trust-row">
        <article><strong>Local</strong><span>Copy and SEO tuned for the <?= e($county['name']) ?> market</span></article>
        <article><strong>Fast</strong><span>Core Web Vitals-ready builds that load quickly</span></article>
        <article><strong>Secure</strong><span>Security best practice in every project</span></article>
        <article><strong>Remote</strong><span>Work with us from anywhere in <?= e($county['name']) ?></span></article>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading left">
        <span class="kicker">Services in <?= e($county['name']) ?></span>
        <h2>Everything you need to grow online in <?= e($county['name']) ?></h2>
        <p>Each service is delivered with your local audience and competitors in mind.</p>
    </div>
    <div class="local-service-grid">
        <?php foreach ($services as $service): ?>
            <a class="local-service-card" href="/<?= e($service['slug']) ?>-<?= e($county['slug']) ?>">
                <i class="fa-solid <?= e($service['icon']) ?>"></i>
                <h3><?= e($service['name']) ?> in <?= e($county['name']) ?></h3>
                <p><?= e($service['blurb']) ?></p>
                <span class="local-service-cta">See <?= e($service['name']) ?> <i class="fa-solid fa-arrow-right"></i></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading left">
        <span class="kicker">Towns we serve</span>
        <h2>Covering <?= e($county['name']) ?> and its towns</h2>
    </div>
    <div class="local-town-list">
        <?php foreach ($county['towns'] as $town): ?>
            <span><i class="fa-solid fa-location-dot"></i> <?= e($town) ?></span>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!empty($nearby)): ?>
    <section class="section reveal">
        <div class="section-heading left">
            <span class="kicker">Nearby in <?= e($county['province']) ?></span>
            <h2>Also covering neighbouring counties</h2>
        </div>
        <div class="local-town-list">
            <?php foreach ($nearby as $near): ?>
                <span><a href="/locations/<?= e($near['slug']) ?>"><?= e($near['name']) ?></a></span>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="home-tools-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker"><?= e($county['name']) ?> businesses</span>
        <h2>Let's turn your website into a lead engine in <?= e($county['name']) ?>.</h2>
        <p>Send your goal and current website and we'll come back with a clear, prioritised plan.</p>
        <div class="button-row">
            <a class="pill-button" href="/contact?area=<?= e($county['slug']) ?>">Get a Proposal <i class="fa-solid fa-paper-plane"></i></a>
            <a class="pill-button ghost" href="/ai-website-growth-consultant">AI Growth Consultant <i class="fa-solid fa-brain"></i></a>
        </div>
    </div>
</section>

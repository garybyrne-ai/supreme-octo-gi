<?php
/** @var array<string,mixed> $service */
/** @var array<string,mixed>|null $county */
/** @var string $areaName */
/** @var bool $isNational */
/** @var array<int, array<string,string>> $otherServices */
/** @var array<int, array<string,string>> $nearby */
$otherServices = $otherServices ?? [];
$nearby = $nearby ?? [];
$areaSlug = $county['slug'] ?? 'ireland';
?>
<section class="subhero location-detail-hero">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span aria-hidden="true">/</span>
        <a href="/locations">Locations</a><span aria-hidden="true">/</span>
        <?php if (!$isNational): ?>
            <a href="/locations/<?= e($county['slug']) ?>"><?= e($county['name']) ?></a><span aria-hidden="true">/</span>
        <?php endif; ?>
        <span><?= e($service['name']) ?></span>
    </nav>
    <span class="status-chip"><span></span> <?= e($isNational ? 'Ireland-wide' : $county['province'] . ', Ireland') ?></span>
    <h1><?= e($service['name']) ?> in <span><?= e($areaName) ?></span></h1>
    <p><?= e($service['blurb']) ?> <?= $isNational ? 'Serving businesses across every county of Ireland.' : 'Built around the ' . e($areaName) . ' market and its buyers.' ?></p>
    <div class="button-row">
        <a class="pill-button" href="/contact?service=<?= e($service['slug']) ?>&amp;area=<?= e($areaSlug) ?>">Start Your <?= e($service['name']) ?> Project <i class="fa-solid fa-arrow-right"></i></a>
        <a class="pill-button ghost" href="/instant-website-quote-calculator">Instant Quote <i class="fa-solid fa-calculator"></i></a>
    </div>
</section>

<section class="section reveal">
    <div class="local-trust-row">
        <article><strong><?= e($isNational ? 'Nationwide' : 'Local') ?></strong><span><?= e($service['name']) ?> for the <?= e($areaName) ?> market</span></article>
        <article><strong>Fast</strong><span>Core Web Vitals-ready delivery</span></article>
        <article><strong>Secure</strong><span>Security best practice as standard</span></article>
        <article><strong>Clear ROI</strong><span>Reporting built around enquiries</span></article>
    </div>
</section>

<section class="split-section reveal">
    <div class="cyber-card service-copy-card">
        <h2>What's included</h2>
        <ul class="check-list">
            <?php foreach ($service['deliverables'] as $item): ?>
                <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="cyber-card service-copy-card">
        <h2>Why <?= e($areaName) ?> businesses choose us</h2>
        <ul class="check-list">
            <?php foreach ($service['benefits'] as $item): ?>
                <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php if (!$isNational): ?>
    <section class="section reveal">
        <div class="section-heading left">
            <span class="kicker">Local coverage</span>
            <h2><?= e($service['name']) ?> across <?= e($county['name']) ?></h2>
        </div>
        <div class="local-town-list">
            <?php foreach ($county['towns'] as $town): ?>
                <span><i class="fa-solid fa-location-dot"></i> <?= e($town) ?></span>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="section reveal">
    <div class="section-heading left">
        <span class="kicker">More in <?= e($areaName) ?></span>
        <h2>Other services in <?= e($areaName) ?></h2>
        <p>Combine services into one coordinated growth plan.</p>
    </div>
    <div class="local-service-grid">
        <?php foreach ($otherServices as $link): ?>
            <a class="local-service-card" href="<?= e($link['url']) ?>">
                <i class="fa-solid <?= e($link['icon']) ?>"></i>
                <h3><?= e($link['short']) ?></h3>
                <p><?= e($link['tagline']) ?></p>
                <span class="local-service-cta">View <i class="fa-solid fa-arrow-right"></i></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!$isNational && !empty($nearby)): ?>
    <section class="section reveal">
        <div class="section-heading left">
            <span class="kicker">Nearby counties</span>
            <h2><?= e($service['name']) ?> in neighbouring counties</h2>
        </div>
        <div class="local-town-list">
            <?php foreach ($nearby as $near): ?>
                <span><a href="/<?= e($service['slug']) ?>-<?= e($near['slug']) ?>"><?= e($service['name']) ?> in <?= e($near['name']) ?></a></span>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="home-tools-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker"><?= e($service['name']) ?> in <?= e($areaName) ?></span>
        <h2>Ready to get started with <?= e($service['name']) ?>?</h2>
        <p>Tell us about your business and goals — we'll reply with a clear, prioritised plan and a fixed proposal.</p>
        <div class="button-row">
            <a class="pill-button" href="/contact?service=<?= e($service['slug']) ?>&amp;area=<?= e($areaSlug) ?>">Get a Proposal <i class="fa-solid fa-paper-plane"></i></a>
            <a class="pill-button ghost" href="/locations">All Areas <i class="fa-solid fa-map-location-dot"></i></a>
        </div>
    </div>
</section>

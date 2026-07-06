<?php
/** @var array<string, array<int, array<string,mixed>>> $provinces */
/** @var array<string, array<string,mixed>> $services */
$services = $services ?? [];
$countyCount = $countyCount ?? 32;
?>
<section class="subhero">
    <span class="status-chip"><span></span> Areas We Cover</span>
    <h1>Web Design &amp; Digital Services Across <span>Ireland</span></h1>
    <p>From Dublin to Donegal, Cork to Belfast — we build fast, secure websites and run SEO, ecommerce and Google Ads for businesses in every county on the island.</p>
    <div class="location-hero-metrics">
        <span><b><?= e((string) $countyCount) ?></b> counties covered</span>
        <span><b>4</b> provinces</span>
        <span><b><?= e((string) count($services)) ?></b> core services</span>
        <span>Remote-first, <b>island-wide</b></span>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">What we deliver locally</span>
        <h2>Every service, tuned for your local market</h2>
        <p>Pick a service to see how it works for businesses in your area, or jump straight to your county below.</p>
    </div>
    <div class="local-service-grid">
        <?php foreach ($services as $service): ?>
            <a class="local-service-card" href="/<?= e($service['slug']) ?>-ireland">
                <i class="fa-solid <?= e($service['icon']) ?>"></i>
                <h3><?= e($service['name']) ?></h3>
                <p><?= e($service['tagline']) ?></p>
                <span class="local-service-cta">Explore <?= e($service['name']) ?> <i class="fa-solid fa-arrow-right"></i></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">Find your county</span>
        <h2>Serving all 32 counties of Ireland</h2>
        <p>Choose your county for local web design, development, SEO, ecommerce and Google Ads.</p>
    </div>
    <?php foreach ($provinces as $province => $counties): ?>
        <?php if (empty($counties)) { continue; } ?>
        <div class="province-group">
            <h2><?= e($province) ?></h2>
            <div class="location-grid">
                <?php foreach ($counties as $county): ?>
                    <a class="location-card cyber-card" href="/locations/<?= e($county['slug']) ?>">
                        <strong><?= e($county['name']) ?></strong>
                        <small>County <?= e($county['name']) ?>, <?= e($province) ?></small>
                        <span class="location-card-towns"><?= e(implode(' · ', array_slice($county['towns'], 0, 3))) ?></span>
                        <span class="location-card-cta">View services <i class="fa-solid fa-arrow-right"></i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="home-tools-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker">Not sure where to start?</span>
        <h2>Tell us your county and goal — get a clear plan back.</h2>
        <p>Whether you need a new website, better rankings, an online store or paid leads, we'll map the fastest route for your local market.</p>
        <div class="button-row">
            <a class="pill-button" href="/contact">Get a Local Proposal <i class="fa-solid fa-paper-plane"></i></a>
            <a class="pill-button ghost" href="/instant-website-quote-calculator">Instant Quote <i class="fa-solid fa-calculator"></i></a>
        </div>
    </div>
</section>

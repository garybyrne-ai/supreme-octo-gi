<section class="subhero">
    <span class="status-chip"><span></span> <?= e($pageIntro['kicker'] ?? 'Investment') ?></span>
    <h1><?= e($pageIntro['heading'] ?? 'Pricing') ?></h1>
    <p>Clear starting points for websites, CMS builds and platform work.</p>
</section>
<section class="split-section reveal">
    <div>
        <h2>Pricing depends on scope, risk and speed.</h2>
        <p>These ranges are starting points. A small conversion website, a custom CMS, a security review and a platform build all carry different levels of planning, testing and support.</p>
    </div>
    <div class="cyber-card">
        <h2>Every quote includes</h2>
        <ul class="check-list">
            <li>Discovery and recommendations</li>
            <li>Responsive implementation</li>
            <li>SEO and performance basics</li>
            <li>Launch support and handoff notes</li>
        </ul>
    </div>
</section>
<section class="section reveal">
    <div class="pricing-grid">
        <?php foreach ($pricing as $plan): ?>
            <article class="cyber-card price-card">
                <h2><?= e($plan['name']) ?></h2>
                <strong><?= e($plan['price']) ?></strong>
                <ul class="check-list">
                    <?php foreach ($plan['features'] as $feature): ?>
                        <li><?= e($feature) ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="pill-button ghost" href="/contact">Discuss Project</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

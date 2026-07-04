<section class="subhero service-detail-hero">
    <div>
        <span class="status-chip"><span></span> Enterprise Capability</span>
        <h1><?= e($service['title']) ?></h1>
        <p><?= e($service['intro'] ?? $service['summary']) ?></p>
        <div class="button-row">
            <a class="pill-button" href="/contact">Start This Project <i class="fa-solid fa-arrow-right"></i></a>
            <a class="pill-button ghost" href="/services">View All Services <i class="fa-solid fa-table-cells-large"></i></a>
        </div>
    </div>
    <aside class="service-orbit-card">
        <div class="service-orbit">
            <span>
                <?php if (!empty($service['visuals'][0]['image'])): ?>
                    <img src="<?= asset('images/' . $service['visuals'][0]['image']) ?>" alt="<?= e($service['visuals'][0]['name']) ?> logo" loading="lazy">
                <?php else: ?>
                    <i class="<?= e($service['visuals'][0]['icon'] ?? $service['icon']) ?>"></i>
                <?php endif; ?>
            </span>
            <b></b><b></b><b></b>
        </div>
        <h2><?= e($service['title']) ?></h2>
        <p><?= e($service['orbit_note'] ?? 'A focused delivery plan built around clarity, trust, speed and qualified written enquiries.') ?></p>
        <?= service_visuals($service['visuals'] ?? [], 'service-logo-cloud', 4) ?>
    </aside>
</section>

<section class="service-metrics reveal">
    <?php foreach ($service['proof_points'] as $point): ?>
        <article><strong><?= e($point['value']) ?></strong><span><?= e($point['label']) ?></span></article>
    <?php endforeach; ?>
</section>

<section class="section service-seo-console reveal">
    <div class="section-heading left">
        <span class="kicker"><?= e($service['console_kicker'] ?? 'Service Intelligence') ?></span>
        <h2><?= e($service['console_title'] ?? ($service['title'] . ' Built Around Real Buyer Decisions')) ?></h2>
        <p><?= e($service['console_intro'] ?? 'This service page is written around the practical questions buyers ask before they trust a provider, not around generic keyword blocks.') ?></p>
    </div>
    <div class="service-seo-grid">
        <?php foreach ($service['seo_essentials'] as $item): ?>
            <article>
                <i class="fa-solid <?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
                <strong><?= e($item['value']) ?></strong>
                <p><?= e($item['body']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section service-market-console reveal">
    <div class="section-heading left">
        <span class="kicker">Market Intelligence</span>
        <h2>Market Signals Behind The Strategy</h2>
    </div>
    <div class="service-market-grid">
        <?php foreach ($service['market_notes'] as $note): ?>
            <article>
                <i class="fa-solid <?= e($note['icon']) ?>"></i>
                <div>
                    <h3><?= e($note['title']) ?></h3>
                    <p><?= e($note['body']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section service-decision-console reveal">
    <div class="section-heading left">
        <span class="kicker">Buyer Journey</span>
        <h2>How Attention Turns Into Qualified Enquiries</h2>
    </div>
    <div class="service-decision-grid">
        <?php foreach ($service['decision_panels'] as $panel): ?>
            <article>
                <em><?= e($panel['step']) ?></em>
                <h3><?= e($panel['title']) ?></h3>
                <p><?= e($panel['body']) ?></p>
                <span><?= e($panel['signal']) ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section service-article reveal">
    <div class="section-heading left">
        <span class="kicker">Service Guide</span>
        <h2>Technical Context, Buyer Intent And Delivery Detail</h2>
    </div>
    <div class="service-article-grid">
        <?php foreach ($service['long_form'] as $section): ?>
            <article class="cyber-card service-article-card">
                <h3><?= e($section['heading']) ?></h3>
                <?php foreach ($section['paragraphs'] as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="split-section service-deep-dive reveal">
    <div class="cyber-card service-copy-card">
        <h2>Benefits</h2>
        <ul class="check-list">
            <?php foreach ($service['benefits'] as $benefit): ?>
                <li><?= e($benefit) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="cyber-card service-copy-card">
        <h2>Best For</h2>
        <ul class="check-list">
            <?php foreach ($service['ideal_for'] as $item): ?>
                <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="split-section service-deep-dive reveal">
    <div class="cyber-card service-copy-card">
        <h2>Deliverables</h2>
        <ul class="check-list">
            <?php foreach ($service['deliverables'] as $deliverable): ?>
                <li><?= e($deliverable) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="cyber-card service-copy-card">
        <h2>Technology & Focus</h2>
        <?= service_visuals($service['visuals'] ?? [], 'service-logo-cloud inline', 6) ?>
        <div class="tag-cloud">
            <?php foreach ($service['tags'] as $tag): ?>
                <span><?= e($tag) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section service-flow-console reveal">
    <div class="section-heading left">
        <span class="kicker">Delivery Console</span>
        <h2>Project Flow Built Around Evidence, Quality Gates And Launch Control</h2>
    </div>
    <div class="flow-command-grid">
        <div class="flow-timeline" aria-label="<?= e($service['title']) ?> project flow">
            <?php foreach ($service['flow_console'] as $index => $step): ?>
                <article class="flow-node">
                    <div class="flow-node-index">
                        <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <i class="fa-solid <?= e($step['icon']) ?>"></i>
                    </div>
                    <div>
                        <em><?= e($step['metric']) ?></em>
                        <h3><?= e($step['phase']) ?></h3>
                        <p><?= e($step['signal']) ?></p>
                        <small><b>Output</b><?= e($step['output']) ?></small>
                    </div>
                    <strong><?= e($step['tag']) ?></strong>
                </article>
            <?php endforeach; ?>
        </div>
        <aside class="flow-radar-panel">
            <span class="kicker">Live Build Signals</span>
            <h3><?= e($service['title']) ?> Data Layer</h3>
            <p>Every page gets a service-specific structure for search intent, lead capture, technical delivery, tracking and post-launch improvement.</p>
            <div class="radar-orb" aria-hidden="true"><span></span><b></b><i></i></div>
            <?= service_visuals($service['visuals'] ?? [], 'service-logo-cloud', 6) ?>
        </aside>
    </div>
</section>

<section class="section service-data-console reveal">
    <div class="section-heading left">
        <span class="kicker">Service Intelligence</span>
        <h2>Data Points Built Into The Page Architecture</h2>
    </div>
    <div class="service-data-grid">
        <?php foreach ($service['data_panels'] as $panel): ?>
            <article class="data-panel">
                <i class="fa-solid <?= e($panel['icon']) ?>"></i>
                <span><?= e($panel['label']) ?></span>
                <strong><?= e($panel['value']) ?></strong>
                <p><?= e($panel['body']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section narrow reveal">
    <div class="section-heading"><h2>FAQ</h2></div>
    <div class="faq-list">
        <?php foreach ($service['faq'] as $faq): ?>
            <details>
                <summary><?= e($faq['question']) ?></summary>
                <p><?= e($faq['answer']) ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading"><h2>Related Services</h2></div>
    <div class="service-grid compact-grid">
        <?php foreach (array_slice(array_filter($services, fn ($item) => $item['slug'] !== $service['slug']), 0, 4) as $related): ?>
            <a class="cyber-card service-card" href="/services/<?= e($related['slug']) ?>">
                <i class="<?= e($related['icon']) ?>"></i>
                <?= service_visuals($related['visuals'] ?? [], 'service-logo-strip compact', 3) ?>
                <h3><?= e($related['title']) ?></h3>
                <p><?= e($related['summary']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading"><h2>Useful Tools for This Service</h2></div>
    <div class="tool-grid featured-tool-grid">
        <a class="cyber-card glass-feature" href="/ai-website-growth-consultant"><i class="fa-solid fa-brain"></i><h3>AI Growth Consultant</h3><p>Map the next best growth actions for this project.</p></a>
        <a class="cyber-card glass-feature" href="/instant-website-quote-calculator"><i class="fa-solid fa-calculator"></i><h3>Quote Calculator</h3><p>Estimate scope before sending a project brief.</p></a>
        <?php if (str_contains($service['slug'], 'seo') || str_contains($service['slug'], 'web-design') || str_contains($service['slug'], 'website')): ?>
            <a class="cyber-card glass-feature" href="/serp-checker"><i class="fa-solid fa-ranking-star"></i><h3>SERP Checker</h3><p>Check ranking opportunities for your market.</p></a>
        <?php elseif (str_contains($service['slug'], 'penetration')): ?>
            <a class="cyber-card glass-feature" href="/security-score-badge-generator"><i class="fa-solid fa-shield-halved"></i><h3>Security Badge</h3><p>Generate a score badge after checks.</p></a>
        <?php else: ?>
            <a class="cyber-card glass-feature" href="/ai-automation-finder"><i class="fa-solid fa-wand-magic-sparkles"></i><h3>Automation Finder</h3><p>Find AI workflows around this service.</p></a>
        <?php endif; ?>
    </div>
</section>

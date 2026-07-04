<section class="subhero">
    <span class="status-chip"><span></span> Core Capabilities</span>
    <h1>Services Built Around Revenue, Search And Operating Speed</h1>
    <p>Web design, PHP 8/Laravel development, ecommerce, apps, API integrations, AI automation, SEO and security for businesses that need digital systems to perform.</p>
</section>
<section class="triple-panel reveal">
    <article class="cyber-card">
        <h2>Build</h2>
        <p>Websites, CMS platforms, apps, plugins and digital products built with lean architecture, editable controls and future growth in mind.</p>
    </article>
    <article class="cyber-card">
        <h2>Optimize</h2>
        <p>Technical SEO, Core Web Vitals, conversion design, PPC readiness and analytics setup so performance can be measured and improved.</p>
    </article>
    <article class="cyber-card">
        <h2>Protect</h2>
        <p>Security-first implementation, penetration testing, maintenance, backups and support workflows for long-term reliability.</p>
    </article>
</section>
<section class="section reveal">
    <div class="service-grid">
        <?php foreach ($services as $service): ?>
            <a class="cyber-card service-card" href="/services/<?= e($service['slug']) ?>">
                <i class="<?= e($service['icon']) ?>"></i>
                <?= service_visuals($service['visuals'] ?? [], 'service-logo-strip compact', 3) ?>
                <h3><?= e($service['title']) ?></h3>
                <p><?= e($service['summary']) ?></p>
                <span class="small-link">Explore service</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="subhero">
    <span class="status-chip"><span></span> Some Of Our Latest Work</span>
    <h1>Portfolio</h1>
    <p>Live websites, lead-generation platforms, service brands, healthcare sites and e-commerce builds framed around speed, trust, search visibility and conversion.</p>
</section>
<section class="triple-panel reveal">
    <article class="cyber-card"><h2>Tourism & Local Business</h2><p>Beautiful, fast websites for destinations, service brands and businesses that need enquiries from search and social traffic.</p></article>
    <article class="cyber-card"><h2>Dashboards & Platforms</h2><p>Operational interfaces for reporting, client portals, booking flows and internal productivity systems.</p></article>
    <article class="cyber-card"><h2>Commerce & SEO</h2><p>Stores and content systems tuned for product discovery, checkout confidence and organic traffic growth.</p></article>
</section>
<section class="section reveal">
    <div class="portfolio-grid large">
        <?php foreach ($portfolio as $index => $item): ?>
            <article class="portfolio-card accent-<?= e($item['accent']) ?>">
                <div class="portfolio-art <?= !empty($item['image']) ? 'portfolio-shot' : 'art-' . ($index + 1) ?>"<?= !empty($item['image']) ? ' style="--portfolio-image: url(\'' . e(asset('images/portfolio/' . $item['image'])) . '\')"' : '' ?>></div>
                <h3><?= e($item['title']) ?></h3>
                <p><?= e($item['summary']) ?></p>
                <span><i class="fa-solid fa-layer-group"></i><?= e($item['category']) ?></span>
                <?php if (!empty($item['url'])): ?>
                    <a class="small-link portfolio-live-link" href="<?= e($item['url']) ?>" target="_blank" rel="noopener">
                        Visit <?= e(parse_url($item['url'], PHP_URL_HOST) ?: 'website') ?> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

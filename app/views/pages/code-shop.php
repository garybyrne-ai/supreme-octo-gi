<section class="subhero code-shop-hero reveal">
    <span class="kicker">Digital Code Shop</span>
    <h1>Production-ready PHP products for faster commercial launches.</h1>
    <p>Buy complete website systems and interface modules built around secure payments, private ZIP delivery, clean backend structure and performance-first frontend craft.</p>
    <div class="button-row centered">
        <a class="pill-button" href="#shop-products">Browse Products <i class="fa-solid fa-arrow-right"></i></a>
        <a class="pill-button ghost" href="/support">Ask Before Buying <i class="fa-solid fa-ticket"></i></a>
    </div>
</section>

<section class="code-shop-strip reveal" aria-label="Code shop standards">
    <article><i class="fa-solid fa-shield-halved"></i><strong>Private ZIP Vault</strong><span>Download files stay outside the public root with short-lived access tokens.</span></article>
    <article><i class="fa-solid fa-credit-card"></i><strong>Gateway Ready</strong><span>Stripe webhook validation and PayPal checkout fields are built into the shop flow.</span></article>
    <article><i class="fa-solid fa-gauge-high"></i><strong>Vitals-Aware UI</strong><span>Packages are shaped for fast paint, stable layouts and clean mobile interaction.</span></article>
    <article><i class="fa-solid fa-code"></i><strong>Lean PHP Core</strong><span>No heavy framework dependency: just clear PHP 8, MySQL and maintainable modules.</span></article>
</section>

<?php
$categoryLabels = \App\Models\CommerceRepository::CATALOG_CATEGORIES;
$serviceLabels = \App\Models\CommerceRepository::SERVICE_DELIVERIES;
$presentCategories = [];
foreach ($products as $p) {
    $key = (string) ($p['catalog_category'] ?? 'file');
    if (!isset($presentCategories[$key])) {
        $presentCategories[$key] = $categoryLabels[$key] ?? 'Digital Product';
    }
}
?>
<section class="section reveal" id="shop-products">
    <div class="section-heading">
        <span class="kicker">Marketplace</span>
        <h2>Themes, plugins, templates and done-for-you services — ready to launch.</h2>
    </div>
    <?php if (count($presentCategories) > 1): ?>
        <div class="catalog-filter" role="tablist" aria-label="Filter products by category">
            <button type="button" class="is-active" data-catalog-filter="all">All</button>
            <?php foreach ($presentCategories as $key => $label): ?>
                <button type="button" data-catalog-filter="<?= e($key) ?>"><?= e($label) ?></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="code-product-grid" data-catalog-grid>
        <?php foreach ($products as $product): ?>
            <?php
            $catKey = (string) ($product['catalog_category'] ?? 'file');
            $catLabel = $categoryLabels[$catKey] ?? 'Digital Product';
            $serviceKey = (string) ($product['service_delivery'] ?? '');
            $extended = $product['extended_price_cents'] ?? null;
            ?>
            <article class="code-product-card cyber-card" data-category="<?= e($catKey) ?>">
                <?php if (!empty($product['thumbnail_url'])): ?>
                    <div class="code-product-thumb">
                        <img src="<?= e($product['thumbnail_url']) ?>" alt="<?= e($product['title']) ?> preview" loading="lazy">
                    </div>
                <?php else: ?>
                    <div class="code-product-visual" aria-hidden="true">
                        <span class="phone-frame">
                            <i class="fa-solid fa-camera"></i>
                            <b></b>
                            <em>SCAN</em>
                        </span>
                        <span class="product-orbit orbit-one"></span>
                        <span class="product-orbit orbit-two"></span>
                    </div>
                <?php endif; ?>
                <div class="code-product-copy">
                    <div class="catalog-card-meta">
                        <span class="catalog-chip"><?= e($catLabel) ?></span>
                        <?php if ($serviceKey !== '' && isset($serviceLabels[$serviceKey])): ?>
                            <span class="catalog-chip service"><?= e($serviceLabels[$serviceKey]) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($product['is_featured'])): ?>
                            <span class="catalog-chip">★ Featured</span>
                        <?php endif; ?>
                    </div>
                    <h3><?= e($product['title']) ?></h3>
                    <?php if (!empty($product['subtitle'])): ?><p class="code-product-subtitle"><?= e($product['subtitle']) ?></p><?php endif; ?>
                    <p><?= e($product['summary']) ?></p>
                    <div class="code-tags">
                        <?php foreach (array_slice($product['platform_tags'], 0, 8) as $tag): ?>
                            <span><?= e($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="code-product-bottom">
                        <span class="catalog-price-line">
                            <strong><?= e($product['currency']) ?> <?= number_format(((int) $product['price_cents']) / 100, 2) ?></strong>
                            <?php if ($extended !== null && $extended !== ''): ?>
                                <span class="ext">Extended <?= e($product['currency']) ?> <?= number_format(((int) $extended) / 100, 2) ?></span>
                            <?php endif; ?>
                        </span>
                        <div class="button-row">
                            <?php if (!empty($product['demo_url'])): ?>
                                <a class="pill-button ghost" href="<?= e($product['demo_url']) ?>" target="_blank" rel="noopener">Live Demo <i class="fa-solid fa-up-right-from-square"></i></a>
                            <?php endif; ?>
                            <a class="pill-button" href="/code-shop/<?= e($product['slug']) ?>">View Item <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <p class="catalog-empty-note" data-catalog-empty hidden>No items in this category yet.</p>
</section>

<script>
(function () {
    var filters = document.querySelectorAll('[data-catalog-filter]');
    var grid = document.querySelector('[data-catalog-grid]');
    if (!filters.length || !grid) { return; }
    var empty = document.querySelector('[data-catalog-empty]');
    var cards = grid.querySelectorAll('.code-product-card');
    filters.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-catalog-filter');
            filters.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            var shown = 0;
            cards.forEach(function (card) {
                var match = target === 'all' || card.getAttribute('data-category') === target;
                card.hidden = !match;
                if (match) { shown++; }
            });
            if (empty) { empty.hidden = shown !== 0; }
        });
    });
})();
</script>

<section class="home-tools-cta code-shop-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker">Need a custom code product?</span>
        <h2>Turn a repeatable business workflow into a product customers can buy online.</h2>
        <p>From camera upload journeys to paid downloads, admin systems, VAT rules, shipping logic and secure webhooks, Crest can package the full flow as a lean PHP/MySQL product.</p>
        <div class="button-row">
            <a class="pill-button" href="/contact">Request Custom Build <i class="fa-solid fa-paper-plane"></i></a>
            <a class="pill-button ghost" href="/services/website-development">Website Development <i class="fa-solid fa-code"></i></a>
        </div>
    </div>
    <div class="home-tools-orbit code-shop-orbit" aria-hidden="true">
        <span><i class="fa-solid fa-camera"></i></span>
        <span><i class="fa-solid fa-credit-card"></i></span>
        <span><i class="fa-solid fa-truck"></i></span>
        <span><i class="fa-solid fa-shield-halved"></i></span>
        <strong>SHOP</strong>
    </div>
</section>

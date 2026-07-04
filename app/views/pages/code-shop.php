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

<section class="section reveal" id="shop-products">
    <div class="section-heading">
        <span class="kicker">Featured Package</span>
        <h2>Launch a sellable digital service with the core system already in place.</h2>
    </div>
    <div class="code-product-grid">
        <?php foreach ($products as $product): ?>
            <article class="code-product-card cyber-card">
                <div class="code-product-visual" aria-hidden="true">
                    <span class="phone-frame">
                        <i class="fa-solid fa-camera"></i>
                        <b></b>
                        <em>SCAN</em>
                    </span>
                    <span class="product-orbit orbit-one"></span>
                    <span class="product-orbit orbit-two"></span>
                </div>
                <div class="code-product-copy">
                    <span class="status-chip"><span></span><?= e($product['platform']) ?> Package</span>
                    <h3><?= e($product['title']) ?></h3>
                    <p><?= e($product['summary']) ?></p>
                    <div class="code-tags">
                        <?php foreach (array_slice($product['platform_tags'], 0, 8) as $tag): ?>
                            <span><?= e($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="code-product-bottom">
                        <strong><?= e($product['currency']) ?> <?= number_format(((int) $product['price_cents']) / 100, 2) ?></strong>
                        <a class="pill-button" href="/code-shop/<?= e($product['slug']) ?>">View Package <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

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

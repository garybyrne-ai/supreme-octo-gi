<section class="subhero code-product-hero reveal">
    <span class="kicker"><?= e($product['platform']) ?> Code Package</span>
    <h1><?= e($product['title']) ?></h1>
    <p><?= e($product['summary']) ?></p>
    <form class="button-row centered checkout-button-row" method="post" action="/code-shop/<?= e($product['slug']) ?>/checkout">
        <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
        <button class="pill-button" type="submit" name="gateway" value="stripe">Buy With Card <i class="fa-solid fa-credit-card"></i></button>
        <button class="pill-button ghost" type="submit" name="gateway" value="paypal">Buy With PayPal <i class="fa-brands fa-paypal"></i></button>
        <a class="pill-button ghost" href="/support">Ask A Question <i class="fa-solid fa-ticket"></i></a>
    </form>
</section>

<section class="code-product-detail reveal">
    <article class="cyber-card code-detail-copy">
        <span class="status-chip"><span></span> What you get</span>
        <h2>A complete PHP service product, not a loose template.</h2>
        <p><?= e($product['description'] ?? $product['summary']) ?></p>
        <div class="code-tags large">
            <?php foreach ($product['platform_tags'] as $tag): ?>
                <span><?= e($tag) ?></span>
            <?php endforeach; ?>
        </div>
    </article>

    <aside class="cyber-card code-buy-panel">
        <span class="kicker">Package Price</span>
        <strong><?= e($product['currency']) ?> <?= number_format(((int) $product['price_cents']) / 100, 2) ?></strong>
        <p>Secure checkout can run through Stripe or PayPal. File purchases are delivered with a 24-hour, one-use download token.</p>
        <form class="checkout-panel-form" method="post" action="/code-shop/<?= e($product['slug']) ?>/checkout">
            <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
            <button class="pill-button" type="submit" name="gateway" value="stripe">Card Checkout <i class="fa-solid fa-credit-card"></i></button>
            <button class="pill-button ghost" type="submit" name="gateway" value="paypal">PayPal Checkout <i class="fa-brands fa-paypal"></i></button>
        </form>
        <small><i class="fa-solid fa-lock"></i> ZIP files are stored outside the public web root.</small>
    </aside>
</section>

<?php if (($product['slug'] ?? '') === 'photo-to-key-php-website-backend'): ?>
    <section class="photo-key-showcase reveal">
        <div class="photo-key-phone" aria-hidden="true">
            <span class="scan-corners"></span>
            <i class="fa-solid fa-key"></i>
            <b></b>
            <em>Scanning key profile...</em>
        </div>
        <div>
            <span class="kicker">Camera-first ordering</span>
            <h2>Customers snap the key, submit details, pay and receive delivery.</h2>
            <p>This product is designed for locksmith and key-cutting businesses that want a modern online ordering flow. It supports browser camera access, existing photo uploads, delivery details, order status language, VAT and shipping configuration points, and payment integration hooks.</p>
            <div class="photo-key-flow">
                <span><i class="fa-solid fa-camera"></i> Take Photo</span>
                <span><i class="fa-solid fa-cloud-arrow-up"></i> Upload Details</span>
                <span><i class="fa-solid fa-credit-card"></i> Secure Checkout</span>
                <span><i class="fa-solid fa-truck"></i> Ship Order</span>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (($product['product_type'] ?? '') === 'snippet'): ?>
    <section class="snippet-preview-panel reveal">
        <div class="section-heading">
            <span class="kicker">Sandboxed Preview</span>
            <h2>Rendered safely before you copy.</h2>
        </div>
        <iframe class="snippet-preview-frame" sandbox srcdoc="<?= e($snippetSrcdoc) ?>" title="<?= e($product['title']) ?> preview"></iframe>
        <?php if (!empty($product['snippet_js'])): ?>
            <pre class="snippet-code"><code><?= e($product['snippet_js']) ?></code></pre>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="code-security-grid reveal">
    <article><i class="fa-solid fa-database"></i><strong>Prepared Queries</strong><span>Purchase and token queries bind every dynamic value through PDO.</span></article>
    <article><i class="fa-solid fa-fingerprint"></i><strong>Verified Webhooks</strong><span>Stripe signatures are checked before any purchase is logged.</span></article>
    <article><i class="fa-solid fa-folder-closed"></i><strong>Private File Path</strong><span>Downloads resolve inside the private code shop directory only.</span></article>
    <article><i class="fa-solid fa-clock"></i><strong>Expiring Links</strong><span>Download tokens expire after 24 hours and are marked used.</span></article>
</section>

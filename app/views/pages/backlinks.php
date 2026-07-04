<?php
/** @var array<int, array<string,mixed>> $plans */
$plans = $plans ?? [];
$old = $old ?? [];
?>
<section class="subhero membership-hero">
    <span class="status-chip"><span></span> Irish Link Building</span>
    <h1>Buy Rare <span>Irish (.ie) Backlinks</span> That Actually Move Rankings</h1>
    <p>Genuine .ie backlinks are hard to find and harder to build — most agencies can't get them. We place white-hat, editorial, niche-relevant Irish links with a live URL report. Start with <strong>25 Irish backlinks for €59</strong>.</p>
    <div class="button-row">
        <a class="pill-button" href="#backlink-plans">See Plans <i class="fa-solid fa-arrow-right"></i></a>
        <a class="pill-button ghost" href="#backlink-order">Order Now <i class="fa-solid fa-link"></i></a>
    </div>
</section>

<?php if (!empty($success)): ?><div class="notice success" style="max-width:900px;margin:0 auto 8px"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="notice error" style="max-width:900px;margin:0 auto 8px"><?= e($error) ?></div><?php endif; ?>

<section class="section reveal">
    <div class="local-trust-row">
        <article><strong>.ie</strong><span>Real Irish top-level-domain links, rarely available</span></article>
        <article><strong>White-hat</strong><span>Manual, editorial, niche-relevant placements</span></article>
        <article><strong>Reported</strong><span>Live URLs you can click and verify</span></article>
        <article><strong>Safe</strong><span>Natural velocity &amp; anchor diversity — no PBNs</span></article>
    </div>
</section>

<section class="section reveal" id="backlink-plans">
    <div class="section-heading">
        <span class="kicker">Backlink Plans</span>
        <h2>Irish backlink packages for every budget</h2>
        <p>Fixed-price, transparent link building. Choose a plan, then complete the short order form below.</p>
    </div>
    <div class="membership-grid">
        <?php foreach ($plans as $plan): ?>
            <article class="membership-card cyber-card <?= !empty($plan['featured']) ? 'is-featured' : '' ?>">
                <?php if (!empty($plan['badge'])): ?><span class="membership-badge"><?= e($plan['badge']) ?></span><?php endif; ?>
                <header class="membership-card-head">
                    <h2><?= e($plan['name']) ?></h2>
                    <p><?= e($plan['links']) ?></p>
                </header>
                <div class="membership-card-price">
                    <strong><?= e($plan['price']) ?></strong><span>one-time</span>
                </div>
                <ul class="check-list">
                    <?php foreach ($plan['features'] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?>
                </ul>
                <div class="membership-card-actions">
                    <?php if (!empty($plan['checkout_url'])): ?>
                        <a class="pill-button" href="<?= e($plan['checkout_url']) ?>" rel="nofollow">Buy <?= e($plan['name']) ?> <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <a class="pill-button" href="#backlink-order" data-select-plan="<?= e($plan['slug']) ?>">Order <?= e($plan['name']) ?> <i class="fa-solid fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="split-section reveal">
    <div>
        <h2>Why Irish (.ie) backlinks are worth more</h2>
        <p>For a business targeting Ireland, a link from a genuine <strong>.ie</strong> domain is one of the strongest local relevance signals you can earn. They're scarce because .ie registration is restricted and Irish publishers are selective — which is exactly why they carry weight and why competitors struggle to replicate them.</p>
    </div>
    <div class="cyber-card">
        <h2>Our link-building principles</h2>
        <ul class="check-list">
            <li>Editorial, in-content placements — not footers or link farms</li>
            <li>Topically relevant Irish blogs, news &amp; local sites</li>
            <li>Natural anchor-text mix (brand, URL, partial, generic)</li>
            <li>Gradual, safe link velocity to avoid spam signals</li>
            <li>No PBNs, no auto-generated spam, no link exchanges</li>
            <li>Transparent live-URL reporting for every link</li>
        </ul>
    </div>
</section>

<section class="section reveal" id="backlink-order">
    <div class="section-heading">
        <span class="kicker">Place Your Order</span>
        <h2>Start your Irish backlink campaign</h2>
        <p>Complete this form and we'll email a secure payment link and confirm scope within one business day.</p>
    </div>
    <form class="cyber-form" method="post" action="/backlinks/order" style="max-width:760px;margin:0 auto" id="backlink-order-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="form-grid two">
            <label>Name <input name="name" required value="<?= e($old['name'] ?? '') ?>"></label>
            <label>Email <input name="email" type="email" required value="<?= e($old['email'] ?? '') ?>"></label>
        </div>
        <label>Your Website <input name="website" required placeholder="https://yourbusiness.ie" value="<?= e($old['website'] ?? '') ?>"></label>
        <label>Plan
            <select name="plan" id="backlink-plan-select">
                <?php foreach ($plans as $plan): ?>
                    <option value="<?= e($plan['slug']) ?>" <?= ($old['plan'] ?? '') === $plan['slug'] ? 'selected' : '' ?>><?= e($plan['name']) ?> — <?= e($plan['price']) ?> (<?= e(strip_tags((string) $plan['links'])) ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Target Keywords <span class="cyber-hint">optional</span>
            <input name="keywords" placeholder="e.g. web design dublin, accountant cork" value="<?= e($old['keywords'] ?? '') ?>">
        </label>
        <label>Notes <textarea name="notes" rows="4" placeholder="Target pages, niche, anchors to avoid, deadlines..."><?= e($old['notes'] ?? '') ?></textarea></label>
        <label class="captcha-field">PHP Captcha
            <span class="captcha-box"><?= e($captcha['question'] ?? '') ?> = ?</span>
            <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Enter answer">
        </label>
        <button class="pill-button" type="submit">Submit Order <i class="fa-solid fa-paper-plane"></i></button>
    </form>
</section>

<section class="section narrow reveal">
    <div class="section-heading"><h2>Backlink FAQ</h2></div>
    <div class="faq-list">
        <details><summary>Are these safe, white-hat links?</summary><p>Yes. Every link is a manual, editorial placement on a real, relevant site. We never use PBNs, spam networks or automated tools, and we build at a natural pace with diverse anchors.</p></details>
        <details><summary>Why are .ie backlinks so rare?</summary><p>.ie domains have restricted registration and Irish publishers are selective, so genuine Irish links are scarce — which is exactly why they're valuable for ranking in Ireland.</p></details>
        <details><summary>How do I know the links are real?</summary><p>You get a report with the live URL of every placement so you can click and verify each one.</p></details>
        <details><summary>How long does delivery take?</summary><p>Links are built gradually over 3–4 weeks (longer for larger plans) to keep your profile natural and safe.</p></details>
        <details><summary>Do you guarantee rankings?</summary><p>No ethical provider can guarantee exact positions, but relevant, high-quality Irish links are one of the strongest local ranking factors and we report on the impact.</p></details>
    </div>
</section>

<script>
(function () {
    document.querySelectorAll('[data-select-plan]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var sel = document.getElementById('backlink-plan-select');
            if (sel) { sel.value = btn.getAttribute('data-select-plan'); }
        });
    });
})();
</script>

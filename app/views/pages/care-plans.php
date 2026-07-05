<?php
/** @var array<int, array<string,mixed>> $plans */
$plans = $plans ?? [];
$old = $old ?? [];
?>
<section class="subhero membership-hero">
    <span class="status-chip"><span></span> Recurring Peace Of Mind</span>
    <h1>Website Care Plans That <span>Protect Your Revenue</span></h1>
    <p>Backups, updates, uptime and security monitoring, speed tune-ups, SEO reporting — all handled for you every month, from <strong>€49/month</strong>. One hack cleanup or a week of downtime costs more than a year of care.</p>
    <div class="button-row">
        <a class="pill-button" href="#care-plans">Compare Plans <i class="fa-solid fa-arrow-right"></i></a>
        <a class="pill-button ghost" href="#care-order">Start Today <i class="fa-solid fa-heart-pulse"></i></a>
    </div>
</section>

<?php if (!empty($success)): ?><div class="notice success" style="max-width:900px;margin:0 auto 8px"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="notice error" style="max-width:900px;margin:0 auto 8px"><?= e($error) ?></div><?php endif; ?>

<section class="section reveal">
    <div class="local-trust-row">
        <article><strong>Patched</strong><span>Stale plugins are the #1 way small sites get hacked</span></article>
        <article><strong>Backed up</strong><span>Off-site copies mean a bad update is a 10-minute rollback</span></article>
        <article><strong>Watched</strong><span>Uptime &amp; security monitoring alerts us before your customers notice</span></article>
        <article><strong>Improved</strong><span>Real dev hours every month — not just maintenance</span></article>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">Why The Price Rises</span>
        <h2>Each tier protects a bigger business risk</h2>
        <p>You're not paying for "more of the same" — each step up covers a site where more money is on the line.</p>
    </div>
    <div class="care-ladder">
        <article class="care-ladder-step">
            <em>€49</em>
            <strong>Keep it safe &amp; online</strong>
            <p>A brochure site's job is to be there, be secure and stay current. Essential Care covers exactly that: backups, updates, monitoring and small fixes — the baseline every website needs.</p>
        </article>
        <article class="care-ladder-step is-mid">
            <em>€99</em>
            <strong>Keep it ranking &amp; converting</strong>
            <p>A lead-generation site earns money through Google and conversions. That demands speed tune-ups, monthly SEO health reports, a staging site and real development hours — work, not just patching.</p>
        </article>
        <article class="care-ladder-step is-top">
            <em>€199</em>
            <strong>Keep revenue flowing 24/7</strong>
            <p>For e-shops, booking systems, Google Ads campaigns and large SEO sites, every hour something breaks costs money. This tier adds checkout &amp; payment monitoring, active Google Ads management, ongoing SEO and same-day emergency response.</p>
        </article>
    </div>
</section>

<section class="section reveal" id="care-plans">
    <div class="section-heading">
        <span class="kicker">Care Plans</span>
        <h2>Choose the level your website earns at</h2>
        <p>Fixed monthly price, no long contracts — upgrade or cancel with 30 days' notice.</p>
    </div>
    <div class="membership-grid">
        <?php foreach ($plans as $plan): ?>
            <article class="membership-card cyber-card <?= !empty($plan['featured']) ? 'is-featured' : '' ?>">
                <?php if (!empty($plan['badge'])): ?><span class="membership-badge"><?= e($plan['badge']) ?></span><?php endif; ?>
                <header class="membership-card-head">
                    <h2><?= e($plan['name']) ?></h2>
                    <p><?= e($plan['ideal_for']) ?></p>
                </header>
                <div class="membership-card-price">
                    <strong><?= e($plan['price']) ?></strong><span><?= e($plan['period'] ?? '/month') ?></span>
                </div>
                <ul class="check-list">
                    <?php foreach ($plan['features'] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?>
                </ul>
                <div class="membership-card-actions">
                    <?php if (!empty($plan['checkout_url'])): ?>
                        <a class="pill-button" href="<?= e($plan['checkout_url']) ?>" rel="nofollow">Subscribe — <?= e($plan['name']) ?> <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <a class="pill-button" href="#care-order" data-select-plan="<?= e($plan['slug']) ?>">Start <?= e($plan['name']) ?> <i class="fa-solid fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section narrow reveal">
    <div class="cyber-card care-includes">
        <h2><i class="fa-solid fa-shield-heart"></i> Every plan includes</h2>
        <p>A named human who already knows your website · monthly work log of everything done · restore cover if an update breaks something · cancel or change tier with 30 days' notice — no lock-in contracts.</p>
    </div>
</section>

<section class="section narrow reveal" id="care-order">
    <div class="section-heading">
        <span class="kicker">Start Your Plan</span>
        <h2>Get your website looked after</h2>
        <p>Complete this form and we'll email a secure payment link and your onboarding checklist within one business day.</p>
    </div>
    <form class="cyber-form" method="post" action="/website-care-plans/order" style="max-width:760px;margin:0 auto" id="care-order-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="form-grid two">
            <label>Name <input name="name" required value="<?= e($old['name'] ?? '') ?>"></label>
            <label>Email <input name="email" type="email" required value="<?= e($old['email'] ?? '') ?>"></label>
        </div>
        <label>Your Website <input name="website" required placeholder="https://yourbusiness.ie" value="<?= e($old['website'] ?? '') ?>"></label>
        <label>Plan
            <select name="plan" id="care-plan-select">
                <?php foreach ($plans as $plan): ?>
                    <option value="<?= e($plan['slug']) ?>" <?= ($old['plan'] ?? '') === $plan['slug'] ? 'selected' : '' ?>><?= e($plan['name']) ?> — <?= e($plan['price']) ?><?= e($plan['period'] ?? '/month') ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-grid two">
            <label>Coupon Code <span class="cyber-hint">optional</span>
                <input name="coupon" placeholder="e.g. LAUNCH50" value="<?= e($old['coupon'] ?? '') ?>"></label>
            <label class="captcha-field">PHP Captcha
                <span class="captcha-box"><?= e($captcha['question'] ?? '') ?> = ?</span>
                <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Enter answer">
            </label>
        </div>
        <label>Notes <span class="cyber-hint">optional</span> <textarea name="notes" rows="4" placeholder="Platform (WordPress, custom…), known issues, what worries you most…"><?= e($old['notes'] ?? '') ?></textarea></label>
        <button class="pill-button" type="submit">Submit Order <i class="fa-solid fa-paper-plane"></i></button>
    </form>
</section>

<section class="section narrow reveal">
    <div class="section-heading"><h2>Care Plan FAQ</h2></div>
    <div class="faq-list">
        <details><summary>What happens after I order?</summary><p>You get a payment link and an onboarding checklist by email. Once access is set up, we take an immediate full backup, run a baseline security and speed scan, and send you the report — usually within 2 business days.</p></details>
        <details><summary>Do unused hours roll over?</summary><p>Development hours are use-it-or-lose-it each month (that's how we keep the price low), but small overruns are never nickel-and-dimed.</p></details>
        <details><summary>My site is on WordPress / Shopify / custom code — covered?</summary><p>Yes. WordPress and custom PHP sites get the full treatment. Hosted platforms like Shopify or Squarespace get everything except core updates (the platform handles those), so we put that time into speed, SEO and content instead.</p></details>
        <details><summary>What counts as an emergency on the €199 plan?</summary><p>Anything that stops money: checkout failures, payment errors, site down, hacked pages, or a disapproved Google Ads campaign. Same-day response, seven days a week.</p></details>
        <details><summary>Can I cancel?</summary><p>Any time with 30 days' notice. You keep every backup, report and improvement we made — your site, your data.</p></details>
    </div>
</section>

<script>
(function () {
    var select = document.getElementById('care-plan-select');
    if (!select) { return; }
    document.querySelectorAll('[data-select-plan]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            select.value = btn.getAttribute('data-select-plan');
        });
    });
})();
</script>

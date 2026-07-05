<?php
$old = $old ?? [];
?>
<section class="subhero membership-hero">
    <span class="status-chip"><span></span> Fixed Price · Guaranteed Result</span>
    <h1>Speed Rescue: Under <span>2 Seconds</span> Or You Don't Pay</h1>
    <p>Slow websites lose rankings, ads money and customers. For a fixed <strong>€149</strong> we tune your images, caching, code and server until your site loads in under 2 seconds — measured with Google's own PageSpeed test, before and after.</p>
    <div class="button-row">
        <a class="pill-button" href="#speed-order">Rescue My Website <i class="fa-solid fa-bolt"></i></a>
        <a class="pill-button ghost" href="/tools/pagespeed">Test Your Speed Free <i class="fa-solid fa-gauge-high"></i></a>
    </div>
</section>

<?php if (!empty($success)): ?><div class="notice success" style="max-width:900px;margin:0 auto 8px"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="notice error" style="max-width:900px;margin:0 auto 8px"><?= e($error) ?></div><?php endif; ?>

<section class="section reveal">
    <div class="local-trust-row">
        <article><strong>Rankings</strong><span>Core Web Vitals are a Google ranking signal — slow sites sink</span></article>
        <article><strong>Ads spend</strong><span>Slow landing pages raise your cost-per-click and waste budget</span></article>
        <article><strong>Sales</strong><span>Every extra second of loading measurably cuts conversions</span></article>
        <article><strong>Proof</strong><span>Before &amp; after Google PageSpeed reports — you see the difference</span></article>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading">
        <span class="kicker">What We Fix</span>
        <h2>The six things that make websites slow</h2>
        <p>We work through all of them on your site — nothing is left to "maybe later".</p>
    </div>
    <div class="audit-includes-grid">
        <article><i class="fa-solid fa-image"></i><strong>Heavy Images</strong><span>Compression, WebP conversion, correct sizes and lazy loading.</span></article>
        <article><i class="fa-solid fa-database"></i><strong>No Caching</strong><span>Browser + server caching so repeat views are instant.</span></article>
        <article><i class="fa-solid fa-file-code"></i><strong>Code Bloat</strong><span>Minified, deferred JS &amp; CSS; unused plugin weight removed.</span></article>
        <article><i class="fa-solid fa-font"></i><strong>Slow Fonts</strong><span>Font loading fixed so text never blocks the page.</span></article>
        <article><i class="fa-solid fa-server"></i><strong>Slow Server</strong><span>TTFB, compression and PHP/host configuration tuned.</span></article>
        <article><i class="fa-solid fa-hard-drive"></i><strong>Bloated Database</strong><span>Revisions, transients and junk cleared (WordPress &amp; custom).</span></article>
    </div>
</section>

<section class="section narrow reveal">
    <div class="cyber-card speed-guarantee">
        <h2><i class="fa-solid fa-handshake"></i> The guarantee, in plain words</h2>
        <p>We measure your homepage with <strong>Google PageSpeed Insights</strong> before we start and after we finish. If we can't get your load time under <strong>2 seconds</strong>, you pay nothing — no arguing, no small print gymnastics.</p>
        <small>Fair play terms: applies to sites we're given working admin/hosting access to, measured on the homepage over a standard connection. Third-party embeds we can't control (e.g. live chat widgets you must keep) are optimised as far as they allow — and we'll show you their exact cost either way.</small>
    </div>
</section>

<section class="section narrow reveal" id="speed-order">
    <div class="section-heading">
        <span class="kicker">Fixed Price — €149</span>
        <h2>Rescue your website's speed</h2>
        <p>Complete this form and we'll email your baseline PageSpeed report and a secure payment link within one business day.</p>
    </div>
    <form class="cyber-form" method="post" action="/speed-rescue/order" style="max-width:760px;margin:0 auto">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="plan" value="speed-rescue">
        <div class="form-grid two">
            <label>Name <input name="name" required value="<?= e($old['name'] ?? '') ?>"></label>
            <label>Email <input name="email" type="email" required value="<?= e($old['email'] ?? '') ?>"></label>
        </div>
        <label>Website To Speed Up <input name="website" required placeholder="https://yourbusiness.com" value="<?= e($old['website'] ?? '') ?>"></label>
        <div class="form-grid two">
            <label>Coupon Code <span class="cyber-hint">optional</span>
                <input name="coupon" placeholder="e.g. LAUNCH50" value="<?= e($old['coupon'] ?? '') ?>"></label>
            <label class="captcha-field">PHP Captcha
                <span class="captcha-box"><?= e($captcha['question'] ?? '') ?> = ?</span>
                <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Enter answer">
            </label>
        </div>
        <label>Platform &amp; anything we should know <span class="cyber-hint">optional</span> <textarea name="notes" rows="4" placeholder="e.g. WordPress on shared hosting · WooCommerce with 400 products · we run Google Ads to this site"><?= e($old['notes'] ?? '') ?></textarea></label>
        <button class="pill-button" type="submit">Order Speed Rescue — €149 <i class="fa-solid fa-paper-plane"></i></button>
    </form>
</section>

<section class="section narrow reveal">
    <div class="section-heading"><h2>Speed Rescue FAQ</h2></div>
    <div class="faq-list">
        <details><summary>How long does it take?</summary><p>Most sites are done within 3–5 business days of getting access. E-commerce sites with large catalogues can take up to a week.</p></details>
        <details><summary>Will anything break?</summary><p>We take a full backup before touching anything and test every change. If an optimisation conflicts with a plugin or feature, we roll it back and find another route.</p></details>
        <details><summary>What do you need from me?</summary><p>Admin access (e.g. WordPress login) and hosting access. We'll send a short, secure checklist after you order.</p></details>
        <details><summary>What if my hosting is the real problem?</summary><p>We'll tell you straight, tune what can be tuned, and recommend a specific better host with a migration quote — the €149 counts toward that work if you go ahead.</p></details>
        <details><summary>Does the result last?</summary><p>Yes — and if you'd like it defended long-term (updates and new content slow sites back down over time), our <a href="/website-care-plans">Care Plans</a> include quarterly speed tune-ups.</p></details>
    </div>
</section>

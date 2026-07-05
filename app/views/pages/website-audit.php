<?php
$old = $old ?? [];
?>
<section class="subhero membership-hero">
    <span class="status-chip"><span></span> One-Time · No Subscription</span>
    <h1>Full Website Audit — <span>€49, Once</span></h1>
    <p>Every scan we run for agencies — security, SEO, speed, TLS, DNS &amp; email, technology — reviewed by a human and delivered as a <strong>client-ready, white-label PDF</strong> with a prioritised fix list. Within one business day.</p>
    <div class="button-row">
        <a class="pill-button" href="#audit-order">Order Your Audit <i class="fa-solid fa-file-shield"></i></a>
        <a class="pill-button ghost" href="#audit-includes">What's Checked <i class="fa-solid fa-list-check"></i></a>
    </div>
</section>

<?php if (!empty($success)): ?><div class="notice success" style="max-width:900px;margin:0 auto 8px"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="notice error" style="max-width:900px;margin:0 auto 8px"><?= e($error) ?></div><?php endif; ?>

<section class="section reveal" id="audit-includes">
    <div class="section-heading">
        <span class="kicker">Seven Angles, One Report</span>
        <h2>What your €49 audit covers</h2>
        <p>The same toolkit behind our Growth Lab platform — plus a human review that a scanner alone can't give you.</p>
    </div>
    <div class="audit-includes-grid">
        <article><i class="fa-solid fa-lock"></i><strong>Security Headers</strong><span>HSTS, CSP, clickjacking &amp; cookie protection graded A–F.</span></article>
        <article><i class="fa-solid fa-certificate"></i><strong>TLS / SSL</strong><span>Certificate health, expiry risk and protocol setup.</span></article>
        <article><i class="fa-solid fa-envelope-circle-check"></i><strong>DNS &amp; Email</strong><span>SPF, DKIM, DMARC — can your email be spoofed?</span></article>
        <article><i class="fa-solid fa-chart-line"></i><strong>On-Page SEO</strong><span>Titles, meta, headings, schema, internal links, indexability.</span></article>
        <article><i class="fa-solid fa-gauge-high"></i><strong>Speed &amp; Core Web Vitals</strong><span>Real Google PageSpeed data with the fixes that matter first.</span></article>
        <article><i class="fa-solid fa-microchip"></i><strong>Technology Stack</strong><span>What your site runs on and where it leaks version info.</span></article>
        <article><i class="fa-solid fa-user-check"></i><strong>Human Review</strong><span>A prioritised, plain-English fix list — not a data dump.</span></article>
    </div>
</section>

<section class="section reveal">
    <div class="care-ladder">
        <article class="care-ladder-step">
            <em>1</em>
            <strong>Order in 2 minutes</strong>
            <p>Tell us your website address and anything you're worried about. Pay by secure link — €49, once.</p>
        </article>
        <article class="care-ladder-step is-mid">
            <em>2</em>
            <strong>We scan &amp; review</strong>
            <p>All seven checks run against your site, then a human reads the results, removes noise and ranks the fixes by impact.</p>
        </article>
        <article class="care-ladder-step is-top">
            <em>3</em>
            <strong>PDF in your inbox</strong>
            <p>A clean, white-label PDF within one business day. Agencies: we can put <strong>your logo</strong> on it and you can resell it.</p>
        </article>
    </div>
</section>

<section class="section narrow reveal" id="audit-order">
    <div class="section-heading">
        <span class="kicker">Order Your Audit</span>
        <h2>Know exactly where your website stands</h2>
        <p>Complete this form and we'll email a secure payment link. Your PDF report lands within one business day of payment.</p>
    </div>
    <form class="cyber-form" method="post" action="/website-audit/order" style="max-width:760px;margin:0 auto">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="plan" value="website-audit">
        <div class="form-grid two">
            <label>Name <input name="name" required value="<?= e($old['name'] ?? '') ?>"></label>
            <label>Email <input name="email" type="email" required value="<?= e($old['email'] ?? '') ?>"></label>
        </div>
        <label>Website To Audit <input name="website" required placeholder="https://yourbusiness.com" value="<?= e($old['website'] ?? '') ?>"></label>
        <div class="form-grid two">
            <label>Coupon Code <span class="cyber-hint">optional</span>
                <input name="coupon" placeholder="e.g. LAUNCH50" value="<?= e($old['coupon'] ?? '') ?>"></label>
            <label class="captcha-field">PHP Captcha
                <span class="captcha-box"><?= e($captcha['question'] ?? '') ?> = ?</span>
                <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Enter answer">
            </label>
        </div>
        <label>Anything specific to look at? <span class="cyber-hint">optional</span> <textarea name="notes" rows="4" placeholder="e.g. we dropped in Google last month · emails going to spam · site feels slow on mobile · white-label with my agency logo"><?= e($old['notes'] ?? '') ?></textarea></label>
        <button class="pill-button" type="submit">Order Audit — €49 <i class="fa-solid fa-paper-plane"></i></button>
    </form>
</section>

<section class="section narrow reveal">
    <div class="section-heading"><h2>Audit FAQ</h2></div>
    <div class="faq-list">
        <details><summary>Is this automated or a real review?</summary><p>Both. Automated scanners collect the data, then a human reads it, removes false alarms, and writes a prioritised fix list in plain English. That review is what you're paying for.</p></details>
        <details><summary>Can I white-label it for my client?</summary><p>Yes — mention it in the notes and we'll put your agency name and logo on the PDF. Many agencies resell this audit at €150–€300.</p></details>
        <details><summary>Will you fix the problems too?</summary><p>The report is yours to action with any developer. If you'd like us to do it, every audit includes a fixed-price quote for the top fixes — and audit customers get the first month of any <a href="/website-care-plans">Care Plan</a> half price.</p></details>
        <details><summary>What do you need from me?</summary><p>Just the URL. Everything we scan is publicly reachable — no passwords or server access required for the audit itself.</p></details>
    </div>
</section>

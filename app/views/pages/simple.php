<section class="subhero">
    <span class="status-chip"><span></span> <?= e(!empty($pageKicker) ? $pageKicker : 'Crest Web Media') ?></span>
    <h1><?= e($pageTitle) ?></h1>
    <p><?= e($metaDescription) ?></p>
</section>

<?php if ($variant === 'about'): ?>
    <section class="split-section reveal">
        <div>
            <h2>Global web systems, sharp design and secure engineering for serious business outcomes.</h2>
            <p>Crest Web Media is a focused digital studio creating websites, applications, API integrations, ecommerce systems, SEO architecture and AI workflows for clients across Ireland, the UK, the USA and Europe. The work blends engineering, design, security and product thinking into digital assets that generate measurable value.</p>
            <p>The studio is built for remote delivery: clear strategy, written-first communication, clean milestones, practical documentation and long-term technical care.</p>
        </div>
        <div class="cyber-card manifesto himalaya-card">
            <h3>The difference</h3>
            <p>The studio standard is calm focus under pressure: conversion-mapped design, resilient systems, disciplined execution and commercial clarity from first screen to final handoff.</p>
        </div>
    </section>
    <section class="triple-panel reveal">
        <article class="cyber-card"><h3>Global Coordination</h3><p>Clear remote communication for clients across time zones, from diagnostic brief to launch review.</p></article>
        <article class="cyber-card himalaya-card"><h3>Deep Focus</h3><p>Careful development work, practical QA and dependable delivery from Shimla, Himachal Pradesh.</p></article>
        <article class="cyber-card"><h3>Commercial Mindset</h3><p>Design, code, SEO and performance choices are tied to qualified leads, trust and revenue.</p></article>
    </section>
<?php elseif ($variant === 'legal'): ?>
    <?php $legalContact = $contact ?? []; $legalEmail = $legalContact['email'] ?? 'ank.kalia@gmail.com'; ?>
    <section class="section narrow reveal">
        <h2>How we handle your data</h2>
        <p>Crest Web Media respects your privacy. This page explains what data we collect, why, the cookies we use, and the rights you have under the GDPR and similar laws. If anything is unclear, email <a href="mailto:<?= e($legalEmail) ?>"><?= e($legalEmail) ?></a>.</p>

        <h3>Information we collect</h3>
        <p>When you submit a contact, order, support or tool form we collect the details you provide (such as name, email and website) to respond to and fulfil your request. For security we may log IP address, device information and timestamps on admin and security events to protect the platform.</p>

        <h3>Cookies we use</h3>
        <p><strong>Essential cookies</strong> (always on) keep the site secure — your session, login and CSRF protection. They store no marketing data and cannot be switched off without breaking the site.</p>
        <p><strong>Analytics &amp; advertising cookies</strong> (optional) are used only if you accept them in our cookie banner. When enabled, we use <strong>Google Analytics</strong> to understand traffic and <strong>Google AdSense</strong> to show ads. These may set cookies and process data as described in <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener nofollow">Google's policies</a>. Until you accept, we apply Google Consent Mode with all advertising and analytics storage <em>denied</em>, so nothing non-essential runs.</p>
        <p>You can change your choice at any time using the <a href="#" data-open-consent>cookie settings</a> button (bottom-left of every page).</p>

        <h3>Google AdSense &amp; third-party ads</h3>
        <p>If ads are enabled, third-party vendors including Google use cookies to serve ads based on prior visits to this and other websites. Google's use of advertising cookies enables it and its partners to serve ads based on your visits. You can opt out of personalised advertising via <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener nofollow">Google Ads Settings</a>, or opt out of some third-party vendors at <a href="https://www.aboutads.info" target="_blank" rel="noopener nofollow">aboutads.info</a>.</p>

        <h3>Your rights</h3>
        <p>You can request access to, correction of, or deletion of your personal data, withdraw consent, or object to processing. To exercise any right, email <a href="mailto:<?= e($legalEmail) ?>"><?= e($legalEmail) ?></a> and we will respond within 30 days.</p>

        <h3>Data retention &amp; sharing</h3>
        <p>We keep enquiry and order data only as long as needed to serve you and meet legal obligations, then delete it. We do not sell your personal data. We share it only with processors needed to run the service (for example payment providers when you buy, and Google if you accept analytics/ads).</p>
    </section>
<?php endif; ?>

<section class="section reveal">
    <div class="section-heading"><h2>Core Capabilities</h2></div>
    <div class="service-grid">
        <?php foreach (array_slice($services, 0, 6) as $service): ?>
            <a class="cyber-card service-card" href="/services/<?= e($service['slug']) ?>">
                <i class="<?= e($service['icon']) ?>"></i>
                <h3><?= e($service['title']) ?></h3>
                <p><?= e($service['summary']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

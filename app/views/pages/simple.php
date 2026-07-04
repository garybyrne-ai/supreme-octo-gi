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
    <section class="section narrow reveal">
        <h2>Plain-English Policy</h2>
        <p>This starter policy explains how Crest Web Media handles enquiries, project data, analytics and operational records. Replace this copy with jurisdiction-specific legal text before production launch.</p>
        <p>Contact form submissions are used to respond to enquiries. Admin and security events may be logged with IP address, device information and timestamps to protect the platform.</p>
        <p>Cookies may be used for session security, analytics and preference storage. Users can request access, correction or deletion of personal data by contacting ank.kalia@gmail.com or calling +91 88948 67819.</p>
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

<?php
$footerContact = (new \App\Models\ContentRepository())->contact();
$techNews = (new \App\Services\TechNewsService())->items();
?>
<?php if (!empty($techNews)): ?>
<section class="tech-news-ticker" aria-label="Latest technology news">
    <div class="ticker-head">
        <span>Latest Tech News</span>
        <strong>Daily signals across AI, SEO, security and web engineering</strong>
    </div>
    <div class="ticker-track" aria-live="polite">
        <div class="ticker-lane">
            <?php foreach (array_merge($techNews, $techNews) as $item): ?>
                <?php $externalNewsLink = !str_starts_with($item['url'], '/'); ?>
                <a href="<?= e($item['url']) ?>"<?= $externalNewsLink ? ' target="_blank" rel="noopener"' : '' ?>>
                    <span><?= e($item['source']) ?></span>
                    <strong><?= e($item['title']) ?></strong>
                    <em><?= e($item['date']) ?></em>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<footer class="site-footer future-footer">
    <section class="footer-command">
        <div>
            <span class="kicker">Crest Web Media Command Desk</span>
            <h2>Ready for a sharper website, app, search system or AI workflow?</h2>
            <p>Send a brief, open a ticket or message on WhatsApp. No forced meeting funnel, just clear written context and a practical next step.</p>
        </div>
        <div class="footer-command-actions">
            <a class="pill-button" href="/contact">Send Project Brief <i class="fa-solid fa-paper-plane"></i></a>
            <a class="pill-button ghost" href="<?= e($footerContact['whatsapp_url']) ?>">WhatsApp Crest <i class="fa-brands fa-whatsapp"></i></a>
            <a class="small-link" href="/support">Open Support Ticket <i class="fa-solid fa-ticket"></i></a>
        </div>
    </section>

    <section class="footer-console professional-footer">
        <div class="footer-brand-panel">
            <a class="brand footer-brand" href="/">
                <span class="brand-logo-wrap">
                    <img class="brand-logo" src="<?= asset('images/crest-web-media-logo.webp') ?>" alt="Crest Web Media" width="170" height="60" loading="lazy">
                    <span class="brand-comet" aria-hidden="true"></span>
                </span>
            </a>
            <p>Enterprise-style web design, secure PHP/MySQL systems, SEO, PPC, app publishing, API integrations and AI automation for Ireland, the UK, the USA and Europe.</p>
            <div class="footer-signal-grid" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
        </div>
        <nav class="footer-nav-panel" aria-label="Footer services">
            <h3>Services</h3>
            <a href="/services/website-development"><i class="fa-solid fa-code"></i> Website Development</a>
            <a href="/services/app-development"><i class="fa-solid fa-mobile-screen"></i> App Development</a>
            <a href="/services/seo"><i class="fa-solid fa-ranking-star"></i> SEO & Rankings</a>
            <a href="/backlinks"><i class="fa-solid fa-link"></i> Buy Irish Backlinks</a>
            <a href="/services/pay-per-click-advertising"><i class="fa-solid fa-bullseye"></i> Pay Per Click</a>
            <a href="/services/ai-integration-services"><i class="fa-solid fa-robot"></i> AI Integration</a>
            <a href="/services/penetration-testing"><i class="fa-solid fa-shield-halved"></i> Penetration Testing</a>
            <a href="/locations"><i class="fa-solid fa-map-location-dot"></i> Areas We Cover</a>
        </nav>
        <nav class="footer-nav-panel" aria-label="Footer growth tools">
            <h3>Growth Tools</h3>
            <a href="/code-shop"><i class="fa-solid fa-cart-shopping"></i> Digital Code Shop</a>
            <a href="/seo-tools"><i class="fa-solid fa-chart-line"></i> SEO Audit Tool</a>
            <a href="/serp-checker"><i class="fa-solid fa-magnifying-glass-chart"></i> SERP Checker</a>
            <a href="/ai-website-growth-consultant"><i class="fa-solid fa-brain"></i> AI Growth Consultant</a>
            <a href="/instant-website-quote-calculator"><i class="fa-solid fa-calculator"></i> Quote Calculator</a>
            <a href="/ppc-roi-calculator"><i class="fa-solid fa-arrow-trend-up"></i> PPC ROI Calculator</a>
            <a href="/before-after-speed-simulator"><i class="fa-solid fa-gauge-high"></i> Speed Simulator</a>
        </nav>
        <nav class="footer-nav-panel" aria-label="Footer security and AI tools">
            <h3>Security + AI</h3>
            <a href="/free-penetration-testing-tools"><i class="fa-solid fa-user-secret"></i> Security Tools</a>
            <a href="/security-score-badge-generator"><i class="fa-solid fa-certificate"></i> Security Badge</a>
            <a href="/ai-automation-finder"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Automation Finder</a>
            <a href="/client-portal-preview"><i class="fa-solid fa-table-columns"></i> Client Portal Preview</a>
            <a href="/support"><i class="fa-solid fa-headset"></i> Ticket Support</a>
            <a href="/forum"><i class="fa-solid fa-comments"></i> Tech Forum</a>
            <a href="/blog"><i class="fa-solid fa-newspaper"></i> Blog</a>
        </nav>
        <div class="footer-contact-panel">
            <h3>Contact</h3>
            <p><i class="fa-solid fa-envelope"></i><a href="mailto:<?= e($footerContact['email']) ?>"><?= e($footerContact['email']) ?></a></p>
            <p><i class="fa-brands fa-whatsapp"></i><a href="<?= e($footerContact['whatsapp_url']) ?>">WhatsApp <?= e($footerContact['phone_display']) ?></a></p>
            <p><i class="fa-solid fa-ticket"></i><a href="/support">Support Ticket System</a></p>
            <?php foreach ($footerContact['locations'] as $location): ?>
                <p><i class="fa-solid fa-location-dot"></i><?= e($location['name']) ?></p>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="footer-statusbar">
        <span>&copy; <?= date('Y') ?> Crest Web Media</span>
        <a href="/privacy-policy">Privacy</a>
        <a href="/terms-and-conditions">Terms</a>
        <span>Remote studio. Global delivery. Written-first support.</span>
    </section>
</footer>

<section class="ai-chatbot" data-chatbot data-chat-endpoint="/chatbot/lead">
    <button class="chatbot-toggle" type="button" aria-label="Open AI service assistant">
        <i class="fa-solid fa-message"></i>
    </button>
    <div class="chatbot-panel" hidden>
        <div class="chatbot-head">
            <div>
                <span>Offline Service Assistant</span>
                <strong>Crest Service Guide</strong>
            </div>
            <button type="button" aria-label="Close chat"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="chatbot-messages" aria-live="polite"></div>
        <form class="chatbot-form">
            <input name="message" placeholder="Ask about services, prices, SEO, AI, apps..." autocomplete="off">
            <button type="submit" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
</section>

<a class="back-top" href="#" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></a>

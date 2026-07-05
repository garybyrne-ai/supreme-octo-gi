<?php
$headerRepository = new \App\Models\ContentRepository();
$headerServices = array_slice($headerRepository->services(), 0, 8);
$headerContact = $headerRepository->contact();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isServicesPath = str_starts_with($currentPath, '/services');
$isShopPath = str_starts_with($currentPath, '/code-shop');
$isStorePath = str_starts_with($currentPath, '/code-shop')
    || str_starts_with($currentPath, '/membership')
    || str_starts_with($currentPath, '/backlinks')
    || str_starts_with($currentPath, '/website-care-plans')
    || str_starts_with($currentPath, '/website-audit')
    || str_starts_with($currentPath, '/speed-rescue')
    || $currentPath === '/tools-pricing';
$isCompanyPath = in_array($currentPath, ['/about', '/portfolio', '/process', '/blog', '/support', '/contact'], true)
    || str_starts_with($currentPath, '/blog/');
$isToolsPath = in_array($currentPath, [
    '/tools',
    '/seo-tools',
    '/serp-checker',
    '/ai-content-assistant',
    '/ethical-hacking-toolkit',
    '/ethical-hacking-tools',
    '/free-penetration-testing-tools',
    '/ai-website-growth-consultant',
    '/instant-website-quote-calculator',
    '/security-score-badge-generator',
    '/client-portal-preview',
    '/ppc-roi-calculator',
    '/before-after-speed-simulator',
    '/ai-automation-finder',
], true);
?>
<header class="site-header">
    <a class="brand" href="/" aria-label="Crest Web Media home">
        <span class="brand-logo-wrap">
            <img class="brand-logo" src="<?= asset('images/crest-web-media-logo.webp') ?>" alt="Crest Web Media" width="170" height="60" fetchpriority="high">
            <span class="brand-comet" aria-hidden="true"></span>
        </span>
    </a>
    <nav class="nav" id="siteNav">
        <a class="<?= active_path('/') ?>" href="/">Home</a>
        <a class="<?= active_path('/about') ?>" href="/about">About</a>
        <div class="nav-item has-mega">
            <a class="<?= active_path('/services') ?>" href="/services" aria-haspopup="true">Services <i class="fa-solid fa-chevron-down"></i></a>
            <div class="mega-menu" aria-label="Services menu">
                <div class="mega-intro">
                    <span>Build Stack</span>
                    <strong>Websites, apps, APIs and AI workflows built as one operating layer.</strong>
                    <p>Choose a focused capability or combine services into a complete growth platform.</p>
                </div>
                <div class="mega-grid">
                    <?php foreach ($headerServices as $service): ?>
                        <a href="/services/<?= e($service['slug']) ?>">
                            <i class="<?= e($service['icon']) ?>"></i>
                            <span><?= e($service['title']) ?><small><?= e($service['summary']) ?></small></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="nav-item has-mega tools-mega">
            <a class="<?= $isToolsPath ? 'is-active' : '' ?>" href="/tools" aria-haspopup="true">Tools <i class="fa-solid fa-chevron-down"></i></a>
            <div class="mega-menu tools-menu" aria-label="Tools menu">
                <div class="mega-intro">
                    <span>Growth Lab</span>
                    <strong>Diagnostics that turn curiosity into qualified project context.</strong>
                    <p>SEO, SERP, PPC, AI, speed, security and client portal previews in one tool suite.</p>
                </div>
                <div class="mega-grid">
                    <a href="/ai-website-growth-consultant"><i class="fa-solid fa-brain"></i><span>AI Growth Consultant<small>Map practical growth opportunities.</small></span></a>
                    <a href="/instant-website-quote-calculator"><i class="fa-solid fa-calculator"></i><span>Quote Calculator<small>Estimate project scope and priority.</small></span></a>
                    <a href="/seo-tools"><i class="fa-solid fa-chart-line"></i><span>SEO Audit<small>Review page-level search signals.</small></span></a>
                    <a href="/serp-checker"><i class="fa-solid fa-ranking-star"></i><span>SERP Checker<small>Compare ranking opportunities.</small></span></a>
                    <a href="/ai-content-assistant"><i class="fa-solid fa-wand-magic-sparkles"></i><span>AI Content Assistant<small>Generate SEO copy in seconds.</small></span></a>
                    <a href="/ppc-roi-calculator"><i class="fa-solid fa-bullseye"></i><span>PPC ROI<small>Model paid-search return.</small></span></a>
                    <a href="/ethical-hacking-toolkit"><i class="fa-solid fa-shield-halved"></i><span>Ethical Hacking Toolkit<small>Scan for exposed files &amp; harden.</small></span></a>
                    <a href="/ethical-hacking-tools"><i class="fa-solid fa-toolbox"></i><span>Hacking Tools Library<small>Pro: download tools &amp; guides.</small></span></a>
                    <a href="/free-penetration-testing-tools"><i class="fa-solid fa-user-secret"></i><span>Security Tools<small>Check headers, DNS and TLS.</small></span></a>
                    <a href="/ai-automation-finder"><i class="fa-solid fa-wand-magic-sparkles"></i><span>AI Automation Finder<small>Find workflow automation candidates.</small></span></a>
                    <a href="/client-portal-preview"><i class="fa-solid fa-table-columns"></i><span>Client Portal<small>Preview reporting and support UX.</small></span></a>
                    <a href="/tools-pricing"><i class="fa-solid fa-crown"></i><span>Growth Lab Pro<small>Unlimited scans &amp; white-label reports.</small></span></a>
                </div>
            </div>
        </div>
        <div class="nav-item has-mega">
            <a class="<?= $isStorePath ? 'is-active' : '' ?>" href="/code-shop" aria-haspopup="true">Store <i class="fa-solid fa-chevron-down"></i></a>
            <div class="mega-menu" aria-label="Store menu">
                <div class="mega-intro">
                    <span>Buy &amp; Grow</span>
                    <strong>Themes, plugins, memberships and Irish link building.</strong>
                    <p>Everything you can buy from Crest Web Media in one place.</p>
                </div>
                <div class="mega-grid">
                    <a href="/code-shop"><i class="fa-solid fa-cart-shopping"></i><span>Marketplace<small>Themes, plugins, templates &amp; services.</small></span></a>
                    <a href="/membership"><i class="fa-solid fa-id-card"></i><span>Membership<small>Growth Lab plans for tools &amp; downloads.</small></span></a>
                    <a href="/backlinks"><i class="fa-solid fa-link"></i><span>Buy Irish Backlinks<small>Rare .ie links — 25 from €59.</small></span></a>
                    <a href="/tools-pricing"><i class="fa-solid fa-crown"></i><span>Growth Lab Pro<small>€25/mo or €200/yr tools membership.</small></span></a>
                    <a href="/website-care-plans"><i class="fa-solid fa-heart-pulse"></i><span>Website Care Plans<small>Maintenance from €49/month.</small></span></a>
                    <a href="/website-audit"><i class="fa-solid fa-file-shield"></i><span>Website Audit — €49<small>One-off white-label PDF report.</small></span></a>
                    <a href="/speed-rescue"><i class="fa-solid fa-bolt"></i><span>Speed Rescue — €149<small>Under 2s load or you don't pay.</small></span></a>
                </div>
            </div>
        </div>
        <div class="nav-item has-mega">
            <a class="<?= $isCompanyPath ? 'is-active' : '' ?>" href="/about" aria-haspopup="true">Company <i class="fa-solid fa-chevron-down"></i></a>
            <div class="mega-menu" aria-label="Company menu">
                <div class="mega-intro">
                    <span>Crest Web Media</span>
                    <strong>Who we are and how we work.</strong>
                    <p>Learn about the studio, see proof, and get in touch.</p>
                </div>
                <div class="mega-grid">
                    <a href="/about"><i class="fa-solid fa-circle-info"></i><span>About<small>Studio, approach and values.</small></span></a>
                    <a href="/portfolio"><i class="fa-solid fa-briefcase"></i><span>Portfolio<small>Selected work &amp; case studies.</small></span></a>
                    <a href="/process"><i class="fa-solid fa-diagram-project"></i><span>Process<small>How projects are delivered.</small></span></a>
                    <a href="/blog"><i class="fa-solid fa-newspaper"></i><span>Blog<small>Guides on SEO, security &amp; web.</small></span></a>
                    <a href="/support"><i class="fa-solid fa-headset"></i><span>Support<small>Open a ticket, get help.</small></span></a>
                    <a href="/contact"><i class="fa-solid fa-paper-plane"></i><span>Contact<small>Start a project brief.</small></span></a>
                </div>
            </div>
        </div>
        <?php if (isset($_SESSION) && !empty($_SESSION['member'])): ?>
            <a class="<?= active_path('/account/dashboard') ?>" href="/account/dashboard">Dashboard</a>
        <?php endif; ?>
        <button class="mobile-nav-account-link" type="button" data-mobile-account-trigger>
            <span>Login / Register</span><i class="fa-solid fa-user-shield"></i>
        </button>
    </nav>
    <div class="header-actions">
        <div class="account-widget" data-account-widget data-forms-endpoint="/account/forms">
            <button class="icon-button account-trigger" type="button" aria-label="Login or register" aria-expanded="false">
                <i class="fa-solid fa-user-shield"></i>
            </button>
            <div class="account-popover" hidden>
                <div class="account-tabs" role="tablist" aria-label="Account forms">
                    <button class="is-active" type="button" data-account-tab="login">Login</button>
                    <button type="button" data-account-tab="register">Register</button>
                </div>
                <form class="account-form is-active" method="post" action="/account/login" data-account-form="login">
                    <input type="hidden" name="_csrf" value="">
                    <h2>Client Login</h2>
                    <label>Email <input name="email" type="email" autocomplete="email" required></label>
                    <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
                    <label class="captcha-field">PHP Captcha
                        <span class="captcha-box" data-captcha="login">Open form to load</span>
                        <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Answer">
                    </label>
                    <button class="pill-button" type="submit">Sign In <i class="fa-solid fa-arrow-right-to-bracket"></i></button>
                </form>
                <form class="account-form" method="post" action="/account/register" data-account-form="register">
                    <input type="hidden" name="_csrf" value="">
                    <h2>Create Access</h2>
                    <label>Name <input name="name" autocomplete="name" required></label>
                    <label>Email <input name="email" type="email" autocomplete="email" required></label>
                    <label>Password <input name="password" type="password" autocomplete="new-password" minlength="10" required></label>
                    <label class="captcha-field">PHP Captcha
                        <span class="captcha-box" data-captcha="register">Open form to load</span>
                        <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Answer">
                    </label>
                    <button class="pill-button" type="submit">Register <i class="fa-solid fa-user-plus"></i></button>
                </form>
                <p class="account-message" role="status" aria-live="polite"></p>
            </div>
        </div>
        <a class="whatsapp-button" href="<?= e($headerContact['whatsapp_url']) ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span>
        </a>
        <a class="pill-button ghost" href="/contact">Send Project Brief <i class="fa-solid fa-paper-plane"></i></a>
        <button class="icon-button nav-toggle" type="button" aria-label="Toggle menu" aria-controls="siteNav" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>
<nav class="mobile-app-dock" aria-label="Mobile app navigation">
    <a class="<?= active_path('/') ?>" href="/">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>
    <a class="<?= $isServicesPath ? 'is-active' : '' ?>" href="/services">
        <i class="fa-solid fa-layer-group"></i>
        <span>Services</span>
    </a>
    <a class="<?= $isToolsPath ? 'is-active' : '' ?>" href="/tools">
        <i class="fa-solid fa-chart-line"></i>
        <span>Tools</span>
    </a>
    <a class="<?= active_path('/portfolio') ?>" href="/portfolio">
        <i class="fa-solid fa-briefcase"></i>
        <span>Work</span>
    </a>
    <a class="<?= active_path('/support') ?>" href="/support">
        <i class="fa-solid fa-headset"></i>
        <span>Support</span>
    </a>
    <button class="mobile-account-trigger" type="button" data-mobile-account-trigger aria-label="Open login and register forms">
        <i class="fa-solid fa-user-shield"></i>
        <span>Login</span>
    </button>
</nav>

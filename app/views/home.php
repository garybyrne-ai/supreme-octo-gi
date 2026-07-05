<section class="hero section-bleed" data-hero>
    <div class="hero-backdrop" aria-hidden="true">
        <span class="hero-orb orb-a"></span>
        <span class="hero-orb orb-b"></span>
        <span class="hero-orb orb-c"></span>
        <span class="hero-gridlines"></span>
        <span class="hero-spotlight"></span>
    </div>
    <div class="vertical-social">
        <span>Let's connect</span>
        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        <a href="#"><i class="fa-brands fa-github"></i></a>
        <a href="#"><i class="fa-brands fa-telegram"></i></a>
        <a href="#"><i class="fa-solid fa-envelope"></i></a>
    </div>
    <?php
        $hero = $hero ?? [];
        $servingList = !empty($hero['serving']) && is_array($hero['serving'])
            ? $hero['serving']
            : [['flag' => '🇮🇪', 'label' => 'Ireland'], ['flag' => '🇬🇧', 'label' => 'UK'], ['flag' => '🇺🇸', 'label' => 'USA'], ['flag' => '🇪🇺', 'label' => 'Europe'], ['flag' => '🌍', 'label' => 'And Beyond']];
    ?>
    <div class="hero-copy">
        <div class="status-chip"><span></span> <?= e($hero['chip_text'] ?? 'Remote. Precise. Built For Growth.') ?> <i class="fa-solid fa-mountain"></i></div>
        <h1><?= strip_tags((string) ($hero['headline'] ?? 'We Build Digital Systems That Work. <span>Scale.</span> And <span>Make Money.</span>'), '<span>') ?></h1>
        <p><?= e($hero['subheading'] ?? 'Websites, PHP platforms, ecommerce, apps, SEO and AI workflows engineered for fast loading, clearer decisions and qualified demand.') ?></p>
        <div class="hero-service-grid" aria-label="Core services">
            <a href="/services/website-development"><i class="fa-solid fa-laptop-code"></i><span>Web Platforms</span></a>
            <a href="/services/app-development"><i class="fa-solid fa-mobile-screen-button"></i><span>Apps &amp; Portals</span></a>
            <a href="/services/seo"><i class="fa-solid fa-magnifying-glass-chart"></i><span>SEO Systems</span></a>
            <a href="/services/performance-optimization"><i class="fa-solid fa-chart-line"></i><span>Conversion UX</span></a>
        </div>
        <div class="button-row">
            <a class="pill-button" href="<?= e($hero['cta_primary_url'] ?? '/portfolio') ?>"><?= e($hero['cta_primary_label'] ?? 'View My Work') ?> <i class="fa-solid fa-arrow-right"></i></a>
            <a class="pill-button ghost" href="<?= e($hero['cta_secondary_url'] ?? '/free-penetration-testing-tools') ?>"><?= e($hero['cta_secondary_label'] ?? 'Free Security Tools') ?> <i class="fa-solid fa-shield-halved"></i></a>
        </div>
        <div class="hero-trust-strip" aria-label="Serving clients worldwide">
            <span><?= e($hero['serving_label'] ?? 'Serving Clients In') ?></span>
            <?php foreach ($servingList as $svc): ?>
            <b><?php if (!empty($svc['flag'])): ?><span class="flag" aria-hidden="true"><?= e($svc['flag']) ?></span> <?php endif; ?><?= e($svc['label'] ?? '') ?></b>
            <?php endforeach; ?>
        </div>
        <?php
            $tickerItems = !empty($hero['ticker']) && is_array($hero['ticker']) ? $hero['ticker'] : [
                '99.9% uptime architecture',
                'Core Web Vitals: green',
                'A+ security headers',
                'GDPR-ready builds',
                '300+ projects delivered',
                '24/7 monitoring & support',
            ];
        ?>
        <div class="hero-ticker" aria-hidden="true">
            <div class="hero-ticker-track">
                <?php for ($pass = 0; $pass < 2; $pass++): foreach ($tickerItems as $tick): ?>
                    <span><i class="fa-solid fa-circle-check"></i> <?= e($tick) ?></span>
                <?php endforeach; endfor; ?>
            </div>
        </div>
    </div>
    <?php
        $heroContact = $contact ?? [];
        $signalEmail = $heroContact['email'] ?? 'ank.kalia@gmail.com';
        $signalLine = $heroContact['signal_line'] ?? 'Dublin + Shimla';
    ?>
    <div class="hero-panels">
        <a class="email-neon-sign" data-tilt href="mailto:<?= e($signalEmail) ?>" aria-label="Email <?= e($signalEmail) ?>">
            <span class="signal-label"><b class="live-dot"></b> DIRECT SIGNAL</span>
            <strong><i class="fa-solid fa-circle-check"></i> <?= e($signalEmail) ?></strong>
            <small><i class="fa-solid fa-location-dot"></i> <?= e($signalLine) ?></small>
            <span class="signal-scan" aria-hidden="true"></span>
            <b class="hud-corners" aria-hidden="true"></b>
            <span class="tilt-glare" aria-hidden="true"></span>
        </a>
        <div class="live-card" data-tilt>
            <span>LOCAL TIME</span>
            <strong id="localTime">10:30 AM</strong>
            <em><b class="live-dot online"></b> Online &amp; Available</em>
            <div class="mountain-hike" aria-hidden="true">
                <span class="sky-sun"></span>
                <span class="mountain-back"></span>
                <span class="mountain-front"></span>
                <span class="hiker"><b class="hiker-pole"></b></span>
            </div>
            <b class="hud-corners" aria-hidden="true"></b>
            <span class="tilt-glare" aria-hidden="true"></span>
        </div>
    </div>
    <a class="hero-scroll-cue" href="#services" aria-label="Scroll to services">
        <span class="cue-mouse"><b></b></span>
        <i class="fa-solid fa-chevron-down"></i>
    </a>
</section>

<section class="section reveal priority-services" id="services">
    <div class="section-heading">
        <span class="kicker">Core Capabilities</span>
        <h2>Digital Systems Built For Revenue, Search And Scale</h2>
    </div>
    <div class="service-grid">
        <?php foreach ($services as $service): ?>
            <a class="cyber-card service-card" href="/services/<?= e($service['slug']) ?>">
                <i class="<?= e($service['icon']) ?>"></i>
                <?= service_visuals($service['visuals'] ?? [], 'service-logo-strip compact icon-only', 3) ?>
                <h3><?= e($service['title']) ?></h3>
                <p><?= e($service['summary']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="himalaya-band reveal">
    <div class="himalaya-band-copy">
        <span class="kicker">Delivery Standard</span>
        <h2>Built With Calm Focus, Shipped With Electric <span>Precision.</span></h2>
        <p class="himalaya-band-lead">Senior design, lean engineering and practical growth thinking for businesses that need the website to carry real commercial weight.</p>
    </div>
    <div class="himalaya-proof-grid">
        <article>
            <em>01</em>
            <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M4 36h40L31 14l-7 12-5-7L4 36Z"/><path d="M24 26l7-12 4 7-4-2-4 7h-3Z"/></svg>
            <strong>Deep Work</strong>
            <span>Clear architecture, careful QA and focused execution.</span>
        </article>
        <article>
            <em>02</em>
            <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M9 10h30v22H9z"/><path d="M15 38h18M20 32l-2 6M28 32l2 6"/></svg>
            <strong>Conversion Systems</strong>
            <span>Interfaces, SEO and PPC paths shaped around qualified leads.</span>
        </article>
        <article>
            <em>03</em>
            <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M24 5l16 7v10c0 10-6.8 17.4-16 21-9.2-3.6-16-11-16-21V12l16-7Z"/><path d="M17 24l5 5 10-12"/></svg>
            <strong>Secure Delivery</strong>
            <span>Performance, support and hardening designed into the release.</span>
        </article>
    </div>
    <div class="himalaya-band-note">
        <i class="fa-solid fa-globe"></i>
        <p>From Shimla in the Himalayan foothills to clients in Ireland, the UK, the USA and Europe, <strong>Crest Web Media</strong> blends patient craft, clean engineering and sharp commercial thinking.</p>
        <span><i class="fa-solid fa-location-dot"></i> Shimla, India</span>
    </div>
</section>

<section class="stat-grid reveal">
    <?php foreach ($stats as $stat): ?>
        <?php
            $rawValue = $stat['value'];
            $counterValue = '';
            $counterSuffix = '';
            if (preg_match('/^(\d+(?:\.\d+)?)([+%]?)$/', $rawValue, $matches)) {
                $counterValue = $matches[1];
                $counterSuffix = $matches[2];
            }
            $counterAttributes = $counterValue !== ''
                ? ' data-count="' . e($counterValue) . '" data-suffix="' . e($counterSuffix) . '"'
                : '';
        ?>
        <article class="metric-card">
            <i class="fa-solid <?= e($stat['icon']) ?>"></i>
            <strong<?= $counterAttributes ?>><?= e($stat['value']) ?></strong>
            <span><?= e($stat['label']) ?></span>
        </article>
    <?php endforeach; ?>
</section>

<section class="section reveal">
    <div class="section-heading">
        <h2>Why Teams Choose Crest Web Media</h2>
    </div>
    <div class="why-strip">
        <?php foreach ($why as $item): ?>
            <article>
                <i class="fa-solid <?= e($item['icon']) ?>"></i>
                <h3><?= e($item['title']) ?></h3>
                <p><?= e($item['body']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading">
        <h2>Technology Stack And Operating Tools</h2>
    </div>
    <div class="tech-marquee">
        <?php foreach ($technologies as $tech): ?>
            <span class="tech-chip"><img src="<?= asset('images/' . $tech['image']) ?>" alt="<?= e($tech['name']) ?> logo" loading="lazy"><?= e($tech['name']) ?></span>
        <?php endforeach; ?>
    </div>
</section>

<section class="triple-panel feature-stack reveal">
    <article class="cyber-card feature-panel security-panel">
        <div class="feature-head">
            <span class="feature-hero-icon shield-icon" aria-hidden="true">
                <svg viewBox="0 0 64 64"><path d="M32 5 54 14v15c0 15-9 25-22 30C19 54 10 44 10 29V14l22-9Z"/><path d="M23 30h18v16H23z"/><path d="M26 30v-5a6 6 0 0 1 12 0v5"/></svg>
            </span>
            <h3>Penetration Testing</h3>
        </div>
        <p>Responsible reviews for web apps, APIs, admin panels and hosting layers using Kali Linux, FreeBSD, Parrot OS, BlackArch, Ubuntu and OWASP methods.</p>
        <div class="tool-badges os-badges" aria-label="Security operating systems and standards">
            <span><img src="<?= asset('images/tech/kalilinux.png') ?>" alt="Kali Linux logo" loading="lazy">Kali Linux</span>
            <span><img src="<?= asset('images/tech/freebsd.png') ?>" alt="FreeBSD logo" loading="lazy">FreeBSD</span>
            <span><img src="<?= asset('images/tech/parrotos.svg') ?>" alt="Parrot OS logo" loading="lazy">Parrot OS</span>
            <span><img src="<?= asset('images/tech/blackarch.svg') ?>" alt="BlackArch logo" loading="lazy">BlackArch</span>
            <span><i class="fa-brands fa-ubuntu"></i>Ubuntu</span>
            <span><img src="<?= asset('images/tech/owasp.svg') ?>" alt="OWASP logo" loading="lazy">OWASP</span>
        </div>
        <a class="small-link feature-link" href="/services/penetration-testing">Learn More <i class="fa-solid fa-arrow-right"></i></a>
    </article>
    <article class="cyber-card feature-panel ai-panel">
        <div class="feature-head">
            <span class="feature-hero-icon ai-chip" aria-hidden="true">AI</span>
            <h3>AI Workflow Systems</h3>
        </div>
        <p>AI is used where it improves delivery, routing, summaries, CRM updates and operating speed without removing human judgment.</p>
        <ul class="feature-list">
            <li><span><i class="fa-solid fa-bolt"></i></span><strong>Faster delivery</strong><small>Research, briefs and QA loops move with less drag.</small></li>
            <li><span><i class="fa-solid fa-brain"></i></span><strong>Sharper decisions</strong><small>AI assists analysis while people own the judgment.</small></li>
            <li><span><i class="fa-solid fa-code"></i></span><strong>Cleaner systems</strong><small>Code stays structured, documented and maintainable.</small></li>
            <li><span><i class="fa-solid fa-rocket"></i></span><strong>Workflow lift</strong><small>Lead routing, CRM updates and support summaries get faster.</small></li>
        </ul>
    </article>
    <article class="cyber-card feature-panel performance-panel">
        <div class="feature-head">
            <span class="feature-hero-icon gauge-icon" aria-hidden="true">
                <svg viewBox="0 0 64 64"><path d="M10 44a22 22 0 1 1 44 0"/><path d="M16 44h8M40 44h8M18 30l6 4M46 30l-6 4M32 22v8"/><path d="M32 44l13-18"/></svg>
            </span>
            <h3>Performance First</h3>
        </div>
        <p>Every build is shaped around fast paint, stable layouts, responsive interaction and measurable Core Web Vitals.</p>
        <div class="score-row">
            <span>100<small>Performance</small></span>
            <span>100<small>Accessibility</small></span>
            <span>100<small>Best Practices</small></span>
            <span>100<small>SEO</small></span>
        </div>
        <a class="small-link feature-link" href="/services/performance-optimization"><i class="fa-solid fa-chart-line"></i> View Full Report <i class="fa-solid fa-arrow-right"></i></a>
    </article>
</section>

<section class="section process-section reveal">
    <div class="section-heading">
        <h2>How The Work Moves From Brief To Launch</h2>
    </div>
    <div class="process-grid">
        <?php foreach ($process as $item): ?>
            <article class="process-node">
                <div class="process-node-top">
                    <span><?= e($item['step']) ?></span>
                    <i class="fa-solid <?= e($item['icon']) ?>"></i>
                </div>
                <div class="process-node-body">
                    <h3><?= e($item['title']) ?></h3>
                    <p><?= e($item['body']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="panel-head">
        <h2>Some Of Our Latest Work</h2>
        <div class="filters"><button>All</button><button>Websites</button><button>Web Apps</button><button>Mobile Apps</button><button>E-Commerce</button></div>
    </div>
    <div class="portfolio-grid">
        <?php foreach ($portfolio as $index => $item): ?>
            <article class="portfolio-card accent-<?= e($item['accent']) ?>">
                <div class="portfolio-art <?= !empty($item['image']) ? 'portfolio-shot' : 'art-' . ($index + 1) ?>"<?= !empty($item['image']) ? ' style="--portfolio-image: url(\'' . e(asset('images/portfolio/' . $item['image'])) . '\')"' : '' ?>></div>
                <h3><?= e($item['title']) ?></h3>
                <p><?= e($item['summary']) ?></p>
                <span><i class="fa-solid fa-window-maximize"></i><?= e($item['category']) ?></span>
                <?php if (!empty($item['url'])): ?>
                    <a class="small-link portfolio-live-link" href="<?= e($item['url']) ?>" target="_blank" rel="noopener">
                        Visit site <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading left"><h2>What Clients Say</h2></div>
    <div class="testimonial-grid">
        <?php foreach ($testimonials as $testimonial): ?>
            <article class="testimonial-card">
                <p><?= e($testimonial['quote']) ?></p>
                <div><span class="avatar"><?= e(substr($testimonial['name'], 0, 1)) ?></span><strong><?= e($testimonial['name']) ?><small><?= e($testimonial['role']) ?> - <?= e($testimonial['flag']) ?></small></strong></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading"><h2>Latest Insights</h2></div>
    <div class="blog-grid compact">
        <?php foreach ($posts as $post): ?>
            <article class="cyber-card">
                <span class="kicker"><?= e($post['category']) ?></span>
                <h3><a href="/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
                <p><?= e($post['excerpt']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="home-tools-cta code-shop-home-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker">Digital Code Shop</span>
        <h2>Launch a camera-first ordering product without starting from zero.</h2>
        <p>Buy the Photo To Key PHP package: a complete webapp with browser camera upload, backend order flow, VAT and shipping fields, payment hooks and a Core Web Vitals-ready frontend.</p>
        <div class="button-row">
            <a class="pill-button" href="/code-shop/photo-to-key-php-website-backend">View Product <i class="fa-solid fa-arrow-right"></i></a>
            <a class="pill-button ghost" href="/code-shop">Open Code Shop <i class="fa-solid fa-cart-shopping"></i></a>
        </div>
    </div>
    <div class="home-tools-orbit code-shop-orbit" aria-hidden="true">
        <span><i class="fa-solid fa-camera"></i></span>
        <span><i class="fa-solid fa-key"></i></span>
        <span><i class="fa-solid fa-credit-card"></i></span>
        <span><i class="fa-solid fa-truck"></i></span>
        <strong>CODE</strong>
    </div>
</section>

<section class="home-tools-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker">Growth Lab</span>
        <h2>Use the tools when you want evidence before a project brief.</h2>
        <p>Audit SEO, check SERP opportunities, model PPC ROI, find AI automations, simulate speed gains and review security signals from one polished tools hub.</p>
        <div class="button-row">
            <a class="pill-button" href="/tools">Open Tools Hub <i class="fa-solid fa-arrow-right"></i></a>
            <a class="pill-button ghost" href="/contact">Send Project Brief <i class="fa-solid fa-paper-plane"></i></a>
        </div>
    </div>
    <div class="home-tools-orbit" aria-hidden="true">
        <span><i class="fa-solid fa-chart-line"></i></span>
        <span><i class="fa-solid fa-ranking-star"></i></span>
        <span><i class="fa-solid fa-shield-halved"></i></span>
        <span><i class="fa-solid fa-brain"></i></span>
        <strong>TOOLS</strong>
    </div>
</section>

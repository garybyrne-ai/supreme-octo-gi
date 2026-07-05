<section class="subhero tools-hero">
    <span class="status-chip"><span></span> Free Defensive Tools</span>
    <h1>Penetration Testing Tools for Safer Websites</h1>
    <p>Run quick defensive checks before launch: headers, DNS/email security, TLS certificates, security.txt, passwords, hashes, JWT decoding and URL/base64 utilities. Use only on systems you own or are authorized to test.</p>
    <div class="button-row">
        <a class="pill-button" href="#server-checks">Run Checks <i class="fa-solid fa-shield-halved"></i></a>
        <a class="pill-button ghost" href="#local-tools">Local Utilities <i class="fa-solid fa-terminal"></i></a>
        <a class="pill-button ghost" href="/support">Open Support Ticket <i class="fa-solid fa-headset"></i></a>
    </div>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="tool-grid reveal tool-summary">
    <article class="cyber-card"><i class="fa-solid fa-lock"></i><h2>Headers</h2><p>Score browser security headers and HTTP posture.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-envelope-circle-check"></i><h2>DNS + Email</h2><p>Check MX, SPF, DMARC, CAA and nameservers.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-certificate"></i><h2>TLS</h2><p>Read certificate issuer, subject and expiry health.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-file-shield"></i><h2>Well-Known</h2><p>Discover security.txt and robots.txt exposure.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-key"></i><h2>Password Lab</h2><p>Test passphrase strength locally in the browser.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-code"></i><h2>Token Utilities</h2><p>Decode JWT, hash text and encode URLs locally.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-diagram-project"></i><h2>CSP Builder</h2><p>Generate a hardened Content Security Policy starter.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-fingerprint"></i><h2>SRI Hash</h2><p>Create SHA-384 integrity hashes for scripts and styles.</p></article>
    <article class="cyber-card"><i class="fa-solid fa-cookie-bite"></i><h2>Cookie Audit</h2><p>Review Set-Cookie flags for session safety.</p></article>
</section>

<section class="section reveal" id="server-checks">
    <div class="section-heading">
        <span class="kicker">Server-side scanners</span>
        <h2>Each scanner has its own page &amp; premium report</h2>
        <p>Open a dedicated tool, run a live scan and download a white-label PDF report.</p>
    </div>
    <div class="local-service-grid">
        <a class="local-service-card" href="/tools/security-headers">
            <i class="fa-solid fa-lock"></i>
            <h3>Security Headers</h3>
            <p>Score HSTS, CSP, COOP, CORP, frame &amp; MIME protection, cookie flags and stack disclosure.</p>
            <span class="local-service-cta">Open scanner <i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a class="local-service-card" href="/tools/dns-email">
            <i class="fa-solid fa-envelope-circle-check"></i>
            <h3>DNS &amp; Email Security</h3>
            <p>Check MX, SPF, DMARC, CAA and nameservers for deliverability and anti-spoofing.</p>
            <span class="local-service-cta">Open scanner <i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a class="local-service-card" href="/tools/tls-ssl">
            <i class="fa-solid fa-certificate"></i>
            <h3>TLS / SSL Certificate</h3>
            <p>Read certificate issuer, subject, validity window and days to expiry.</p>
            <span class="local-service-cta">Open scanner <i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a class="local-service-card" href="/tools/security-txt">
            <i class="fa-solid fa-file-shield"></i>
            <h3>security.txt &amp; Discovery</h3>
            <p>Discover responsible-disclosure and crawler files exposed over HTTPS.</p>
            <span class="local-service-cta">Open scanner <i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a class="local-service-card" href="/tools/tech-stack">
            <i class="fa-solid fa-microchip"></i>
            <h3>Technology Stack</h3>
            <p>Detect the CMS, frameworks, libraries, analytics, CDN and server behind a site.</p>
            <span class="local-service-cta">Open scanner <i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a class="local-service-card" href="/seo-tools">
            <i class="fa-solid fa-chart-line"></i>
            <h3>On-Page SEO Audit</h3>
            <p>Titles, meta, headings, schema, Open Graph, keyword density and indexability.</p>
            <span class="local-service-cta">Open scanner <i class="fa-solid fa-arrow-right"></i></span>
        </a>
    </div>
</section>

<section class="tool-workbench reveal" id="local-tools" data-tools-locked="<?= empty($toolLead) ? 'true' : 'false' ?>">
    <div class="cyber-form glass-tool" data-password-tool>
        <h2>Password Strength Lab</h2>
        <label>Test Password
            <input type="password" autocomplete="new-password" placeholder="Type locally only">
        </label>
        <div class="strength-meter"><span></span></div>
        <p class="tool-output">Strength score will appear here.</p>
    </div>

    <div class="cyber-form glass-tool" data-hash-tool>
        <h2>SHA-256 Hash Generator</h2>
        <label>Input Text
            <textarea rows="5" placeholder="Paste text to hash locally"></textarea>
        </label>
        <button class="pill-button ghost" type="button">Generate Hash <i class="fa-solid fa-fingerprint"></i></button>
        <output class="hash-output">Hash output</output>
    </div>

    <div class="cyber-form glass-tool" data-jwt-tool>
        <h2>JWT Decoder</h2>
        <label>Token
            <textarea rows="5" placeholder="Paste a JWT to decode locally"></textarea>
        </label>
        <button class="pill-button ghost" type="button">Decode Token <i class="fa-solid fa-unlock-keyhole"></i></button>
        <output class="hash-output">Decoded header and payload</output>
    </div>

    <div class="cyber-form glass-tool" data-codec-tool>
        <h2>URL + Base64 Utility</h2>
        <label>Input
            <textarea rows="5" placeholder="Paste text to encode or decode locally"></textarea>
        </label>
        <div class="tool-button-row">
            <button class="pill-button ghost" type="button" data-codec="url-encode">URL Encode</button>
            <button class="pill-button ghost" type="button" data-codec="url-decode">URL Decode</button>
            <button class="pill-button ghost" type="button" data-codec="base64-encode">Base64 Encode</button>
            <button class="pill-button ghost" type="button" data-codec="base64-decode">Base64 Decode</button>
        </div>
        <output class="hash-output">Utility output</output>
    </div>

    <div class="cyber-form glass-tool" data-csp-builder>
        <h2>CSP Policy Builder</h2>
        <label>Allowed Domains
            <input placeholder="self, cdn.example.com, analytics.example.com">
        </label>
        <div class="tool-button-row">
            <button class="pill-button ghost" type="button" data-csp-mode="strict">Strict</button>
            <button class="pill-button ghost" type="button" data-csp-mode="balanced">Balanced</button>
        </div>
        <output class="hash-output">Content-Security-Policy output</output>
    </div>

    <div class="cyber-form glass-tool" data-sri-tool>
        <h2>SRI Hash Generator</h2>
        <label>Script or CSS Content
            <textarea rows="5" placeholder="Paste the exact file content"></textarea>
        </label>
        <button class="pill-button ghost" type="button">Generate SHA-384 SRI <i class="fa-solid fa-fingerprint"></i></button>
        <output class="hash-output">sha384 integrity output</output>
    </div>

    <div class="cyber-form glass-tool" data-cookie-auditor>
        <h2>Cookie Flag Auditor</h2>
        <label>Set-Cookie Header
            <textarea rows="5" placeholder="session=abc; Path=/; HttpOnly; Secure; SameSite=Lax"></textarea>
        </label>
        <button class="pill-button ghost" type="button">Audit Cookie <i class="fa-solid fa-cookie-bite"></i></button>
        <output class="hash-output">Cookie audit output</output>
    </div>
</section>

<section class="home-tools-cta reveal">
    <div class="home-tools-cta-copy">
        <span class="kicker">Growth Lab Pro</span>
        <h2>Unlimited scans, white-label PDF reports and monitoring — from €25/month.</h2>
        <p>Run every scanner without the 3-per-day free limit, add your own agency branding and download client-ready PDF reports.</p>
        <div class="button-row">
            <a class="pill-button" href="/tools-pricing">See Pro Pricing <i class="fa-solid fa-arrow-right"></i></a>
            <a class="pill-button ghost" href="/tools">All Tools <i class="fa-solid fa-grip"></i></a>
        </div>
    </div>
</section>

<?php require base_path('app/views/partials/tool-pro-cta.php'); ?>

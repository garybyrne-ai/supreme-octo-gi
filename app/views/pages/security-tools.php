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

<section class="tool-workbench reveal" id="server-checks">
    <form class="cyber-form glass-tool" method="post" action="/free-penetration-testing-tools/headers">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Security Header Analyzer</h2>
        <p>Checks HSTS, CSP, frame protection, MIME sniffing, referrer and permissions policy.</p>
        <?php if (!empty($toolError)): ?><div class="notice error"><?= e($toolError) ?></div><?php endif; ?>
        <label>Website URL
            <input name="target_url" type="url" inputmode="url" placeholder="https://example.com" value="<?= e($targetUrl ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Run Header Check <i class="fa-solid fa-arrow-right"></i></button>
    </form>

    <form class="cyber-form glass-tool" method="post" action="/free-penetration-testing-tools/dns">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>DNS + Email Security</h2>
        <p>Checks public DNS records plus SPF, DMARC and CAA signals.</p>
        <?php if (!empty($dnsError)): ?><div class="notice error"><?= e($dnsError) ?></div><?php endif; ?>
        <label>Domain
            <input name="domain" inputmode="url" placeholder="example.com" value="<?= e($dnsHost ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Check DNS <i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <form class="cyber-form glass-tool" method="post" action="/free-penetration-testing-tools/tls">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>TLS Certificate Health</h2>
        <p>Reads the HTTPS certificate on port 443 and checks expiry timing.</p>
        <?php if (!empty($tlsError)): ?><div class="notice error"><?= e($tlsError) ?></div><?php endif; ?>
        <label>Domain
            <input name="domain" inputmode="url" placeholder="example.com" value="<?= e($tlsHost ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Check TLS <i class="fa-solid fa-certificate"></i></button>
    </form>

    <form class="cyber-form glass-tool" method="post" action="/free-penetration-testing-tools/well-known">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Security.txt + Robots</h2>
        <p>Checks common disclosure files over HTTPS without crawling the site.</p>
        <?php if (!empty($wellKnownError)): ?><div class="notice error"><?= e($wellKnownError) ?></div><?php endif; ?>
        <label>Domain
            <input name="domain" inputmode="url" placeholder="example.com" value="<?= e($wellKnownHost ?? '') ?>" required>
        </label>
        <button class="pill-button" type="submit">Discover Files <i class="fa-solid fa-file-shield"></i></button>
    </form>
</section>

<?php if (!empty($headerResult) || !empty($dnsResult) || !empty($tlsResult) || !empty($wellKnownResult)): ?>
    <section class="tool-results reveal">
        <?php if (!empty($headerResult)): ?>
            <article class="cyber-card tool-result-card">
                <span class="kicker"><?= e($headerResult['status']) ?></span>
                <h2><?= e((string) $headerResult['score']) ?>/100</h2>
                <p>Header readiness score for <?= e($targetUrl ?? '') ?>.</p>
                <div class="tool-check-list">
                    <?php foreach ($headerResult['checks'] as $check): ?>
                        <div class="<?= $check['present'] ? 'is-good' : 'is-missing' ?>"><i class="fa-solid <?= $check['present'] ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i><span><strong><?= e($check['label']) ?></strong><small><?= e(excerpt($check['value'], 110)) ?></small></span></div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!empty($dnsResult)): ?>
            <article class="cyber-card tool-result-card">
                <span class="kicker"><?= e($dnsHost ?? '') ?></span>
                <h2><?= e((string) $dnsResult['score']) ?>/100</h2>
                <p>DNS and email security readiness score.</p>
                <div class="tool-check-list">
                    <?php foreach ($dnsResult['checks'] as $check): ?>
                        <div class="<?= $check['present'] ? 'is-good' : 'is-missing' ?>"><i class="fa-solid <?= $check['present'] ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i><span><strong><?= e($check['label']) ?></strong><small><?= e(excerpt($check['value'], 130)) ?></small></span></div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!empty($tlsResult)): ?>
            <article class="cyber-card tool-result-card tls-result">
                <span class="kicker"><?= e($tlsHost ?? '') ?></span>
                <h2><?= e((string) $tlsResult['score']) ?>/100</h2>
                <p>TLS certificate health score.</p>
                <div class="tool-check-list">
                    <div class="is-good"><i class="fa-solid fa-certificate"></i><span><strong>Subject</strong><small><?= e($tlsResult['subject']) ?></small></span></div>
                    <div class="is-good"><i class="fa-solid fa-building-shield"></i><span><strong>Issuer</strong><small><?= e($tlsResult['issuer']) ?></small></span></div>
                    <div class="<?= (int) $tlsResult['days_remaining'] > 14 ? 'is-good' : 'is-missing' ?>"><i class="fa-solid fa-clock"></i><span><strong>Validity</strong><small><?= e($tlsResult['valid_from']) ?> to <?= e($tlsResult['valid_to']) ?>, <?= e((string) $tlsResult['days_remaining']) ?> days remaining</small></span></div>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!empty($wellKnownResult)): ?>
            <article class="cyber-card tool-result-card">
                <span class="kicker"><?= e($wellKnownHost ?? '') ?></span>
                <h2><?= e((string) $wellKnownResult['score']) ?>/100</h2>
                <p>Well-known file discovery score.</p>
                <div class="tool-check-list">
                    <?php foreach ($wellKnownResult['checks'] as $check): ?>
                        <div class="<?= $check['present'] ? 'is-good' : 'is-missing' ?>"><i class="fa-solid <?= $check['present'] ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i><span><strong><?= e($check['label']) ?></strong><small><?= e(excerpt($check['value'], 130)) ?></small></span></div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>
    </section>
<?php endif; ?>

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

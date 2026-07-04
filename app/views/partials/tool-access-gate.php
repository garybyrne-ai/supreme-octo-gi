<?php
$toolLead = $toolLead ?? null;
$scanUsage = $scanUsage ?? null;
$paypalSettings = $paypalSettings ?? [];
$creditsUrl = $paypalSettings['credits_checkout_url'] ?? '';
$pass19Url = $paypalSettings['growth_lab_19_url'] ?? '';
$pass49Url = $paypalSettings['growth_lab_49_url'] ?? '';
?>
<section class="tool-access-gate reveal" id="tool-access">
    <div class="cyber-card glass-feature">
        <span class="kicker"><?= $toolLead ? 'Access Verified' : 'Unlock Results' ?></span>
        <h2><?= $toolLead ? 'Your Growth Lab tools are active.' : 'Enter your name and email to reveal tool results.' ?></h2>
        <p><?= $toolLead ? 'You are signed in as ' . e($toolLead['email']) . '. You get 3 free server-side scans per day; unlimited scans, white-label PDF reports and background monitoring unlock with Growth Lab Pro (€25/month or €200/year).' : 'We send a one-time code to your email. Verified users get 3 free scans per day, then upgrade to Growth Lab Pro for unlimited access.' ?></p>
        <?php if ($toolLead && is_array($scanUsage)): ?>
            <div class="scan-meter" aria-label="Daily free scan usage">
                <strong><?= e((string) $scanUsage['remaining']) ?></strong>
                <span>free scans left today</span>
                <small><?= e((string) $scanUsage['used']) ?>/<?= e((string) $scanUsage['limit']) ?> used</small>
            </div>
        <?php endif; ?>
        <?php if (!empty($accessMessage)): ?><div class="notice success"><?= e($accessMessage) ?></div><?php endif; ?>
        <?php if (!empty($accessError)): ?><div class="notice error"><?= e($accessError) ?></div><?php endif; ?>
    </div>
    <?php if (!$toolLead): ?>
        <form class="cyber-form glass-tool" method="post" action="/tools/access/send-code">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="return_to" value="<?= e(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/') ?>">
            <label>Name <input name="name" required autocomplete="name" value="<?= e($pendingToolLead['name'] ?? '') ?>"></label>
            <label>Email <input name="email" type="email" required autocomplete="email" value="<?= e($pendingToolLead['email'] ?? '') ?>"></label>
            <button class="pill-button" type="submit">Send Sign-In Code <i class="fa-solid fa-envelope"></i></button>
        </form>
        <form class="cyber-form glass-tool" method="post" action="/tools/access/verify">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="return_to" value="<?= e(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/') ?>">
            <label>Sign-In Code <input name="code" inputmode="numeric" required placeholder="6-digit code"></label>
            <button class="pill-button ghost" type="submit">Verify and Unlock <i class="fa-solid fa-unlock"></i></button>
        </form>
    <?php endif; ?>
</section>

<?php
// Prefer a direct PayPal subscribe link if the admin configured one, otherwise
// send visitors to the on-site pricing page where Stripe/PayPal checkout lives.
$passUrl = $pass19Url ?: $pass49Url;
$passHref = $passUrl ?: '/tools-pricing';
$passExternal = $passUrl !== '';
?>
<section class="growth-lab-upgrade reveal">
    <article class="cyber-card growth-lab-card">
        <span class="kicker">Growth Lab Pro</span>
        <h2>Need unlimited scans, white-label reports or monitoring?</h2>
        <p>Free accounts get 3 scans per day. <strong>Growth Lab Pro — €25/month or €200/year</strong> unlocks unlimited scans across every tool, white-label PDF reports and continuous background monitoring. Built for agencies, SEO teams and business owners.</p>
        <div class="growth-lab-actions">
            <a class="pill-button" href="<?= e($passHref) ?>"<?= $passExternal ? ' target="_blank" rel="noopener"' : '' ?>>Get Growth Lab Pro <i class="fa-solid fa-bolt"></i></a>
            <a class="pill-button ghost" href="/membership">See All Plans <i class="fa-solid fa-id-card"></i></a>
        </div>
    </article>
</section>

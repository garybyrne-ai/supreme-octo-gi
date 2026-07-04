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
        <p><?= $toolLead ? 'You are signed in as ' . e($toolLead['email']) . '. You get 3 free server-side scans per day; deeper data, white-label PDF reports and background monitoring unlock with credits or a Growth Lab Pass.' : 'We send a one-time code to your email. Verified users get 3 free scans per day before credits or Growth Lab Pass upgrades.' ?></p>
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

<section class="growth-lab-upgrade reveal">
    <article class="cyber-card growth-lab-card">
        <span class="kicker">Growth Lab Pass</span>
        <h2>Need deeper scans, exportable reports or monitoring?</h2>
        <p>Free users get 3 scans per day. Credits and the $19-$49/month Growth Lab Pass are built for agencies, SEO teams and business owners who need deeper data, white-label PDF reports and continuous background monitoring.</p>
        <div class="growth-lab-actions">
            <a class="pill-button" href="<?= e($creditsUrl ?: '/contact') ?>"<?= $creditsUrl ? ' target="_blank" rel="noopener"' : '' ?>>Buy Credits <i class="fa-solid fa-credit-card"></i></a>
            <a class="pill-button ghost" href="<?= e($pass19Url ?: $pass49Url ?: '/contact') ?>"<?= ($pass19Url || $pass49Url) ? ' target="_blank" rel="noopener"' : '' ?>>Get Growth Lab Pass <i class="fa-solid fa-bolt"></i></a>
        </div>
    </article>
</section>

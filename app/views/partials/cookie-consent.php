<?php
/** @var array<string,mixed> $adsSettings */
$adsSettings = $adsSettings ?? [];
$consentMessage = (string) ($adsSettings['consent_message'] ?? 'We use cookies to keep the site secure and, with your consent, to show relevant ads and measure traffic.');
?>
<div class="cookie-consent" id="cookieConsent" role="dialog" aria-live="polite" aria-label="Cookie consent"
     data-adsense="<?= e(!empty($adsActive) ? ($adsenseClient ?? '') : '') ?>"
     data-analytics="<?= e($analyticsId ?? '') ?>" hidden>
    <div class="cookie-consent-inner">
        <div class="cookie-consent-copy">
            <strong><i class="fa-solid fa-shield-halved"></i> Your privacy choice</strong>
            <p><?= e($consentMessage) ?> <a href="/cookie-policy">Cookie Policy</a>.</p>
        </div>
        <div class="cookie-consent-actions">
            <button type="button" class="pill-button ghost" data-consent="reject">Reject non-essential</button>
            <button type="button" class="pill-button" data-consent="accept">Accept all</button>
        </div>
    </div>
</div>
<button type="button" class="cookie-reopen" id="cookieReopen" aria-label="Manage cookie settings" hidden><i class="fa-solid fa-cookie-bite"></i></button>

<?php
$adminMenu = [
    'blog' => ['title' => 'Blog Manager', 'icon' => 'fa-newspaper'],
    'portfolio' => ['title' => 'Portfolio Manager', 'icon' => 'fa-layer-group'],
    'services' => ['title' => 'Services Manager', 'icon' => 'fa-screwdriver-wrench'],
    'testimonials' => ['title' => 'Testimonials', 'icon' => 'fa-comment-dots'],
    'tickets' => ['title' => 'Support Tickets', 'icon' => 'fa-ticket'],
    'newsletter-offer' => ['title' => 'Newsletter Offer', 'icon' => 'fa-envelope-open-text'],
    'mail-settings' => ['title' => 'Mail Settings', 'icon' => 'fa-paper-plane'],
    'paypal-settings' => ['title' => 'Payment Settings', 'icon' => 'fa-credit-card'],
    'commerce' => ['title' => 'Commerce Engine', 'icon' => 'fa-cart-shopping'],
    'faq' => ['title' => 'FAQ Manager', 'icon' => 'fa-circle-question'],
    'seo' => ['title' => 'SEO Center', 'icon' => 'fa-chart-line'],
    'redirects' => ['title' => 'Redirect Manager', 'icon' => 'fa-route'],
    'media' => ['title' => 'Media Library', 'icon' => 'fa-images'],
    'forum-members' => ['title' => 'Forum Members', 'icon' => 'fa-comments'],
    'analytics' => ['title' => 'Analytics', 'icon' => 'fa-wave-square'],
    'activity' => ['title' => 'Activity Logs', 'icon' => 'fa-clock-rotate-left'],
    'backups' => ['title' => 'Backups', 'icon' => 'fa-database'],
    'theme' => ['title' => 'Theme Settings', 'icon' => 'fa-palette'],
];
$moduleSlug = $moduleSlug ?? trim((string) basename((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)));
$moduleDrafts = $moduleDrafts ?? [];
?>
<section class="admin-shell">
    <aside class="admin-sidebar">
        <a class="brand admin-brand" href="/admin/dashboard">
            <span class="brand-logo-wrap">
                <img class="brand-logo" src="<?= asset('images/crest-web-media-logo.webp') ?>" alt="Crest Web Media Admin" width="170" height="60">
                <span class="brand-comet" aria-hidden="true"></span>
            </span>
        </a>
        <nav aria-label="Admin menu">
            <?php foreach ($adminMenu as $slug => $item): ?>
                <a class="<?= active_path('/admin/modules/' . $slug) ?>" href="/admin/modules/<?= e($slug) ?>">
                    <i class="fa-solid <?= e($item['icon']) ?>"></i>
                    <span><?= e($item['title']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="/admin/logout">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button class="pill-button ghost" type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</button>
        </form>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <span class="status-chip"><span></span> CMS Module</span>
                <h1><?= e($module['title']) ?></h1>
                <p>Module command panel for controlled content operations, launch checks and future database-backed editing.</p>
            </div>
            <a class="pill-button ghost" href="/admin/dashboard"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </header>

        <section class="admin-module-detail cyber-card">
            <span class="module-orbit large"><i class="fa-solid <?= e($module['icon']) ?>"></i></span>
            <div>
                <h2><?= e($module['title']) ?> Console</h2>
                <p>This screen is connected and ready for the next CRUD layer: tables, forms, media uploads and publish workflows can plug in here without changing the public website.</p>
            </div>
            <div class="module-action-grid">
                <?php foreach ($module['actions'] as $index => $action): ?>
                    <button class="<?= $index === 0 ? 'is-active' : '' ?>" type="button" data-admin-action-target="#module-action-<?= e((string) $index) ?>">
                        <i class="fa-solid fa-bolt"></i><?= e($action) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if (!empty($notice)): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

        <section class="admin-action-panels" aria-label="<?= e($module['title']) ?> actions">
            <?php foreach ($module['actions'] as $index => $action): ?>
                <form id="module-action-<?= e((string) $index) ?>" class="cyber-form admin-action-panel <?= $index === 0 ? 'is-active' : '' ?>" method="post" action="/admin/modules/<?= e($moduleSlug) ?>/actions">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="action" value="<?= e($action) ?>">
                    <div class="admin-action-heading">
                        <span class="module-orbit"><i class="fa-solid fa-bolt"></i></span>
                        <div>
                            <span class="status-chip"><span></span> Live backend action</span>
                            <h2><?= e($action) ?></h2>
                            <p>Save a clear backend task, content update, proof note or next step for <?= e($module['title']) ?>. It will be stored in this module's action log.</p>
                        </div>
                    </div>
                    <div class="form-grid two">
                        <label>Action Title
                            <input name="title" required maxlength="160" placeholder="<?= e($action) ?> task">
                        </label>
                        <label>Status
                            <select name="status">
                                <option value="planned">Planned</option>
                                <option value="in_progress">In progress</option>
                                <option value="ready">Ready to publish</option>
                                <option value="done">Done</option>
                            </select>
                        </label>
                    </div>
                    <label>Notes
                        <textarea name="notes" rows="5" placeholder="Add copy, page URL, client note, media requirement, SEO detail or technical instruction."></textarea>
                    </label>
                    <button class="pill-button" type="submit">Save Action <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
            <?php endforeach; ?>
        </section>

        <section class="cyber-card admin-action-log">
            <div class="section-heading compact">
                <span class="status-chip"><span></span> Stored backend log</span>
                <h2><?= e($module['title']) ?> Action Log</h2>
            </div>
            <?php if (!empty($moduleDrafts)): ?>
                <div class="admin-log-list">
                    <?php foreach ($moduleDrafts as $draft): ?>
                        <article>
                            <div>
                                <strong><?= e($draft['title'] ?? '') ?></strong>
                                <span><?= e($draft['action'] ?? '') ?> - <?= e($draft['created_at'] ?? '') ?></span>
                            </div>
                            <em><?= e(str_replace('_', ' ', (string) ($draft['status'] ?? 'planned'))) ?></em>
                            <?php if (!empty($draft['notes'])): ?>
                                <p><?= nl2br(e($draft['notes'])) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No saved backend actions yet. Pick a command above, add the update, and save it here.</p>
            <?php endif; ?>
        </section>

        <?php if (($module['title'] ?? '') === 'Media Library'): ?>
            <section class="media-library-panel">
                <form class="cyber-form media-upload-form" method="post" action="/admin/media/upload" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Upload Optimized Media</h2>
                    <p>JPG, PNG, GIF and WebP files are converted to WebP automatically. PHP, SVG and script uploads are blocked.</p>
                    <label class="media-drop-zone">Select Image
                        <input name="asset" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
                        <span><i class="fa-solid fa-cloud-arrow-up"></i> Drop or choose an image up to 8MB</span>
                    </label>
                    <label>Alt Text
                        <input name="alt" maxlength="255" placeholder="Describe the image for accessibility and SEO">
                    </label>
                    <button class="pill-button" type="submit">Upload + Convert <i class="fa-solid fa-image"></i></button>
                </form>

                <aside class="cyber-card media-guidance">
                    <h2>Upload Protection</h2>
                    <ul class="check-list">
                        <li>Raster images only: JPG, PNG, GIF and WebP.</li>
                        <li>Files are renamed, converted and stored under `/uploads/media`.</li>
                        <li>Upload folders disable PHP execution and directory listing.</li>
                        <li>Use the gallery below to copy URLs into pages, posts or portfolio entries.</li>
                    </ul>
                </aside>
            </section>

            <section class="cyber-card media-gallery-card">
                <div class="section-heading compact">
                    <span class="status-chip"><span></span> Gallery</span>
                    <h2>Uploaded Images</h2>
                </div>
                <?php if (!empty($mediaItems)): ?>
                    <div class="media-grid">
                        <?php foreach ($mediaItems as $item): ?>
                            <article class="media-card">
                                <img src="<?= e($item['path'] ?? '') ?>" alt="<?= e($item['alt'] ?? $item['filename'] ?? 'Uploaded media') ?>" loading="lazy">
                                <div>
                                    <strong><?= e($item['filename'] ?? 'media.webp') ?></strong>
                                    <small><?= e(number_format(((int) ($item['size_bytes'] ?? 0)) / 1024, 1)) ?> KB<?= !empty($item['width']) ? ' - ' . e((string) $item['width']) . 'x' . e((string) $item['height']) : '' ?></small>
                                    <input readonly value="<?= e($item['path'] ?? '') ?>" aria-label="Media URL">
                                    <button class="pill-button ghost" type="button" data-copy-value="<?= e($item['path'] ?? '') ?>"><i class="fa-solid fa-copy"></i> Copy URL</button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No uploaded images yet. Add your first image above and it will appear here.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Support Tickets'): ?>
            <section class="cyber-card ticket-table-card">
                <h2>Ticket Queue</h2>
                <?php if (!empty($tickets)): ?>
                    <div class="ticket-table full">
                        <?php foreach ($tickets as $ticket): ?>
                            <div>
                                <strong><?= e($ticket['reference'] ?? '') ?></strong>
                                <span><?= e($ticket['subject'] ?? '') ?><small><?= e($ticket['name'] ?? '') ?> - <?= e($ticket['email'] ?? '') ?></small></span>
                                <em><?= e($ticket['priority'] ?? 'normal') ?></em>
                                <small><?= e($ticket['status'] ?? 'open') ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No tickets have been created yet.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Forum Members'): ?>
            <section class="cyber-card ticket-table-card">
                <h2>Registered Forum Members</h2>
                <p>Users can read the tech forum immediately, but only admin-approved accounts can post replies.</p>
                <?php if (!empty($forumMembers)): ?>
                    <div class="ticket-table full forum-member-table">
                        <?php foreach ($forumMembers as $member): ?>
                            <div>
                                <strong><?= e($member['name'] ?? '') ?></strong>
                                <span><?= e($member['email'] ?? '') ?><small>Registered <?= e($member['created_at'] ?? '') ?></small></span>
                                <em><?= !empty($member['forum_verified']) ? 'approved' : 'pending' ?></em>
                                <form method="post" action="/admin/forum-members/verify">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="member_id" value="<?= e($member['id'] ?? '') ?>">
                                    <?php if (!empty($member['forum_verified'])): ?>
                                        <button class="pill-button ghost" type="submit" name="status" value="revoke">Revoke</button>
                                    <?php else: ?>
                                        <button class="pill-button" type="submit" name="status" value="approve">Approve</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No registered forum members yet.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Newsletter Offer'): ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/newsletter-offer">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Edit Automated Offer</h2>
                    <label>Subject <input name="subject" required value="<?= e($newsletterOffer['subject'] ?? '') ?>"></label>
                    <label>Preheader <input name="preheader" required value="<?= e($newsletterOffer['preheader'] ?? '') ?>"></label>
                    <label>Headline <input name="headline" required value="<?= e($newsletterOffer['headline'] ?? '') ?>"></label>
                    <label>Intro <textarea name="intro" rows="5" required><?= e($newsletterOffer['intro'] ?? '') ?></textarea></label>
                    <label>Offer <textarea name="offer" rows="6" required><?= e($newsletterOffer['offer'] ?? '') ?></textarea></label>
                    <label>CTA Label <input name="cta_label" required value="<?= e($newsletterOffer['cta_label'] ?? '') ?>"></label>
                    <label>CTA URL <input name="cta_url" type="url" required value="<?= e($newsletterOffer['cta_url'] ?? '') ?>"></label>
                    <button class="pill-button" type="submit">Save Offer <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <aside class="cyber-card ticket-table-card">
                    <h2>Recent Tool Leads</h2>
                    <?php if (!empty($toolLeads)): ?>
                        <div class="ticket-table full">
                            <?php foreach ($toolLeads as $lead): ?>
                                <div>
                                    <strong><?= e($lead['name'] ?? '') ?></strong>
                                    <span><?= e($lead['email'] ?? '') ?><small><?= e($lead['source'] ?? 'tools') ?></small></span>
                                    <em>lead</em>
                                    <small><?= e($lead['created_at'] ?? '') ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No tool leads yet.</p>
                    <?php endif; ?>
                </aside>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Mail Settings'): ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/mail-settings">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Email Transport</h2>
                    <label>Mail Driver
                        <select name="driver">
                            <option value="php_mail" <?= ($mailSettings['driver'] ?? 'php_mail') === 'php_mail' ? 'selected' : '' ?>>PHP mail()</option>
                            <option value="gmail_smtp" <?= ($mailSettings['driver'] ?? '') === 'gmail_smtp' ? 'selected' : '' ?>>Gmail SMTP</option>
                        </select>
                    </label>
                    <div class="form-grid two">
                        <label>From Name <input name="from_name" required value="<?= e($mailSettings['from_name'] ?? '') ?>"></label>
                        <label>From Email <input name="from_email" type="email" required value="<?= e($mailSettings['from_email'] ?? '') ?>"></label>
                    </div>
                    <h2>Gmail SMTP</h2>
                    <div class="form-grid two">
                        <label>SMTP Host <input name="smtp_host" value="<?= e($mailSettings['smtp_host'] ?? 'smtp.gmail.com') ?>"></label>
                        <label>SMTP Port <input name="smtp_port" inputmode="numeric" value="<?= e($mailSettings['smtp_port'] ?? '587') ?>"></label>
                    </div>
                    <label>Encryption
                        <select name="smtp_encryption">
                            <option value="tls" <?= ($mailSettings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS / STARTTLS</option>
                            <option value="none" <?= ($mailSettings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                    </label>
                    <label>Gmail Username <input name="smtp_username" type="email" autocomplete="username" value="<?= e($mailSettings['smtp_username'] ?? '') ?>" placeholder="yourname@gmail.com"></label>
                    <label>Gmail App Password <input name="smtp_password" type="password" autocomplete="new-password" placeholder="<?= !empty($mailSettings['smtp_password']) ? 'Saved - leave blank to keep' : '16-character Gmail app password' ?>"></label>
                    <button class="pill-button" type="submit">Save Mail Settings <i class="fa-solid fa-paper-plane"></i></button>
                </form>
                <aside class="cyber-card">
                    <h2>Gmail Setup Notes</h2>
                    <ul class="check-list">
                        <li>Use a Gmail or Google Workspace account.</li>
                        <li>Enable 2-Step Verification in Google Account security.</li>
                        <li>Create a 16-character App Password for Mail and paste it here. Do not use your normal Gmail password.</li>
                        <li>Use `smtp.gmail.com`, port `587`, TLS.</li>
                        <li>For Gmail SMTP, the visible sender email should normally match the Gmail username unless that alias is verified in Google.</li>
                        <li>Both sign-in codes and automatic offer emails use this setting.</li>
                    </ul>
                    <form class="mail-test-form" method="post" action="/admin/mail-settings/test">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <label>Send Test Email
                            <input name="test_email" type="email" required placeholder="your@email.com" value="<?= e($_SESSION['admin']['email'] ?? '') ?>">
                        </label>
                        <button class="pill-button" type="submit">Send Test <i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </aside>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Payment Settings'): ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/paypal-settings">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Growth Lab PayPal Gateway</h2>
                    <label>Mode
                        <select name="mode">
                            <option value="sandbox" <?= ($paypalSettings['mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                            <option value="live" <?= ($paypalSettings['mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                        </select>
                    </label>
                    <div class="form-grid two">
                        <label>PayPal Merchant ID
                            <input name="merchant_id" value="<?= e($paypalSettings['merchant_id'] ?? '') ?>" placeholder="Merchant account ID">
                        </label>
                        <label>Currency
                            <input name="currency" maxlength="3" value="<?= e($paypalSettings['currency'] ?? 'USD') ?>" placeholder="USD">
                        </label>
                    </div>
                    <label>Client ID
                        <input name="client_id" value="<?= e($paypalSettings['client_id'] ?? '') ?>" placeholder="PayPal REST app client ID">
                    </label>
                    <label>Client Secret
                        <input name="client_secret" type="password" autocomplete="new-password" placeholder="<?= !empty($paypalSettings['client_secret']) ? 'Saved - leave blank to keep' : 'PayPal REST app secret' ?>">
                    </label>
                    <label>Webhook ID
                        <input name="webhook_id" value="<?= e($paypalSettings['webhook_id'] ?? '') ?>" placeholder="Webhook ID for payment/subscription events">
                    </label>
                    <h2>Checkout Links</h2>
                    <label>Code Shop PayPal Checkout URL
                        <input name="shop_paypal_checkout_url" type="url" value="<?= e($paypalSettings['shop_paypal_checkout_url'] ?? '') ?>" placeholder="Photo To Key PayPal checkout link">
                    </label>
                    <label>Credit Pack Checkout URL
                        <input name="credits_checkout_url" type="url" value="<?= e($paypalSettings['credits_checkout_url'] ?? '') ?>" placeholder="https://www.paypal.com/checkoutnow?...">
                    </label>
                    <div class="form-grid two">
                        <label>$19 Growth Lab Pass URL
                            <input name="growth_lab_19_url" type="url" value="<?= e($paypalSettings['growth_lab_19_url'] ?? '') ?>" placeholder="PayPal subscription link">
                        </label>
                        <label>$49 Growth Lab Pass URL
                            <input name="growth_lab_49_url" type="url" value="<?= e($paypalSettings['growth_lab_49_url'] ?? '') ?>" placeholder="PayPal subscription link">
                        </label>
                    </div>
                    <h2>Stripe Checkout</h2>
                    <label>Stripe Secret Key
                        <input name="stripe_secret_key" type="password" autocomplete="new-password" placeholder="<?= !empty($paypalSettings['stripe_secret_key']) ? 'Saved - leave blank to keep' : 'sk_live_...' ?>">
                    </label>
                    <label>Stripe Webhook Secret
                        <input name="stripe_webhook_secret" type="password" autocomplete="new-password" placeholder="<?= !empty($paypalSettings['stripe_webhook_secret']) ? 'Saved - leave blank to keep' : 'whsec_...' ?>">
                    </label>
                    <label>Photo To Key Stripe Price ID
                        <input name="stripe_price_photo_to_key" value="<?= e($paypalSettings['stripe_price_photo_to_key'] ?? '') ?>" placeholder="price_123">
                    </label>
                    <div class="form-grid two">
                        <label>Stripe Success URL
                            <input name="stripe_success_url" type="url" value="<?= e($paypalSettings['stripe_success_url'] ?? '') ?>" placeholder="Optional custom success URL">
                        </label>
                        <label>Stripe Cancel URL
                            <input name="stripe_cancel_url" type="url" value="<?= e($paypalSettings['stripe_cancel_url'] ?? '') ?>" placeholder="Optional custom cancel URL">
                        </label>
                    </div>
                    <button class="pill-button" type="submit">Save Payment Settings <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <aside class="cyber-card">
                    <h2>Gateway Checklist</h2>
                    <ul class="check-list">
                        <li>Verified users receive 3 free server-side scans per day.</li>
                        <li>After the free limit, tool pages promote credits and the $19-$49/month Growth Lab Pass.</li>
                        <li>Code Shop card checkout needs a Stripe secret key, webhook secret and Photo To Key Price ID.</li>
                        <li>Stripe webhook URL: <code>/webhooks/stripe</code>. Add <code>checkout.session.completed</code> in Stripe.</li>
                        <li>PayPal checkout uses your configured Code Shop PayPal URL.</li>
                        <li>Keep Sandbox enabled until PayPal and Stripe are tested end to end.</li>
                    </ul>
                </aside>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Theme Settings'): ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/cms/theme-assets" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Brand Asset Console</h2>
                    <div class="form-grid two">
                        <label>Asset Key
                            <select name="asset_key">
                                <option value="logo">Logo</option>
                                <option value="site-icon">Site Icon</option>
                                <option value="social-card">Social Card</option>
                            </select>
                        </label>
                        <label>Variant
                            <select name="variant">
                                <option value="primary">Primary</option>
                                <option value="dark">Dark</option>
                                <option value="light">Light</option>
                                <option value="icon">Icon</option>
                                <option value="favicon">Favicon</option>
                                <option value="social">Social</option>
                            </select>
                        </label>
                    </div>
                    <label class="media-drop-zone">Upload SVG/WebP/PNG/ICO
                        <input name="asset" type="file" accept="image/svg+xml,image/webp,image/png,image/x-icon" required>
                        <span><i class="fa-solid fa-cloud-arrow-up"></i> Upload validated brand asset</span>
                    </label>
                    <button class="pill-button" type="submit">Upload Asset <i class="fa-solid fa-floppy-disk"></i></button>
                </form>

                <form class="cyber-form" method="post" action="/admin/cms/typography">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Typography Engine</h2>
                    <p>Use verified Google font names only. The saved map compiles into cached CSS variables to prevent layout shift.</p>
                    <label>Typography Rules JSON
                        <textarea name="rules" rows="12">{
  "body": {"font_family": "Raleway", "font_weights": [400], "fallback_stack": "\"Segoe UI\", Aptos, system-ui, sans-serif", "is_enabled": true, "sort_order": 10},
  "h1": {"font_family": "Oxanium", "font_weights": [800], "fallback_stack": "\"Segoe UI Variable Display\", system-ui, sans-serif", "is_enabled": true, "sort_order": 20},
  "buttons": {"font_family": "Raleway", "font_weights": [800], "fallback_stack": "\"Segoe UI\", system-ui, sans-serif", "is_enabled": true, "sort_order": 30},
  "main-menu": {"font_family": "Raleway", "font_weights": [800], "fallback_stack": "\"Segoe UI\", system-ui, sans-serif", "is_enabled": true, "sort_order": 40}
}</textarea>
                    </label>
                    <button class="pill-button" type="submit">Compile Font Map <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
            </section>

            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/cms/custom-code">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Custom CSS / JS Compiler</h2>
                    <div class="form-grid two">
                        <label>Name <input name="name" value="Theme override"></label>
                        <label>Type
                            <select name="asset_type">
                                <option value="css">CSS</option>
                                <option value="js">JavaScript</option>
                            </select>
                        </label>
                    </div>
                    <label>Load Strategy
                        <select name="load_strategy">
                            <option value="defer">Defer</option>
                            <option value="async">Async</option>
                            <option value="module">Module</option>
                        </select>
                    </label>
                    <label><input type="checkbox" name="is_enabled" value="1" checked> Enable after save</label>
                    <label>Code
                        <textarea name="code" rows="9" placeholder="Paste safe CSS or JS. CSS is minified into /assets/css/cms. JS is minified into /assets/js/cms."></textarea>
                    </label>
                    <button class="pill-button" type="submit">Compile Custom Code <i class="fa-solid fa-code"></i></button>
                </form>

                <form class="cyber-form" method="post" action="/admin/cms/script-injections">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Script Injection Inventory</h2>
                    <div class="form-grid two">
                        <label>Name <input name="name" value="Analytics Pixel"></label>
                        <label>Location
                            <select name="location">
                                <option value="head">Head</option>
                                <option value="body_open">Body Open</option>
                                <option value="footer_close">Footer Close</option>
                            </select>
                        </label>
                    </div>
                    <label><input type="checkbox" name="is_enabled" value="1"> Enable after validation</label>
                    <label>HTML / Script Block
                        <textarea name="code" rows="9" placeholder="Approved tracking snippets only. Unsafe XSS vectors and unapproved remote hosts are blocked."></textarea>
                    </label>
                    <button class="pill-button" type="submit">Save Injection <i class="fa-solid fa-shield-halved"></i></button>
                </form>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Commerce Engine'): ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/cms/products">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Digital Product Lifecycle</h2>
                    <div class="form-grid two">
                        <label>Title <input name="title" required value="Photo To Key PHP Website With Backend"></label>
                        <label>Slug <input name="slug" required value="photo-to-key-php-website-backend"></label>
                    </div>
                    <label>Summary
                        <textarea name="summary" rows="3" required>A complete camera-first key ordering website with PHP backend, checkout, shipping, VAT and Core Web Vitals-ready frontend.</textarea>
                    </label>
                    <label>Description
                        <textarea name="description" rows="6">Includes browser camera capture, upload flow, backend order management, Stripe/PayPal hooks, VAT/shipping fields and high-performance frontend sections.</textarea>
                    </label>
                    <div class="form-grid two">
                        <label>Product Type
                            <select name="product_type">
                                <option value="file">Downloadable file</option>
                                <option value="snippet">Code snippet</option>
                            </select>
                        </label>
                        <label>Platform <input name="platform" value="Core PHP"></label>
                    </div>
                    <div class="form-grid two">
                        <label>Price <input name="price" inputmode="decimal" value="49.00"></label>
                        <label>Currency <input name="currency" maxlength="3" value="USD"></label>
                    </div>
                    <label>Platform Tags <input name="platform_tags" value="PHP 8, MySQL, Browser Camera, Stripe, PayPal, VAT, Shipping, Core Web Vitals"></label>
                    <label>Stripe Price ID <input name="stripe_price_id" placeholder="price_..."></label>
                    <label>PayPal Checkout URL <input name="paypal_checkout_url" type="url" placeholder="https://www.paypal.com/checkoutnow?..."></label>
                    <label><input type="checkbox" name="is_active" value="1" checked> Active in shop</label>
                    <button class="pill-button" type="submit">Save Product <i class="fa-solid fa-floppy-disk"></i></button>
                </form>

                <form class="cyber-form" method="post" action="/admin/cms/product-assets" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Private Package Upload</h2>
                    <p>ZIP files are stored above the public root. Customers receive short-lived, count-limited download grants after successful payment.</p>
                    <label>Product ID <input name="product_id" inputmode="numeric" required placeholder="Product database ID"></label>
                    <label>Version Label <input name="version_label" value="1.0.0"></label>
                    <label class="media-drop-zone">Upload ZIP Package
                        <input name="package" type="file" accept="application/zip,.zip" required>
                        <span><i class="fa-solid fa-cloud-arrow-up"></i> Attach private downloadable ZIP</span>
                    </label>
                    <button class="pill-button" type="submit">Upload Package <i class="fa-solid fa-lock"></i></button>
                </form>
            </section>
        <?php endif; ?>
    </div>
</section>

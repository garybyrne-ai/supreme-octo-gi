<?php
$adminMenu = [
    'blog' => ['title' => 'Blog Manager', 'icon' => 'fa-newspaper'],
    'portfolio' => ['title' => 'Portfolio Manager', 'icon' => 'fa-layer-group'],
    'services' => ['title' => 'Services Manager', 'icon' => 'fa-screwdriver-wrench'],
    'testimonials' => ['title' => 'Testimonials', 'icon' => 'fa-comment-dots'],
    'tickets' => ['title' => 'Support Tickets', 'icon' => 'fa-ticket'],
    'content' => ['title' => 'Site Content', 'icon' => 'fa-pen-ruler'],
    'newsletter-offer' => ['title' => 'Newsletter Offer', 'icon' => 'fa-envelope-open-text'],
    'mail-settings' => ['title' => 'Mail Settings', 'icon' => 'fa-paper-plane'],
    'paypal-settings' => ['title' => 'Payment Settings', 'icon' => 'fa-credit-card'],
    'commerce' => ['title' => 'Commerce Engine', 'icon' => 'fa-cart-shopping'],
    'membership' => ['title' => 'Membership Plans', 'icon' => 'fa-id-card'],
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

        <?php if (($module['title'] ?? '') === 'Site Content'): ?>
            <?php
                $sc = $siteContent ?? [];
                $scHero = $sc['hero'] ?? [];
                $scContact = $sc['contact'] ?? [];
                $scIntros = $sc['page_intros'] ?? [];
                $introDefs = $pageIntroDefs ?? [];
                $backlinkPlans = $backlinkPlans ?? [];
                $servingLines = [];
                foreach (($scHero['serving'] ?? []) as $s) {
                    $servingLines[] = trim(($s['flag'] ?? '') . ' | ' . ($s['label'] ?? ''), ' |');
                }
                $locationLines = [];
                foreach (($scContact['locations'] ?? []) as $l) {
                    $locationLines[] = trim(($l['name'] ?? '') . ' | ' . ($l['type'] ?? '') . ' | ' . ($l['timezone'] ?? ''), ' |');
                }
            ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content/hero">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Home Hero</h2>
                    <label>Status chip text <input name="chip_text" value="<?= e($scHero['chip_text'] ?? '') ?>"></label>
                    <label>Headline <small>(use &lt;span&gt;…&lt;/span&gt; to highlight words)</small>
                        <textarea name="headline" rows="2"><?= e($scHero['headline'] ?? '') ?></textarea></label>
                    <label>Subheading <textarea name="subheading" rows="3"><?= e($scHero['subheading'] ?? '') ?></textarea></label>
                    <label>"Serving Clients In" label <input name="serving_label" value="<?= e($scHero['serving_label'] ?? '') ?>"></label>
                    <label>Serving list <small>(one per line, format: flag | label)</small>
                        <textarea name="serving" rows="5"><?= e(implode("\n", $servingLines)) ?></textarea></label>
                    <div class="form-grid two">
                        <label>Primary button label <input name="cta_primary_label" value="<?= e($scHero['cta_primary_label'] ?? '') ?>"></label>
                        <label>Primary button URL <input name="cta_primary_url" value="<?= e($scHero['cta_primary_url'] ?? '') ?>"></label>
                        <label>Secondary button label <input name="cta_secondary_label" value="<?= e($scHero['cta_secondary_label'] ?? '') ?>"></label>
                        <label>Secondary button URL <input name="cta_secondary_url" value="<?= e($scHero['cta_secondary_url'] ?? '') ?>"></label>
                    </div>
                    <button class="pill-button" type="submit">Save Hero <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <form class="cyber-form" method="post" action="/admin/content/contact">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Contact &amp; DIRECT SIGNAL</h2>
                    <label>Contact email <input name="email" type="email" value="<?= e($scContact['email'] ?? '') ?>"></label>
                    <div class="form-grid two">
                        <label>Phone (raw) <input name="phone" value="<?= e($scContact['phone'] ?? '') ?>"></label>
                        <label>Phone (display) <input name="phone_display" value="<?= e($scContact['phone_display'] ?? '') ?>"></label>
                    </div>
                    <label>WhatsApp URL <input name="whatsapp_url" value="<?= e($scContact['whatsapp_url'] ?? '') ?>"></label>
                    <label>DIRECT SIGNAL location line <input name="signal_line" value="<?= e($scContact['signal_line'] ?? '') ?>"></label>
                    <label>Locations <small>(one per line, format: name | type | timezone)</small>
                        <textarea name="locations" rows="4"><?= e(implode("\n", $locationLines)) ?></textarea></label>
                    <button class="pill-button" type="submit">Save Contact <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
            </section>

            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content/page-intros">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Page Intro Headings</h2>
                    <p>Override the kicker &amp; heading on key marketing pages. Leave blank to keep the built-in default.</p>
                    <?php foreach ($introDefs as $key => $def): ?>
                        <?php $savedIntro = $scIntros[$key] ?? []; ?>
                        <div class="form-grid two">
                            <label><?= e($def[0]) ?> — kicker
                                <input name="<?= e($key) ?>_kicker" placeholder="<?= e($def[1] ?? '') ?>" value="<?= e($savedIntro['kicker'] ?? '') ?>"></label>
                            <label><?= e($def[0]) ?> — heading
                                <input name="<?= e($key) ?>_heading" placeholder="<?= e($def[0]) ?>" value="<?= e($savedIntro['heading'] ?? '') ?>"></label>
                        </div>
                    <?php endforeach; ?>
                    <button class="pill-button" type="submit">Save Page Intros <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
            </section>

            <section class="split-section">
                <form class="cyber-form membership-plan-form" method="post" action="/admin/backlink-plans">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="original_slug" value="" data-plan-original-slug>
                    <h2>Backlink Plan</h2>
                    <p>Add or edit an Irish (.ie) backlink plan sold on <a href="/backlinks" target="_blank" rel="noopener">/backlinks</a>. Click "Edit" on a plan below to load it here.</p>
                    <div class="form-grid two">
                        <label>Name <input name="name" data-plan-field="name" required></label>
                        <label>Slug <small>(blank = auto)</small> <input name="slug" data-plan-field="slug"></label>
                        <label>Price <input name="price" data-plan-field="price" placeholder="€59"></label>
                        <label>Links summary <input name="links" data-plan-field="links" placeholder="25 Irish (.ie) backlinks"></label>
                        <label>Badge <input name="badge" data-plan-field="badge" placeholder="Rare .ie links"></label>
                        <label>Sort order <input name="sort_order" data-plan-field="sort_order" type="number" value="100"></label>
                        <label>Checkout URL <small>(Stripe/PayPal link, optional)</small> <input name="checkout_url" data-plan-field="checkout_url"></label>
                    </div>
                    <label>Features <small>(one per line)</small> <textarea name="features" rows="6" data-plan-field="features"></textarea></label>
                    <div class="checkbox-row">
                        <label><input type="checkbox" name="active" value="1" checked data-plan-field="active"> Active (shown on site)</label>
                        <label><input type="checkbox" name="featured" value="1" data-plan-field="featured"> Featured (most popular)</label>
                    </div>
                    <button class="pill-button" type="submit">Save Plan <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <section class="cyber-card membership-plan-list">
                    <div class="section-heading compact">
                        <span class="status-chip"><span></span> Live plans</span>
                        <h2>Backlink Tiers</h2>
                    </div>
                    <?php if (!empty($backlinkPlans)): ?>
                        <div class="membership-plan-grid">
                            <?php foreach ($backlinkPlans as $bp): ?>
                                <article class="membership-plan-item <?= empty($bp['active']) ? 'is-paused' : '' ?>">
                                    <header>
                                        <div>
                                            <strong><?= e($bp['name']) ?></strong>
                                            <small><?= e($bp['slug']) ?></small>
                                        </div>
                                        <span class="membership-price"><?= e($bp['price']) ?><em><?= e($bp['links']) ?></em></span>
                                    </header>
                                    <div class="membership-plan-flags">
                                        <em class="pill-status <?= !empty($bp['active']) ? 'is-live' : 'is-paused' ?>"><?= !empty($bp['active']) ? 'Active' : 'Paused' ?></em>
                                        <?php if (!empty($bp['featured'])): ?><em class="pill-status is-feature">Featured</em><?php endif; ?>
                                        <?php if (!empty($bp['checkout_url'])): ?><em class="pill-status is-gw">Checkout link</em><?php endif; ?>
                                    </div>
                                    <div class="membership-plan-actions">
                                        <button type="button" class="pill-button ghost" data-edit-backlink-plan='<?= e(json_encode($bp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>'><i class="fa-solid fa-pen"></i> Edit</button>
                                        <form method="post" action="/admin/backlink-plans/state">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="slug" value="<?= e($bp['slug']) ?>">
                                            <input type="hidden" name="state" value="<?= !empty($bp['active']) ? 'deactivate' : 'activate' ?>">
                                            <button class="pill-button ghost" type="submit"><?= !empty($bp['active']) ? 'Pause' : 'Activate' ?></button>
                                        </form>
                                        <form method="post" action="/admin/backlink-plans/state" onsubmit="return confirm('Delete this backlink plan? This cannot be undone.');">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="slug" value="<?= e($bp['slug']) ?>">
                                            <input type="hidden" name="state" value="delete">
                                            <button class="pill-button ghost danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No backlink plans yet. Add your first plan on the left — it appears on <a href="/backlinks">/backlinks</a> instantly once active.</p>
                    <?php endif; ?>
                </section>
            </section>
            <script>
            (function () {
                var form = document.querySelector('.membership-plan-form[action="/admin/backlink-plans"]');
                if (!form) { return; }
                document.querySelectorAll('[data-edit-backlink-plan]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var plan = JSON.parse(btn.getAttribute('data-edit-backlink-plan'));
                        var set = function (name, val) {
                            var el = form.querySelector('[data-plan-field="' + name + '"]');
                            if (el) { el.value = val; }
                        };
                        set('name', plan.name || '');
                        set('slug', plan.slug || '');
                        set('price', plan.price || '');
                        set('links', plan.links || '');
                        set('badge', plan.badge || '');
                        set('sort_order', plan.sort_order || 100);
                        set('checkout_url', plan.checkout_url || '');
                        set('features', (plan.features || []).join('\n'));
                        form.querySelector('[data-plan-field="featured"]').checked = !!plan.featured;
                        form.querySelector('[data-plan-field="active"]').checked = !!plan.active;
                        form.querySelector('[data-plan-original-slug]').value = plan.slug || '';
                        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                });
            })();
            </script>
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
                    <h2>Search Data <span class="cyber-hint">real SERP rankings</span></h2>
                    <label>ZenSERP API Key
                        <input name="serp_api_key" type="password" autocomplete="off" placeholder="<?= !empty($paypalSettings['serp_api_key']) ? 'Saved - leave blank to keep' : 'Paste your ZenSERP apikey' ?>">
                    </label>
                    <p class="cyber-note">When set, the SERP Checker returns live Google rankings via ZenSERP instead of the free fallback. Stored privately on the server (never in code). You can also set the <code>ZENSERP_API_KEY</code> environment variable.</p>
                    <label>Google PageSpeed API Key <span class="cyber-hint">optional</span>
                        <input name="google_psi_key" type="password" autocomplete="off" placeholder="<?= !empty($paypalSettings['google_psi_key']) ? 'Saved - leave blank to keep' : 'Optional — raises PageSpeed quota' ?>">
                    </label>
                    <p class="cyber-note">The PageSpeed &amp; Core Web Vitals tool works without a key at low volume. Add a free Google PageSpeed Insights API key (or set <code>GOOGLE_PSI_KEY</code>) to raise the request limit.</p>
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
            <?php
            $catalogCategories = $catalogCategories ?? \App\Models\CommerceRepository::CATALOG_CATEGORIES;
            $serviceDeliveries = $serviceDeliveries ?? \App\Models\CommerceRepository::SERVICE_DELIVERIES;
            $catalogProducts = $catalogProducts ?? [];
            ?>
            <section class="split-section">
                <form class="cyber-form marketplace-product-form" method="post" action="/admin/cms/products">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Marketplace Item <span class="cyber-hint">ThemeForest / CodeCanyon style</span></h2>
                    <p>Sell themes, plugins, templates, Squarespace &amp; Wix modules, PHP scripts and done-for-you services. To edit an existing item, reuse its exact slug.</p>
                    <div class="form-grid two">
                        <label>Title <input name="title" required placeholder="Aurora WordPress Portfolio Theme"></label>
                        <label>Slug <input name="slug" required placeholder="aurora-wordpress-portfolio-theme"></label>
                    </div>
                    <label>Subtitle / short tag <input name="subtitle" maxlength="190" placeholder="Responsive creative theme with 12 demos"></label>
                    <div class="form-grid two">
                        <label>Category
                            <select name="catalog_category">
                                <?php foreach ($catalogCategories as $key => $label): ?>
                                    <option value="<?= e($key) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Service Delivery <span class="cyber-hint">Figma/PSD to WordPress</span>
                            <select name="service_delivery">
                                <?php foreach ($serviceDeliveries as $key => $label): ?>
                                    <option value="<?= e($key) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <label>Summary
                        <textarea name="summary" rows="3" required placeholder="One-paragraph marketplace summary shown on the shop card."></textarea>
                    </label>
                    <label>Description
                        <textarea name="description" rows="6" placeholder="Full item description, feature list, compatibility, changelog notes."></textarea>
                    </label>
                    <div class="form-grid two">
                        <label>Delivery Type
                            <select name="product_type">
                                <option value="file">Downloadable file</option>
                                <option value="snippet">Code snippet</option>
                            </select>
                        </label>
                        <label>Platform <input name="platform" value="WordPress"></label>
                    </div>
                    <div class="form-grid two">
                        <label>Regular License Price <input name="price" inputmode="decimal" value="39.00"></label>
                        <label>Extended License Price <input name="extended_price" inputmode="decimal" placeholder="Optional, e.g. 399.00"></label>
                    </div>
                    <div class="form-grid two">
                        <label>Currency <input name="currency" maxlength="3" value="USD"></label>
                        <label>Sort Order <input name="sort_order" inputmode="numeric" value="100"></label>
                    </div>
                    <label>Live Preview / Demo URL <input name="demo_url" type="url" placeholder="https://demo.crestwebmedia.com/aurora"></label>
                    <label>Thumbnail URL <input name="thumbnail_url" type="url" placeholder="https://.../aurora-preview.webp"></label>
                    <label>Tags <input name="platform_tags" placeholder="WordPress, Elementor, WooCommerce, Responsive, RTL"></label>
                    <div class="form-grid two">
                        <label>Stripe Price ID <input name="stripe_price_id" placeholder="price_..."></label>
                        <label>PayPal Checkout URL <input name="paypal_checkout_url" type="url" placeholder="https://www.paypal.com/checkoutnow?..."></label>
                    </div>
                    <div class="checkbox-row">
                        <label><input type="checkbox" name="is_active" value="1" checked> Active in shop</label>
                        <label><input type="checkbox" name="is_featured" value="1"> Feature on marketplace</label>
                    </div>
                    <button class="pill-button" type="submit">Save Marketplace Item <i class="fa-solid fa-floppy-disk"></i></button>
                </form>

                <form class="cyber-form" method="post" action="/admin/cms/product-assets" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Private Package Upload</h2>
                    <p>ZIP files are stored above the public root. Buyers receive short-lived, count-limited download grants after successful payment.</p>
                    <label>Product ID <input name="product_id" inputmode="numeric" required placeholder="Product database ID (see table below)"></label>
                    <label>Version Label <input name="version_label" value="1.0.0"></label>
                    <label class="media-drop-zone">Upload ZIP Package
                        <input name="package" type="file" accept="application/zip,.zip" required>
                        <span><i class="fa-solid fa-cloud-arrow-up"></i> Attach private downloadable ZIP</span>
                    </label>
                    <button class="pill-button" type="submit">Upload Package <i class="fa-solid fa-lock"></i></button>
                </form>
            </section>

            <section class="cyber-card commerce-catalog-card">
                <div class="section-heading compact">
                    <span class="status-chip"><span></span> Live catalog</span>
                    <h2>Marketplace Items</h2>
                </div>
                <?php if (!empty($catalogProducts)): ?>
                    <div class="commerce-catalog-table">
                        <div class="commerce-catalog-head">
                            <span>ID</span><span>Item</span><span>Category</span><span>Price</span><span>Status</span>
                        </div>
                        <?php foreach ($catalogProducts as $product): ?>
                            <?php
                            $priceLabel = '$' . number_format(((int) ($product['price_cents'] ?? 0)) / 100, 2);
                            $extended = $product['extended_price_cents'] ?? null;
                            if ($extended !== null && $extended !== '') {
                                $priceLabel .= ' / $' . number_format(((int) $extended) / 100, 2);
                            }
                            ?>
                            <div class="commerce-catalog-row">
                                <span class="cc-id">#<?= e((string) $product['id']) ?></span>
                                <span class="cc-title"><strong><?= e($product['title'] ?? '') ?></strong><small><?= e($product['slug'] ?? '') ?></small></span>
                                <span class="cc-cat"><?= e($catalogCategories[$product['catalog_category'] ?? 'file'] ?? 'Digital Product') ?></span>
                                <span class="cc-price"><?= e($priceLabel) ?></span>
                                <span class="cc-status">
                                    <em class="pill-status <?= !empty($product['is_active']) ? 'is-live' : 'is-paused' ?>"><?= !empty($product['is_active']) ? 'Live' : 'Paused' ?></em>
                                    <?php if (!empty($product['is_featured'])): ?><em class="pill-status is-feature">Featured</em><?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No marketplace items yet. Create your first theme, plugin or service above and it will appear here with its database ID.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Membership Plans'): ?>
            <?php
            $intervals = $intervals ?? \App\Models\MembershipPlanRepository::INTERVALS;
            $membershipPlans = $membershipPlans ?? [];
            $editPlan = null;
            $editId = (int) ($_GET['edit'] ?? 0);
            if ($editId > 0) {
                foreach ($membershipPlans as $candidate) {
                    if ((int) $candidate['id'] === $editId) {
                        $editPlan = $candidate;
                        break;
                    }
                }
            }
            $intervalLabels = [
                'one_time' => 'One-time',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
                'quarterly' => 'Quarterly',
                'yearly' => 'Yearly',
            ];
            ?>
            <section class="cyber-card pro-test-account">
                <div class="section-heading compact">
                    <span class="status-chip"><span></span> Testing</span>
                    <h2>Pro Test Account</h2>
                </div>
                <p>Create a Growth Lab <strong>Pro</strong> member so you can sign in at <a href="/account" target="_blank" rel="noopener">/account</a> and test every tool with unlimited scans, white-label reports and saved reports. Choose any password — it is never stored in code. Delete or downgrade this account before you launch publicly.</p>
                <form class="cyber-form" method="post" action="/admin/test-member">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <div class="form-grid two">
                        <label>Name <input name="name" value="Pro Tester"></label>
                        <label>Email (username) <input name="email" type="email" value="pro@crestwebmedia.com" required></label>
                    </div>
                    <label>Password <small>(min 10 characters — you choose it)</small>
                        <input name="password" type="text" minlength="10" placeholder="Type a strong password you'll remember" required></label>
                    <button class="pill-button" type="submit">Create / Refresh Pro Test Login <i class="fa-solid fa-user-shield"></i></button>
                </form>
            </section>
            <section class="split-section membership-admin">
                <form class="cyber-form membership-plan-form" method="post" action="/admin/membership/plans">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= e((string) ($editPlan['id'] ?? '')) ?>">
                    <h2><?= $editPlan ? 'Edit Plan: ' . e($editPlan['name']) : 'Create Membership Plan' ?></h2>
                    <p>Control every commercial attribute of a membership tier. Connect Stripe and PayPal by pasting a hosted payment/subscribe link, a Stripe Price ID or a PayPal Plan ID.</p>
                    <div class="form-grid two">
                        <label>Plan Name <input name="name" required value="<?= e($editPlan['name'] ?? '') ?>" placeholder="Growth Lab Pass"></label>
                        <label>Slug <input name="slug" value="<?= e($editPlan['slug'] ?? '') ?>" placeholder="growth-lab-pass"></label>
                    </div>
                    <label>Tagline <input name="tagline" maxlength="255" value="<?= e($editPlan['tagline'] ?? '') ?>" placeholder="Unlimited tools + premium downloads"></label>
                    <div class="form-grid two">
                        <label>Price <input name="price" inputmode="decimal" value="<?= e($editPlan ? number_format(((int) $editPlan['price_cents']) / 100, 2) : '19.00') ?>"></label>
                        <label>Currency <input name="currency" maxlength="3" value="<?= e($editPlan['currency'] ?? 'USD') ?>"></label>
                    </div>
                    <div class="form-grid two">
                        <label>Billing Interval
                            <select name="billing_interval">
                                <?php foreach ($intervals as $interval): ?>
                                    <option value="<?= e($interval) ?>" <?= ($editPlan['billing_interval'] ?? 'monthly') === $interval ? 'selected' : '' ?>><?= e($intervalLabels[$interval] ?? $interval) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Free Trial (days) <input name="trial_days" inputmode="numeric" value="<?= e((string) ($editPlan['trial_days'] ?? 0)) ?>"></label>
                    </div>
                    <h2>Stripe</h2>
                    <div class="form-grid two">
                        <label>Stripe Price ID <input name="stripe_price_id" value="<?= e($editPlan['stripe_price_id'] ?? '') ?>" placeholder="price_123"></label>
                        <label>Stripe Payment Link <input name="stripe_payment_link" type="url" value="<?= e($editPlan['stripe_payment_link'] ?? '') ?>" placeholder="https://buy.stripe.com/..."></label>
                    </div>
                    <h2>PayPal</h2>
                    <div class="form-grid two">
                        <label>PayPal Plan ID <input name="paypal_plan_id" value="<?= e($editPlan['paypal_plan_id'] ?? '') ?>" placeholder="P-XXXXXXXX"></label>
                        <label>PayPal Subscribe URL <input name="paypal_subscribe_url" type="url" value="<?= e($editPlan['paypal_subscribe_url'] ?? '') ?>" placeholder="https://www.paypal.com/webapps/billing/..."></label>
                    </div>
                    <label>Features <span class="cyber-hint">one per line</span>
                        <textarea name="features" rows="6" placeholder="Unlimited security &amp; SEO scans&#10;All premium themes &amp; plugins&#10;Priority build support"><?= e(!empty($editPlan['features']) ? implode("\n", $editPlan['features']) : '') ?></textarea>
                    </label>
                    <div class="form-grid two">
                        <label>Badge <input name="badge" maxlength="60" value="<?= e($editPlan['badge'] ?? '') ?>" placeholder="Most popular"></label>
                        <label>CTA Label <input name="cta_label" maxlength="80" value="<?= e($editPlan['cta_label'] ?? 'Get started') ?>"></label>
                    </div>
                    <label>Sort Order <input name="sort_order" inputmode="numeric" value="<?= e((string) ($editPlan['sort_order'] ?? 100)) ?>"></label>
                    <div class="checkbox-row">
                        <label><input type="checkbox" name="is_active" value="1" <?= (!$editPlan || !empty($editPlan['is_active'])) ? 'checked' : '' ?>> Active</label>
                        <label><input type="checkbox" name="is_featured" value="1" <?= !empty($editPlan['is_featured']) ? 'checked' : '' ?>> Highlighted / featured</label>
                    </div>
                    <button class="pill-button" type="submit"><?= $editPlan ? 'Update Plan' : 'Create Plan' ?> <i class="fa-solid fa-floppy-disk"></i></button>
                    <?php if ($editPlan): ?><a class="pill-button ghost" href="/admin/modules/membership">Cancel edit</a><?php endif; ?>
                </form>

                <aside class="cyber-card membership-guidance">
                    <h2>How members pay</h2>
                    <ul class="check-list">
                        <li><strong>Stripe:</strong> create a recurring Price in Stripe, paste the <code>price_...</code> ID, or paste a Stripe Payment Link for instant checkout.</li>
                        <li><strong>PayPal:</strong> create a subscription plan, paste the Plan ID and the hosted subscribe URL.</li>
                        <li>The public <a href="/membership" target="_blank" rel="noopener">/membership</a> page shows every active plan with Stripe and PayPal buttons.</li>
                        <li>Leave a gateway blank to hide that button for a plan.</li>
                        <li>Stripe webhook: <code>/webhooks/stripe</code> &nbsp; PayPal webhook: <code>/webhooks/paypal</code>.</li>
                    </ul>
                </aside>
            </section>

            <section class="cyber-card membership-plan-list">
                <div class="section-heading compact">
                    <span class="status-chip"><span></span> Live plans</span>
                    <h2>Membership Tiers</h2>
                </div>
                <?php if (!empty($membershipPlans)): ?>
                    <div class="membership-plan-grid">
                        <?php foreach ($membershipPlans as $plan): ?>
                            <article class="membership-plan-item <?= empty($plan['is_active']) ? 'is-paused' : '' ?>">
                                <header>
                                    <div>
                                        <strong><?= e($plan['name']) ?></strong>
                                        <small><?= e($plan['slug']) ?></small>
                                    </div>
                                    <span class="membership-price">
                                        <?= e('$' . number_format(((int) $plan['price_cents']) / 100, 2)) ?>
                                        <em><?= e($intervalLabels[$plan['billing_interval']] ?? $plan['billing_interval']) ?></em>
                                    </span>
                                </header>
                                <div class="membership-plan-flags">
                                    <em class="pill-status <?= !empty($plan['is_active']) ? 'is-live' : 'is-paused' ?>"><?= !empty($plan['is_active']) ? 'Active' : 'Paused' ?></em>
                                    <?php if (!empty($plan['is_featured'])): ?><em class="pill-status is-feature">Featured</em><?php endif; ?>
                                    <?php if (!empty($plan['stripe_price_id']) || !empty($plan['stripe_payment_link'])): ?><em class="pill-status is-gw">Stripe</em><?php endif; ?>
                                    <?php if (!empty($plan['paypal_plan_id']) || !empty($plan['paypal_subscribe_url'])): ?><em class="pill-status is-gw">PayPal</em><?php endif; ?>
                                </div>
                                <div class="membership-plan-actions">
                                    <a class="pill-button ghost" href="/admin/modules/membership?edit=<?= e((string) $plan['id']) ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <form method="post" action="/admin/membership/plans/toggle">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="id" value="<?= e((string) $plan['id']) ?>">
                                        <input type="hidden" name="state" value="<?= !empty($plan['is_active']) ? 'pause' : 'activate' ?>">
                                        <button class="pill-button ghost" type="submit"><?= !empty($plan['is_active']) ? 'Pause' : 'Activate' ?></button>
                                    </form>
                                    <form method="post" action="/admin/membership/plans/delete" onsubmit="return confirm('Delete this plan? This cannot be undone.');">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="id" value="<?= e((string) $plan['id']) ?>">
                                        <button class="pill-button ghost danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No membership plans yet. Create your first plan on the left — it will appear on the public membership page instantly once active.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</section>

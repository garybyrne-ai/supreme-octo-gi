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
    'members' => ['title' => 'Member Manager', 'icon' => 'fa-users-gear'],
    'clients' => ['title' => 'Clients', 'icon' => 'fa-handshake'],
    'referrals' => ['title' => 'Referrals', 'icon' => 'fa-share-nodes'],
    'work-log' => ['title' => 'Client Work Log', 'icon' => 'fa-clipboard-list'],
    'abandoned-orders' => ['title' => 'Abandoned Orders', 'icon' => 'fa-cart-arrow-down'],
    'coupons' => ['title' => 'Coupons', 'icon' => 'fa-tags'],
    'ads' => ['title' => 'Ads & Consent', 'icon' => 'fa-rectangle-ad'],
    'search-console' => ['title' => 'Search Console', 'icon' => 'fa-magnifying-glass-chart'],
    'ai-settings' => ['title' => 'AI Assistant', 'icon' => 'fa-robot'],
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

// Modules that ship a real editor (lists + forms) below. For these we show the
// editor first and hide the generic "console / save-action / action-log"
// scaffolding, which otherwise buries the real UI and looks like the whole page.
$modulesWithRealUi = [
    'Media Library', 'Support Tickets', 'Forum Members', 'Member Manager', 'Clients',
    'Site Content', 'Ads & Consent', 'Newsletter Offer', 'Mail Settings', 'Payment Settings',
    'Commerce Engine', 'Membership Plans', 'Coupons', 'Theme Settings',
    'Clients', 'Search Console', 'Referrals', 'Client Work Log', 'Abandoned Orders', 'AI Assistant',
    'Services Manager', 'Testimonials', 'FAQ Manager', 'Blog Manager', 'Portfolio Manager',
    'SEO Center', 'Redirect Manager', 'Activity Logs', 'Backups', 'Analytics',
];
$hasRealUi = in_array($module['title'] ?? '', $modulesWithRealUi, true);
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

        <?php if (!empty($notice)): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

        <?php if (!$hasRealUi): ?>
        <section class="admin-module-detail cyber-card">
            <span class="module-orbit large"><i class="fa-solid <?= e($module['icon']) ?>"></i></span>
            <div>
                <h2><?= e($module['title']) ?> Console</h2>
                <p>This module doesn't have a dedicated editor yet — use the action panel below to log the update you want, and it will be built into a full editor next.</p>
            </div>
            <div class="module-action-grid">
                <?php foreach ($module['actions'] as $index => $action): ?>
                    <button class="<?= $index === 0 ? 'is-active' : '' ?>" type="button" data-admin-action-target="#module-action-<?= e((string) $index) ?>">
                        <i class="fa-solid fa-bolt"></i><?= e($action) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

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
        <?php endif; /* !$hasRealUi */ ?>

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

        <?php if (($module['title'] ?? '') === 'Member Manager'): ?>
            <?php $siteMembers = $siteMembers ?? []; ?>
            <section class="cyber-card member-manager">
                <div class="section-heading compact">
                    <span class="status-chip"><span></span> Members</span>
                    <h2>Member Manager</h2>
                </div>
                <p>Edit any member, upgrade or downgrade their Growth Lab Pro access and pick exactly when it ends using the calendar. Leave the expiry blank for lifetime Pro. Downgrading to Free removes tool Pro access immediately.</p>
                <?php if (!empty($siteMembers)): ?>
                    <div class="member-card-grid">
                        <?php foreach ($siteMembers as $sm):
                            $membership = is_array($sm['membership'] ?? null) ? $sm['membership'] : [];
                            $isPro = \App\Models\MemberRepository::isPro($sm);
                            $endsAt = (string) ($membership['current_period_ends_at'] ?? '');
                            $endsInput = $endsAt !== '' ? date('Y-m-d\TH:i', (int) strtotime($endsAt)) : '';
                            $endsLabel = $endsAt !== '' ? date('j M Y, H:i', (int) strtotime($endsAt)) : 'Lifetime';
                        ?>
                            <article class="member-card <?= $isPro ? 'is-pro' : 'is-free' ?>">
                                <header class="member-card-head">
                                    <div>
                                        <strong><?= e($sm['name'] ?? 'Member') ?></strong>
                                        <small><?= e($sm['email'] ?? '') ?></small>
                                    </div>
                                    <span class="member-tier <?= $isPro ? 'tier-pro' : 'tier-free' ?>">
                                        <?= $isPro ? 'PRO' : 'FREE' ?>
                                    </span>
                                </header>
                                <p class="member-meta">
                                    <?php if ($isPro): ?>
                                        <i class="fa-solid fa-circle-check"></i> Pro until <strong><?= e($endsLabel) ?></strong>
                                    <?php elseif (!empty($membership)): ?>
                                        <i class="fa-solid fa-clock"></i> Pro expired / cancelled
                                    <?php else: ?>
                                        <i class="fa-regular fa-circle"></i> Free account
                                    <?php endif; ?>
                                    <?php if (!empty($sm['forum_verified'])): ?><span class="member-flag">Forum approved</span><?php endif; ?>
                                </p>
                                <form class="cyber-form member-edit-form" method="post" action="/admin/members/save">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="member_id" value="<?= e($sm['id'] ?? '') ?>">
                                    <div class="form-grid two">
                                        <label>Name <input name="name" value="<?= e($sm['name'] ?? '') ?>"></label>
                                        <label>Email <input name="email" type="email" value="<?= e($sm['email'] ?? '') ?>"></label>
                                    </div>
                                    <div class="form-grid two">
                                        <label>Membership
                                            <select name="plan" data-member-plan>
                                                <option value="pro" <?= $isPro ? 'selected' : '' ?>>Pro (Growth Lab)</option>
                                                <option value="free" <?= $isPro ? '' : 'selected' ?>>Free</option>
                                            </select>
                                        </label>
                                        <label>Pro access ends <small>(calendar — blank = lifetime)</small>
                                            <input name="expires_at" type="datetime-local" value="<?= e($endsInput) ?>">
                                        </label>
                                    </div>
                                    <label>Reset password <small>(leave blank to keep current — min 10 chars)</small>
                                        <input name="password" type="text" autocomplete="off" placeholder="Type a new password to reset it">
                                    </label>
                                    <label class="member-check"><input type="checkbox" name="forum_verified" value="1" <?= !empty($sm['forum_verified']) ? 'checked' : '' ?>> Allow forum posting</label>
                                    <div class="member-card-actions">
                                        <button class="pill-button" type="submit">Save Member <i class="fa-solid fa-floppy-disk"></i></button>
                                    </div>
                                </form>
                                <form method="post" action="/admin/members/delete" class="member-delete-form" onsubmit="return confirm('Delete this member permanently? This cannot be undone.');">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="member_id" value="<?= e($sm['id'] ?? '') ?>">
                                    <button class="pill-button ghost danger" type="submit"><i class="fa-solid fa-trash"></i> Delete member</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No members yet. When people register at <a href="/account/forms" target="_blank" rel="noopener">/account</a> they will appear here, and you can also create one from the <a href="/admin/modules/membership">Pro Test Account</a> tool.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Clients'): ?>
            <?php $clientList = $clientList ?? []; ?>
            <section class="split-section">
                <form class="cyber-form client-form" method="post" action="/admin/clients">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="original_slug" value="" data-client-original-slug>
                    <h2>Client / Case Study</h2>
                    <p>Shown on the home "Our Clients" wall (public) and the gated portfolio (logged-in). Upload the official logo in <a href="/admin/modules/media">Media Library</a>, then paste its path below.</p>
                    <div class="form-grid two">
                        <label>Name <input name="name" data-client-field="name" required></label>
                        <label>Slug <small>(blank = auto)</small> <input name="slug" data-client-field="slug"></label>
                        <label>Logo image path <input name="logo" data-client-field="logo" placeholder="/assets/images/clients/name.svg"></label>
                        <label>Website URL <small>(optional)</small> <input name="url" data-client-field="url"></label>
                        <label>Industry <input name="industry" data-client-field="industry" placeholder="Beauty & Wellness"></label>
                        <label>Sort order <input name="sort_order" type="number" data-client-field="sort_order" value="100"></label>
                    </div>
                    <label>Services <small>(one per line)</small> <textarea name="services" rows="3" data-client-field="services" placeholder="Website Build&#10;Local SEO&#10;Booking Automation"></textarea></label>
                    <label>Summary <small>(one line, shown on cards)</small> <textarea name="summary" rows="2" data-client-field="summary"></textarea></label>
                    <label>The challenge <textarea name="challenge" rows="3" data-client-field="challenge"></textarea></label>
                    <label>What we did <textarea name="work" rows="3" data-client-field="work"></textarea></label>
                    <label>Results <small>(one per line)</small> <textarea name="results" rows="3" data-client-field="results"></textarea></label>
                    <div class="checkbox-row">
                        <label><input type="checkbox" name="active" value="1" checked data-client-field="active"> Active (shown on site)</label>
                        <label><input type="checkbox" name="featured" value="1" data-client-field="featured"> Featured</label>
                    </div>
                    <button class="pill-button" type="submit">Save Client <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <section class="cyber-card membership-plan-list">
                    <div class="section-heading compact">
                        <span class="status-chip"><span></span> Showcase</span>
                        <h2>Clients</h2>
                    </div>
                    <?php if (!empty($clientList)): ?>
                        <div class="membership-plan-grid">
                            <?php foreach ($clientList as $cl): ?>
                                <article class="membership-plan-item <?= empty($cl['active']) ? 'is-paused' : '' ?>">
                                    <header>
                                        <div>
                                            <strong><?= e($cl['name']) ?></strong>
                                            <small><?= e($cl['industry']) ?></small>
                                        </div>
                                        <?php if (!empty($cl['logo'])): ?><img src="<?= e($cl['logo']) ?>" alt="" style="max-width:96px;max-height:34px;background:#fff;border-radius:6px;padding:3px"><?php endif; ?>
                                    </header>
                                    <div class="membership-plan-flags">
                                        <em class="pill-status <?= !empty($cl['active']) ? 'is-live' : 'is-paused' ?>"><?= !empty($cl['active']) ? 'Shown' : 'Hidden' ?></em>
                                        <?php if (!empty($cl['featured'])): ?><em class="pill-status is-feature">Featured</em><?php endif; ?>
                                    </div>
                                    <div class="membership-plan-actions">
                                        <button type="button" class="pill-button ghost" data-edit-client='<?= e(json_encode($cl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>'><i class="fa-solid fa-pen"></i> Edit</button>
                                        <form method="post" action="/admin/clients/state">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="slug" value="<?= e($cl['slug']) ?>">
                                            <input type="hidden" name="state" value="<?= !empty($cl['active']) ? 'deactivate' : 'activate' ?>">
                                            <button class="pill-button ghost" type="submit"><?= !empty($cl['active']) ? 'Hide' : 'Show' ?></button>
                                        </form>
                                        <form method="post" action="/admin/clients/state" onsubmit="return confirm('Delete this client?');">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="slug" value="<?= e($cl['slug']) ?>">
                                            <input type="hidden" name="state" value="delete">
                                            <button class="pill-button ghost danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No clients yet. Add your first one on the left.</p>
                    <?php endif; ?>
                </section>
            </section>
            <script>
            (function () {
                var form = document.querySelector('.client-form');
                if (!form) { return; }
                document.querySelectorAll('[data-edit-client]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var c = JSON.parse(btn.getAttribute('data-edit-client'));
                        var set = function (n, v) { var el = form.querySelector('[data-client-field="' + n + '"]'); if (el) { el.value = v; } };
                        set('name', c.name || ''); set('slug', c.slug || ''); set('logo', c.logo || '');
                        set('url', c.url || ''); set('industry', c.industry || ''); set('sort_order', c.sort_order || 100);
                        set('services', (c.services || []).join('\n')); set('summary', c.summary || '');
                        set('challenge', c.challenge || ''); set('work', c.work || ''); set('results', (c.results || []).join('\n'));
                        form.querySelector('[data-client-field="active"]').checked = !!c.active;
                        form.querySelector('[data-client-field="featured"]').checked = !!c.featured;
                        form.querySelector('[data-client-original-slug]').value = c.slug || '';
                        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                });
            })();
            </script>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Ads & Consent'): ?>
            <?php $adsSettings = $adsSettings ?? []; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/ads-settings">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Google AdSense &amp; Analytics</h2>
                    <p>Ads and analytics only load <strong>after</strong> a visitor accepts cookies (Google Consent Mode v2), so the site stays GDPR-compliant. Leave blank to keep them off.</p>
                    <label>AdSense Publisher ID <small>(from AdSense → Account → looks like ca-pub-…)</small>
                        <input name="adsense_client" value="<?= e($adsSettings['adsense_client'] ?? '') ?>" placeholder="ca-pub-1234567890123456"></label>
                    <label>Google Analytics 4 ID <small>(optional — looks like G-XXXXXXXXXX)</small>
                        <input name="analytics_id" value="<?= e($adsSettings['analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX"></label>
                    <label class="checkbox-row"><input type="checkbox" name="ads_enabled" value="1" <?= !empty($adsSettings['ads_enabled']) ? 'checked' : '' ?>> Enable ads on the site (needs a valid AdSense ID above)</label>
                    <label>Cookie consent message <small>(shown in the banner)</small>
                        <textarea name="consent_message" rows="3"><?= e($adsSettings['consent_message'] ?? '') ?></textarea></label>
                    <button class="pill-button" type="submit">Save Ads &amp; Consent <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <div class="cyber-card">
                    <h2>ads.txt</h2>
                    <p>Served live at <a href="/ads.txt" target="_blank" rel="noopener">/ads.txt</a>. AdSense requires this file with your publisher line — copy it from AdSense → Sites → "Get code".</p>
                    <form class="cyber-form" method="post" action="/admin/ads-settings">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="adsense_client" value="<?= e($adsSettings['adsense_client'] ?? '') ?>">
                        <input type="hidden" name="analytics_id" value="<?= e($adsSettings['analytics_id'] ?? '') ?>">
                        <input type="hidden" name="ads_enabled" value="<?= !empty($adsSettings['ads_enabled']) ? '1' : '' ?>">
                        <input type="hidden" name="consent_message" value="<?= e($adsSettings['consent_message'] ?? '') ?>">
                        <label>ads.txt contents
                            <textarea name="ads_txt" rows="7" style="font-family:monospace"><?= e($adsSettings['ads_txt'] ?? '') ?></textarea></label>
                        <button class="pill-button ghost" type="submit">Save ads.txt <i class="fa-solid fa-floppy-disk"></i></button>
                    </form>
                    <p><small><i class="fa-solid fa-circle-info"></i> AdSense also wants you to place ad units in your pages. Once approved, paste the ad-unit snippet into <a href="/admin/modules/theme">Theme Settings → Script injections</a> or tell me where you want ads and I'll wire the slots.</small></p>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Client Work Log'): ?>
            <?php $workLog = $workLog ?? []; $workLogTypes = $workLogTypes ?? \App\Models\ClientPortalRepository::TYPES; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/work-log">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Post a client update</h2>
                    <p>The client sees this on their logged-in dashboard (they must have registered with this email). Great for care-plan clients: log work done, attach a report link, note a backup.</p>
                    <div class="form-grid two">
                        <label>Client email <input name="email" type="email" required placeholder="client@business.ie"></label>
                        <label>Type
                            <select name="type">
                                <?php foreach ($workLogTypes as $t): ?><option value="<?= e($t) ?>"><?= e(ucfirst($t)) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label>Date <input name="date" type="date"></label>
                        <label>Report / file link <small>(optional)</small> <input name="link" placeholder="https://…"></label>
                    </div>
                    <label>Title <input name="title" required placeholder="Monthly maintenance completed"></label>
                    <label>Note <textarea name="note" rows="4" placeholder="What was done, findings, next steps…"></textarea></label>
                    <button class="pill-button" type="submit">Post Update <i class="fa-solid fa-paper-plane"></i></button>
                </form>
                <section class="cyber-card ticket-table-card">
                    <h2>Recent entries</h2>
                    <?php if (!empty($workLog)): ?>
                        <div class="ticket-table full">
                            <?php foreach ($workLog as $entry): ?>
                                <div>
                                    <strong><?= e($entry['title'] ?? '') ?></strong>
                                    <span><?= e($entry['email'] ?? '') ?><small><?= e(ucfirst((string) ($entry['type'] ?? 'update'))) ?> · <?= e((string) ($entry['date'] ?? '')) ?></small></span>
                                    <form method="post" action="/admin/work-log/delete" onsubmit="return confirm('Delete this entry?');">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="email" value="<?= e($entry['email'] ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= e($entry['id'] ?? '') ?>">
                                        <button class="pill-button ghost small danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No entries yet. Post your first client update on the left.</p>
                    <?php endif; ?>
                </section>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'SEO Center'): ?>
            <?php $seoSettings = $seoSettings ?? []; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/seo-settings">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Global SEO &amp; social</h2>
                    <p>These apply site-wide. Per-page titles and descriptions are still set on each page; these fill the gaps and control social sharing, verification and schema.</p>
                    <label>Default meta description <small>(used when a page has none)</small>
                        <textarea name="default_meta_description" rows="2"><?= e($seoSettings['default_meta_description'] ?? '') ?></textarea></label>
                    <label>Social share image URL <small>(Open Graph / Twitter — 1200×630 works best)</small>
                        <input name="og_image" type="url" value="<?= e($seoSettings['og_image'] ?? '') ?>" placeholder="https://www.crestwebmedia.com/assets/images/og.png"></label>
                    <label>Twitter / X handle <input name="twitter_site" value="<?= e($seoSettings['twitter_site'] ?? '') ?>" placeholder="@crestwebmedia"></label>
                    <div class="form-grid two">
                        <label>Google verification <small>(content value)</small><input name="google_verification" value="<?= e($seoSettings['google_verification'] ?? '') ?>"></label>
                        <label>Bing verification <small>(msvalidate.01)</small><input name="bing_verification" value="<?= e($seoSettings['bing_verification'] ?? '') ?>"></label>
                    </div>
                    <label class="member-check"><input type="checkbox" name="allow_indexing" value="1" <?= !empty($seoSettings['allow_indexing']) ? 'checked' : '' ?>> Allow search engines to index the site <small>(uncheck for staging)</small></label>
                    <button class="pill-button" type="submit">Save SEO Settings <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <form class="cyber-form" method="post" action="/admin/seo-settings">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="default_meta_description" value="<?= e($seoSettings['default_meta_description'] ?? '') ?>">
                    <input type="hidden" name="og_image" value="<?= e($seoSettings['og_image'] ?? '') ?>">
                    <input type="hidden" name="twitter_site" value="<?= e($seoSettings['twitter_site'] ?? '') ?>">
                    <input type="hidden" name="google_verification" value="<?= e($seoSettings['google_verification'] ?? '') ?>">
                    <input type="hidden" name="bing_verification" value="<?= e($seoSettings['bing_verification'] ?? '') ?>">
                    <input type="hidden" name="allow_indexing" value="<?= !empty($seoSettings['allow_indexing']) ? '1' : '' ?>">
                    <h2>Organization &amp; profiles</h2>
                    <p>Feeds your Organization schema (rich results) and the <code>sameAs</code> links search engines use to connect your brand.</p>
                    <div class="form-grid two">
                        <label>Organization name <input name="organization_name" value="<?= e($seoSettings['organization_name'] ?? '') ?>" placeholder="Crest Web Media"></label>
                        <label>Logo URL <input name="organization_logo" type="url" value="<?= e($seoSettings['organization_logo'] ?? '') ?>"></label>
                    </div>
                    <div class="form-grid two">
                        <label>LinkedIn URL <input name="social_linkedin" type="url" value="<?= e($seoSettings['social_linkedin'] ?? '') ?>"></label>
                        <label>GitHub URL <input name="social_github" type="url" value="<?= e($seoSettings['social_github'] ?? '') ?>"></label>
                    </div>
                    <div class="form-grid two">
                        <label>Facebook URL <input name="social_facebook" type="url" value="<?= e($seoSettings['social_facebook'] ?? '') ?>"></label>
                        <label>X / Twitter URL <input name="social_x" type="url" value="<?= e($seoSettings['social_x'] ?? '') ?>"></label>
                    </div>
                    <button class="pill-button" type="submit">Save Organization <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Redirect Manager'): ?>
            <?php $redirectsList = $redirectsList ?? []; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content-item/save">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="type" value="redirect">
                    <h2>Add a redirect</h2>
                    <p>Send an old or changed URL to a new one. Applied before routing on every request.</p>
                    <label>From path <small>(on this site, e.g. <code>/old-page</code>)</small><input name="from" required placeholder="/old-page"></label>
                    <label>To <small>(a path like <code>/new-page</code> or a full URL)</small><input name="to" required placeholder="/new-page"></label>
                    <label>Type
                        <select name="status">
                            <option value="301">301 — Permanent</option>
                            <option value="302">302 — Temporary</option>
                            <option value="307">307 — Temporary (keep method)</option>
                            <option value="308">308 — Permanent (keep method)</option>
                        </select>
                    </label>
                    <button class="pill-button" type="submit">Add Redirect <i class="fa-solid fa-plus"></i></button>
                </form>
                <div class="cyber-card admin-editable-panel">
                    <h2>Redirects (<?= count($redirectsList) ?>)</h2>
                    <?php if (empty($redirectsList)): ?>
                        <p>No redirects yet. Add one on the left — for example after renaming a page or changing a URL structure.</p>
                    <?php else: ?>
                        <div class="admin-editable-list">
                            <?php foreach ($redirectsList as $rd): ?>
                                <div class="admin-edit-item" style="padding:12px 16px">
                                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                                        <code><?= e($rd['from'] ?? '') ?></code> <i class="fa-solid fa-arrow-right" style="color:var(--primary)"></i> <code><?= e($rd['to'] ?? '') ?></code>
                                        <span class="portal-badge"><?= e((string) ($rd['status'] ?? 301)) ?></span>
                                        <form method="post" action="/admin/content-item/delete" onsubmit="return confirm('Delete this redirect?');" style="margin-left:auto">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="redirect"><input type="hidden" name="id" value="<?= e($rd['id'] ?? '') ?>">
                                            <button type="submit" class="serp-tr-remove" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Analytics'): ?>
            <?php $analytics = $analytics ?? []; ?>
            <section class="cyber-card">
                <div class="section-heading compact"><span class="status-chip"><span></span> Live</span><h2>Site metrics</h2></div>
                <p>Real numbers from your own data. <?= !empty($analytics['ga4_configured']) ? 'Google Analytics 4 is connected for traffic analytics.' : 'Add a Google Analytics 4 ID in <a href="/admin/modules/ads">Ads &amp; Consent</a> for full traffic analytics.' ?></p>
                <div class="serp-metrics">
                    <div class="serp-metric"><span>Members</span><strong><?= (int) ($analytics['members_total'] ?? 0) ?></strong></div>
                    <div class="serp-metric tier-excellent"><span>Pro members</span><strong><?= (int) ($analytics['members_pro'] ?? 0) ?></strong></div>
                    <div class="serp-metric"><span>Free members</span><strong><?= (int) ($analytics['members_free'] ?? 0) ?></strong></div>
                    <div class="serp-metric"><span>Tool leads</span><strong><?= (int) ($analytics['tool_leads'] ?? 0) ?></strong></div>
                    <div class="serp-metric"><span>Referrals</span><strong><?= (int) ($analytics['referrals'] ?? 0) ?></strong></div>
                    <div class="serp-metric"><span>Open tickets</span><strong><?= (int) ($analytics['open_tickets'] ?? 0) ?></strong></div>
                    <div class="serp-metric"><span>Blog posts</span><strong><?= (int) ($analytics['blog_posts'] ?? 0) ?></strong></div>
                    <div class="serp-metric"><span>Activity events</span><strong><?= (int) ($analytics['activity_events'] ?? 0) ?></strong></div>
                </div>
                <h3 style="margin:18px 0 10px">Checkout recovery</h3>
                <div class="serp-metrics">
                    <div class="serp-metric"><span>Started</span><strong><?= (int) ($analytics['orders_started'] ?? 0) ?></strong></div>
                    <div class="serp-metric"><span>Recovery emailed</span><strong><?= (int) ($analytics['orders_recovered'] ?? 0) ?></strong></div>
                    <div class="serp-metric tier-excellent"><span>Paid</span><strong><?= (int) ($analytics['orders_paid'] ?? 0) ?></strong></div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Activity Logs'): ?>
            <?php $activityLog = $activityLog ?? []; ?>
            <section class="cyber-card ticket-table-card">
                <div class="section-heading compact"><span class="status-chip"><span></span> Audit</span><h2>Recent activity</h2></div>
                <p>The most recent system and admin events (logins, saves, orders, payments). Read-only.</p>
                <?php if (empty($activityLog)): ?>
                    <p>No activity recorded yet.</p>
                <?php else: ?>
                    <div class="ticket-table full">
                        <?php foreach ($activityLog as $ev): ?>
                            <div>
                                <strong><?= e($ev['event'] ?? '') ?></strong>
                                <span><?php $ctx = $ev['context'] ?? []; echo is_array($ctx) && $ctx !== [] ? e(excerpt(json_encode($ctx, JSON_UNESCAPED_SLASHES), 90)) : '<small>—</small>'; ?></span>
                                <small><?= e(!empty($ev['timestamp']) ? gmdate('j M Y, H:i', (int) strtotime((string) $ev['timestamp'])) : '') ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Backups'): ?>
            <?php $backupInventory = $backupInventory ?? []; $backupTotal = $backupTotal ?? 0; ?>
            <section class="split-section">
                <div class="cyber-card">
                    <div class="section-heading compact"><span class="status-chip"><span></span> Export</span><h2>Download a backup</h2></div>
                    <p>Bundles all your editable content and settings (members, blog, services, portfolio, orders, settings…) into a single JSON snapshot you can save off-site. Total data: <strong><?= number_format($backupTotal / 1024, 1) ?> KB</strong>.</p>
                    <form method="post" action="/admin/backup/download">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button class="pill-button" type="submit"><i class="fa-solid fa-download"></i> Download backup snapshot</button>
                    </form>
                    <p><small><i class="fa-solid fa-circle-info"></i> Store the file somewhere safe. To restore, keep the file — restore tooling can be added on request, or the JSON files can be placed back into <code>storage/data</code>.</small></p>
                </div>
                <div class="cyber-card admin-editable-panel">
                    <h2>Data files (<?= count($backupInventory) ?>)</h2>
                    <?php if (empty($backupInventory)): ?>
                        <p>No stored data files yet — they are created as you use the CMS.</p>
                    <?php else: ?>
                        <div class="ticket-table full">
                            <?php foreach ($backupInventory as $file): ?>
                                <div>
                                    <strong><?= e($file['name']) ?></strong>
                                    <span><?= number_format($file['size'] / 1024, 1) ?> KB</span>
                                    <small><?= e($file['modified']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Blog Manager'): ?>
            <?php $blogList = $blogList ?? []; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content-item/save">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="type" value="blog">
                    <h2>Write a new post</h2>
                    <p>Posts appear on <a href="/blog" target="_blank" rel="noopener">/blog</a> at <code>/blog/{slug}</code> with full Article + FAQ schema.</p>
                    <div class="form-grid two">
                        <label>Title <input name="title" required></label>
                        <label>Slug <small>(auto from title)</small><input name="slug"></label>
                    </div>
                    <div class="form-grid two">
                        <label>Category <input name="category" placeholder="SEO, Security, Web Design…"></label>
                        <label>Publish date <input name="published_at" type="date"></label>
                    </div>
                    <label>Excerpt <small>(short summary for cards &amp; meta)</small><textarea name="excerpt" rows="2"></textarea></label>
                    <label>Focus keyword <input name="focus_keyword"></label>
                    <label>Content <small>(use <code>## Heading</code> to start a section; blank line = new paragraph)</small>
                        <textarea name="content" rows="12" placeholder="## The quick answer&#10;&#10;First paragraph…&#10;&#10;Second paragraph…&#10;&#10;## Next section&#10;&#10;More text…"></textarea></label>
                    <label>Checklist <small>(one item per line — optional)</small><textarea name="checklist" rows="4"></textarea></label>
                    <label>FAQ <small>(one per line as <code>Question || Answer</code> — optional)</small><textarea name="faq" rows="4"></textarea></label>
                    <button class="pill-button" type="submit">Publish Post <i class="fa-solid fa-plus"></i></button>
                </form>
                <div class="cyber-card admin-editable-panel">
                    <h2>Posts (<?= count($blogList) ?>)</h2>
                    <p>Click a post to edit its full content. Newest edits go live immediately.</p>
                    <div class="admin-editable-list">
                        <?php foreach ($blogList as $post): ?>
                            <details class="admin-edit-item">
                                <summary><i class="fa-solid fa-newspaper"></i> <strong><?= e($post['title'] ?? '') ?></strong><small><?= e($post['category'] ?? '') ?> · <?= e($post['published_at'] ?? '') ?></small></summary>
                                <form class="cyber-form" method="post" action="/admin/content-item/save">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="type" value="blog">
                                    <input type="hidden" name="id" value="<?= e($post['id'] ?? $post['slug'] ?? '') ?>">
                                    <div class="form-grid two">
                                        <label>Title <input name="title" value="<?= e($post['title'] ?? '') ?>" required></label>
                                        <label>Slug <input name="slug" value="<?= e($post['slug'] ?? '') ?>"></label>
                                    </div>
                                    <div class="form-grid two">
                                        <label>Category <input name="category" value="<?= e($post['category'] ?? '') ?>"></label>
                                        <label>Publish date <input name="published_at" type="date" value="<?= e($post['published_at'] ?? '') ?>"></label>
                                    </div>
                                    <label>Excerpt <textarea name="excerpt" rows="2"><?= e($post['excerpt'] ?? '') ?></textarea></label>
                                    <label>Focus keyword <input name="focus_keyword" value="<?= e($post['focus_keyword'] ?? '') ?>"></label>
                                    <label>Content <small>(<code>## Heading</code> starts a section; blank line = new paragraph)</small>
                                        <textarea name="content" rows="14"><?= e(\App\Models\BlogPostRepository::sectionsToText($post['body_sections'] ?? [])) ?></textarea></label>
                                    <label>Checklist <textarea name="checklist" rows="4"><?= e(implode("\n", (array) ($post['checklist'] ?? []))) ?></textarea></label>
                                    <label>FAQ <small>(<code>Question || Answer</code> per line)</small><textarea name="faq" rows="4"><?= e(\App\Models\BlogPostRepository::faqToText($post['faq'] ?? [])) ?></textarea></label>
                                    <div class="admin-item-actions">
                                        <button class="pill-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                                        <a class="pill-button ghost" href="/blog/<?= e($post['slug'] ?? '') ?>" target="_blank" rel="noopener">View <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                    </div>
                                </form>
                                <div class="admin-item-toolbar">
                                    <?php foreach (['up' => 'fa-arrow-up', 'down' => 'fa-arrow-down'] as $dir => $ic): ?>
                                        <form method="post" action="/admin/content-item/move">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="blog"><input type="hidden" name="id" value="<?= e($post['id'] ?? $post['slug'] ?? '') ?>"><input type="hidden" name="dir" value="<?= $dir ?>">
                                            <button type="submit" title="Move <?= $dir ?>"><i class="fa-solid <?= $ic ?>"></i></button>
                                        </form>
                                    <?php endforeach; ?>
                                    <form method="post" action="/admin/content-item/delete" onsubmit="return confirm('Delete this post?');">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="blog"><input type="hidden" name="id" value="<?= e($post['id'] ?? $post['slug'] ?? '') ?>">
                                        <button type="submit" class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Portfolio Manager'): ?>
            <?php $portfolioList = $portfolioList ?? []; $accents = ['blue', 'cyan', 'green', 'orange', 'purple']; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content-item/save">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="type" value="portfolio">
                    <h2>Add a project</h2>
                    <p>Projects shown on the home page and <a href="/portfolio" target="_blank" rel="noopener">/portfolio</a>.</p>
                    <label>Title <input name="title" required></label>
                    <label>Category <input name="category" placeholder="Healthcare Website, E-Commerce Store…"></label>
                    <label>Summary <textarea name="summary" rows="3"></textarea></label>
                    <div class="form-grid two">
                        <label>Accent colour
                            <select name="accent"><?php foreach ($accents as $a): ?><option value="<?= $a ?>"><?= ucfirst($a) ?></option><?php endforeach; ?></select>
                        </label>
                        <label>Live URL <small>(optional)</small><input name="url" type="url" placeholder="https://…"></label>
                    </div>
                    <label>Image filename <small>(optional — upload in Media Library first)</small><input name="image" placeholder="project.webp"></label>
                    <button class="pill-button" type="submit">Add Project <i class="fa-solid fa-plus"></i></button>
                </form>
                <div class="cyber-card admin-editable-panel">
                    <h2>Projects (<?= count($portfolioList) ?>)</h2>
                    <div class="admin-editable-list">
                        <?php foreach ($portfolioList as $pf): ?>
                            <details class="admin-edit-item">
                                <summary><i class="fa-solid fa-layer-group"></i> <strong><?= e($pf['title'] ?? '') ?></strong><small><?= e($pf['category'] ?? '') ?></small></summary>
                                <form class="cyber-form" method="post" action="/admin/content-item/save">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="type" value="portfolio">
                                    <input type="hidden" name="id" value="<?= e($pf['id'] ?? '') ?>">
                                    <label>Title <input name="title" value="<?= e($pf['title'] ?? '') ?>" required></label>
                                    <label>Category <input name="category" value="<?= e($pf['category'] ?? '') ?>"></label>
                                    <label>Summary <textarea name="summary" rows="3"><?= e($pf['summary'] ?? '') ?></textarea></label>
                                    <div class="form-grid two">
                                        <label>Accent colour
                                            <select name="accent"><?php foreach ($accents as $a): ?><option value="<?= $a ?>" <?= ($pf['accent'] ?? '') === $a ? 'selected' : '' ?>><?= ucfirst($a) ?></option><?php endforeach; ?></select>
                                        </label>
                                        <label>Live URL <input name="url" type="url" value="<?= e($pf['url'] ?? '') ?>"></label>
                                    </div>
                                    <label>Image filename <input name="image" value="<?= e($pf['image'] ?? '') ?>"></label>
                                    <div class="admin-item-actions">
                                        <button class="pill-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                                    </div>
                                </form>
                                <div class="admin-item-toolbar">
                                    <?php foreach (['up' => 'fa-arrow-up', 'down' => 'fa-arrow-down'] as $dir => $ic): ?>
                                        <form method="post" action="/admin/content-item/move">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="portfolio"><input type="hidden" name="id" value="<?= e($pf['id'] ?? '') ?>"><input type="hidden" name="dir" value="<?= $dir ?>">
                                            <button type="submit" title="Move <?= $dir ?>"><i class="fa-solid <?= $ic ?>"></i></button>
                                        </form>
                                    <?php endforeach; ?>
                                    <form method="post" action="/admin/content-item/delete" onsubmit="return confirm('Delete this project?');">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="portfolio"><input type="hidden" name="id" value="<?= e($pf['id'] ?? '') ?>">
                                        <button type="submit" class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Services Manager'): ?>
            <?php $servicesList = $servicesList ?? []; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content-item/save">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="type" value="service">
                    <h2>Add a service</h2>
                    <p>Services appear on the home page and the Services page, and each gets its own detail page at <code>/services/{slug}</code>.</p>
                    <label>Title <input name="title" required placeholder="Website Development"></label>
                    <label>Slug <small>(optional — auto-generated from the title)</small>
                        <input name="slug" placeholder="website-development"></label>
                    <label>Summary <textarea name="summary" rows="3" placeholder="One or two sentences describing the service."></textarea></label>
                    <label>Icon <small>(Font Awesome class)</small>
                        <input name="icon" value="fa-solid fa-screwdriver-wrench"></label>
                    <label>Tags <small>(one per line or comma-separated)</small>
                        <textarea name="tags" rows="3" placeholder="PHP 8 Architecture&#10;Custom CMS&#10;Edge-Speed UX"></textarea></label>
                    <button class="pill-button" type="submit">Add Service <i class="fa-solid fa-plus"></i></button>
                </form>
                <div class="cyber-card admin-editable-panel">
                    <h2>Services (<?= count($servicesList) ?>)</h2>
                    <p>Click a service to edit it. Reorder with the arrows; changes go live immediately.</p>
                    <div class="admin-editable-list">
                        <?php foreach ($servicesList as $svc): ?>
                            <details class="admin-edit-item">
                                <summary><i class="<?= e($svc['icon'] ?? 'fa-solid fa-screwdriver-wrench') ?>"></i> <strong><?= e($svc['title'] ?? '') ?></strong><small><?= e($svc['slug'] ?? '') ?></small></summary>
                                <form class="cyber-form" method="post" action="/admin/content-item/save">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="type" value="service">
                                    <input type="hidden" name="id" value="<?= e($svc['id'] ?? '') ?>">
                                    <div class="form-grid two">
                                        <label>Title <input name="title" value="<?= e($svc['title'] ?? '') ?>" required></label>
                                        <label>Slug <input name="slug" value="<?= e($svc['slug'] ?? '') ?>"></label>
                                    </div>
                                    <label>Summary <textarea name="summary" rows="3"><?= e($svc['summary'] ?? '') ?></textarea></label>
                                    <label>Icon <input name="icon" value="<?= e($svc['icon'] ?? '') ?>"></label>
                                    <label>Tags <small>(one per line)</small><textarea name="tags" rows="3"><?= e(implode("\n", (array) ($svc['tags'] ?? []))) ?></textarea></label>
                                    <div class="admin-item-actions">
                                        <button class="pill-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                                    </div>
                                </form>
                                <div class="admin-item-toolbar">
                                    <?php foreach (['up' => 'fa-arrow-up', 'down' => 'fa-arrow-down'] as $dir => $ic): ?>
                                        <form method="post" action="/admin/content-item/move">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="service"><input type="hidden" name="id" value="<?= e($svc['id'] ?? '') ?>"><input type="hidden" name="dir" value="<?= $dir ?>">
                                            <button type="submit" title="Move <?= $dir ?>"><i class="fa-solid <?= $ic ?>"></i></button>
                                        </form>
                                    <?php endforeach; ?>
                                    <form method="post" action="/admin/content-item/delete" onsubmit="return confirm('Delete this service?');">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="service"><input type="hidden" name="id" value="<?= e($svc['id'] ?? '') ?>">
                                        <button type="submit" class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Testimonials'): ?>
            <?php $testimonialsList = $testimonialsList ?? []; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content-item/save">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="type" value="testimonial">
                    <h2>Add a testimonial</h2>
                    <p>Client quotes shown on the home page and Testimonials page.</p>
                    <div class="form-grid two">
                        <label>Name <input name="name" required placeholder="Jane Murphy"></label>
                        <label>Role / company <input name="role" placeholder="Founder, Acme Ltd"></label>
                    </div>
                    <div class="form-grid two">
                        <label>Country <input name="country" placeholder="Ireland"></label>
                        <label>Flag code <small>(2 letters, e.g. IE)</small><input name="flag" placeholder="IE" maxlength="4"></label>
                    </div>
                    <label>Quote <textarea name="quote" rows="4" required></textarea></label>
                    <button class="pill-button" type="submit">Add Testimonial <i class="fa-solid fa-plus"></i></button>
                </form>
                <div class="cyber-card admin-editable-panel">
                    <h2>Testimonials (<?= count($testimonialsList) ?>)</h2>
                    <div class="admin-editable-list">
                        <?php foreach ($testimonialsList as $t): ?>
                            <details class="admin-edit-item">
                                <summary><i class="fa-solid fa-comment-dots"></i> <strong><?= e($t['name'] ?? '') ?></strong><small><?= e($t['role'] ?? '') ?></small></summary>
                                <form class="cyber-form" method="post" action="/admin/content-item/save">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="type" value="testimonial">
                                    <input type="hidden" name="id" value="<?= e($t['id'] ?? '') ?>">
                                    <div class="form-grid two">
                                        <label>Name <input name="name" value="<?= e($t['name'] ?? '') ?>" required></label>
                                        <label>Role / company <input name="role" value="<?= e($t['role'] ?? '') ?>"></label>
                                    </div>
                                    <div class="form-grid two">
                                        <label>Country <input name="country" value="<?= e($t['country'] ?? '') ?>"></label>
                                        <label>Flag code <input name="flag" value="<?= e($t['flag'] ?? '') ?>" maxlength="4"></label>
                                    </div>
                                    <label>Quote <textarea name="quote" rows="4" required><?= e($t['quote'] ?? '') ?></textarea></label>
                                    <div class="admin-item-actions">
                                        <button class="pill-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                                    </div>
                                </form>
                                <div class="admin-item-toolbar">
                                    <?php foreach (['up' => 'fa-arrow-up', 'down' => 'fa-arrow-down'] as $dir => $ic): ?>
                                        <form method="post" action="/admin/content-item/move">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="testimonial"><input type="hidden" name="id" value="<?= e($t['id'] ?? '') ?>"><input type="hidden" name="dir" value="<?= $dir ?>">
                                            <button type="submit" title="Move <?= $dir ?>"><i class="fa-solid <?= $ic ?>"></i></button>
                                        </form>
                                    <?php endforeach; ?>
                                    <form method="post" action="/admin/content-item/delete" onsubmit="return confirm('Delete this testimonial?');">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="testimonial"><input type="hidden" name="id" value="<?= e($t['id'] ?? '') ?>">
                                        <button type="submit" class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'FAQ Manager'): ?>
            <?php $faqList = $faqList ?? []; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/content-item/save">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="type" value="faq">
                    <h2>Add a FAQ</h2>
                    <p>Questions and answers on the FAQ page (also emitted as FAQ schema for search engines).</p>
                    <label>Question <input name="question" required></label>
                    <label>Answer <textarea name="answer" rows="4" required></textarea></label>
                    <button class="pill-button" type="submit">Add FAQ <i class="fa-solid fa-plus"></i></button>
                </form>
                <div class="cyber-card admin-editable-panel">
                    <h2>FAQs (<?= count($faqList) ?>)</h2>
                    <div class="admin-editable-list">
                        <?php foreach ($faqList as $f): ?>
                            <details class="admin-edit-item">
                                <summary><i class="fa-solid fa-circle-question"></i> <strong><?= e($f['question'] ?? '') ?></strong></summary>
                                <form class="cyber-form" method="post" action="/admin/content-item/save">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="type" value="faq">
                                    <input type="hidden" name="id" value="<?= e($f['id'] ?? '') ?>">
                                    <label>Question <input name="question" value="<?= e($f['question'] ?? '') ?>" required></label>
                                    <label>Answer <textarea name="answer" rows="4" required><?= e($f['answer'] ?? '') ?></textarea></label>
                                    <div class="admin-item-actions">
                                        <button class="pill-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                                    </div>
                                </form>
                                <div class="admin-item-toolbar">
                                    <?php foreach (['up' => 'fa-arrow-up', 'down' => 'fa-arrow-down'] as $dir => $ic): ?>
                                        <form method="post" action="/admin/content-item/move">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="faq"><input type="hidden" name="id" value="<?= e($f['id'] ?? '') ?>"><input type="hidden" name="dir" value="<?= $dir ?>">
                                            <button type="submit" title="Move <?= $dir ?>"><i class="fa-solid <?= $ic ?>"></i></button>
                                        </form>
                                    <?php endforeach; ?>
                                    <form method="post" action="/admin/content-item/delete" onsubmit="return confirm('Delete this FAQ?');">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="type" value="faq"><input type="hidden" name="id" value="<?= e($f['id'] ?? '') ?>">
                                        <button type="submit" class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Referrals'): ?>
            <?php $referralEvents = $referralEvents ?? []; ?>
            <section class="cyber-card ticket-table-card">
                <div class="section-heading compact">
                    <span class="status-chip"><span></span> 20% Program</span>
                    <h2>Referral Activity</h2>
                </div>
                <p>Each row is a member who signed up through someone's referral link. Pay the referrer 20% recurring once the referred member becomes a paying Growth Lab Pro subscriber (check Member Manager for their plan).</p>
                <?php if (!empty($referralEvents)): ?>
                    <div class="ticket-table full">
                        <?php foreach ($referralEvents as $ev): ?>
                            <div>
                                <strong><?= e($ev['referrer_email'] ?? '') ?></strong>
                                <span>referred <?= e($ev['referred_email'] ?? '') ?><small>code <?= e($ev['code'] ?? '') ?></small></span>
                                <em><?= e(str_replace('_', ' ', (string) ($ev['status'] ?? 'signed up'))) ?></em>
                                <small><?= e(!empty($ev['created_at']) ? gmdate('j M Y', (int) strtotime((string) $ev['created_at'])) : '') ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No referrals yet. Members get their referral link on their <a href="/account/dashboard">dashboard</a> — share the program to get it going.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Abandoned Orders'): ?>
            <?php $abandonedOrders = $abandonedOrders ?? []; $abandonedStats = $abandonedStats ?? []; ?>
            <section class="cyber-card ticket-table-card">
                <div class="section-heading compact">
                    <span class="status-chip"><span></span> Recovery</span>
                    <h2>Abandoned Checkouts</h2>
                </div>
                <p>Every row is a code-shop checkout that was started. Buyers who don't pay within the grace period get one recovery email. Schedule <code>/cron/run-abandoned-orders?key=YOUR_KEY</code> (same key as monitoring) to send them automatically.</p>
                <div class="stat-inline-row">
                    <span><strong><?= (int) ($abandonedStats['started'] ?? 0) ?></strong> open</span>
                    <span><strong><?= (int) ($abandonedStats['recovered'] ?? 0) ?></strong> emailed</span>
                    <span><strong><?= (int) ($abandonedStats['completed'] ?? 0) ?></strong> paid</span>
                </div>
                <?php if (!empty($abandonedOrders)): ?>
                    <div class="ticket-table full">
                        <?php foreach ($abandonedOrders as $ord): ?>
                            <div>
                                <strong><?= e($ord['email'] ?? '') ?></strong>
                                <span><?= e($ord['title'] ?? '') ?><small><?= e($ord['currency'] ?? 'EUR') ?> <?= e($ord['price'] ?? '') ?> · <?= e($ord['gateway'] ?? '') ?></small></span>
                                <em><?= e(str_replace('_', ' ', (string) ($ord['status'] ?? 'started'))) ?></em>
                                <small><?= e(!empty($ord['created_at']) ? gmdate('j M Y H:i', (int) strtotime((string) $ord['created_at'])) : '') ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No checkouts recorded yet. Orders appear here the moment a buyer clicks Card or PayPal checkout on a code-shop product.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Search Console'): ?>
            <?php $gscSettings = $gscSettings ?? []; $gscRedirectUri = $gscRedirectUri ?? ''; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/search-console-settings">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Google Search Console OAuth</h2>
                    <p>Lets Pro members connect their own Search Console and see queries, clicks, top pages and (via CSV) backlinks. Create OAuth credentials in a Google Cloud project — steps on the right.</p>
                    <label>OAuth Client ID <input name="client_id" value="<?= e($gscSettings['client_id'] ?? '') ?>" placeholder="1234-abc.apps.googleusercontent.com"></label>
                    <label>OAuth Client Secret <small>(leave blank to keep the saved secret)</small>
                        <input name="client_secret" type="password" placeholder="<?= !empty($gscSettings['client_secret']) ? '•••••••• (saved)' : 'GOCSPX-…' ?>"></label>
                    <label class="checkbox-row"><input type="checkbox" name="enabled" value="1" <?= !empty($gscSettings['enabled']) ? 'checked' : '' ?>> Enable the Search Console integration</label>
                    <button class="pill-button" type="submit">Save OAuth Settings <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <div class="cyber-card">
                    <h2>Setup checklist</h2>
                    <ol class="seo-priority-list">
                        <li><strong>Create a Google Cloud project</strong><span>console.cloud.google.com → new project.</span></li>
                        <li><strong>Enable the Search Console API</strong><span>APIs &amp; Services → Library → "Google Search Console API" → Enable.</span></li>
                        <li><strong>Configure the OAuth consent screen</strong><span>External, add your email as a test user (or publish).</span></li>
                        <li><strong>Create an OAuth Client ID</strong><span>Credentials → Create → "Web application".</span></li>
                        <li><strong>Add this exact redirect URI</strong><span class="gsc-redirect-uri"><?= e($gscRedirectUri ?: 'https://yourdomain.com/account/search-console/callback') ?></span></li>
                        <li><strong>Paste the Client ID &amp; Secret</strong><span>into the form on the left and tick Enable.</span></li>
                    </ol>
                    <p><small><i class="fa-solid fa-circle-info"></i> The redirect URI must match character-for-character. Note: Google's API returns queries, clicks and pages — backlinks are imported by members via CSV, as Google does not expose them through the API.</small></p>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'AI Assistant'): ?>
            <?php $aiSettings = $aiSettings ?? []; $aiProviders = $aiProviders ?? ['openai', 'anthropic']; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/ai-settings">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>AI Content Assistant</h2>
                    <p>Optional. Connect an OpenAI or Anthropic key and the <a href="/ai-content-assistant">content assistant</a> generates live copy. Leave it off and it uses high-quality built-in templates — the tool works either way.</p>
                    <label>Provider
                        <select name="provider">
                            <?php foreach ($aiProviders as $p): ?>
                                <option value="<?= e($p) ?>" <?= (($aiSettings['provider'] ?? 'openai') === $p) ? 'selected' : '' ?>><?= e(ucfirst($p)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>API Key <small>(leave blank to keep the saved key)</small>
                        <input name="api_key" type="password" placeholder="<?= !empty($aiSettings['api_key']) ? '•••••••• (saved)' : 'sk-… or your Anthropic key' ?>">
                    </label>
                    <label>Model <small>(optional — sensible default per provider)</small>
                        <input name="model" value="<?= e($aiSettings['model'] ?? '') ?>" placeholder="gpt-4o-mini / claude-haiku-4-5-20251001">
                    </label>
                    <label class="checkbox-row"><input type="checkbox" name="enabled" value="1" <?= !empty($aiSettings['enabled']) ? 'checked' : '' ?>> Use the live AI provider (needs a key)</label>
                    <button class="pill-button" type="submit">Save AI Settings <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <div class="cyber-card">
                    <h2>How it works</h2>
                    <ol class="seo-priority-list">
                        <li><strong>No key needed to start</strong><span>The assistant ships with template-based copy for 8 content types.</span></li>
                        <li><strong>Add a key for live output</strong><span>OpenAI (platform.openai.com) or Anthropic (console.anthropic.com).</span></li>
                        <li><strong>Keys are stored server-side</strong><span>and never sent to the browser or exposed in page source.</span></li>
                        <li><strong>Cost control</strong><span>Free members get 3 generations/day; Pro is unlimited. Live calls use small, low-cost models by default.</span></li>
                    </ol>
                    <p><small><i class="fa-solid fa-shield-halved"></i> Status: <strong><?= !empty($aiSettings['enabled']) && !empty($aiSettings['api_key']) ? 'Live provider connected' : 'Using built-in templates' ?></strong></small></p>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Site Content'): ?>
            <?php
                $sc = $siteContent ?? [];
                $scHero = $sc['hero'] ?? [];
                $scGrowth = $sc['growth_cta'] ?? [];
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
                    <label>Serving list <small>(one per line: flag | label. Flag can be an image path like /assets/images/flags/ie.svg or an emoji)</small>
                        <textarea name="serving" rows="6"><?= e(implode("\n", $servingLines)) ?></textarea></label>
                    <label>Hero ticker items <small>(one per line — the scrolling proof strip)</small>
                        <textarea name="ticker" rows="4"><?= e(implode("\n", is_array($scHero['ticker'] ?? null) ? $scHero['ticker'] : [])) ?></textarea></label>
                    <div class="form-grid two">
                        <label>Primary button label <input name="cta_primary_label" value="<?= e($scHero['cta_primary_label'] ?? '') ?>"></label>
                        <label>Primary button URL <input name="cta_primary_url" value="<?= e($scHero['cta_primary_url'] ?? '') ?>"></label>
                        <label>Secondary button label <input name="cta_secondary_label" value="<?= e($scHero['cta_secondary_label'] ?? '') ?>"></label>
                        <label>Secondary button URL <input name="cta_secondary_url" value="<?= e($scHero['cta_secondary_url'] ?? '') ?>"></label>
                    </div>
                    <button class="pill-button" type="submit">Save Hero <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <form class="cyber-form" method="post" action="/admin/content/growth-cta">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Tools Membership CTA</h2>
                    <p>The Growth Lab Pro call-to-action below the home hero. The perk cards, pricing and buttons stay fixed — edit the eyebrow, headline and subcopy here.</p>
                    <label>Eyebrow / chip text <input name="chip_text" value="<?= e($scGrowth['chip_text'] ?? '') ?>"></label>
                    <label>Headline <small>(use &lt;span&gt;…&lt;/span&gt; to highlight words)</small>
                        <textarea name="headline" rows="2"><?= e($scGrowth['headline'] ?? '') ?></textarea></label>
                    <label>Subcopy <textarea name="subheading" rows="4"><?= e($scGrowth['subheading'] ?? '') ?></textarea></label>
                    <button class="pill-button" type="submit">Save Membership CTA <i class="fa-solid fa-floppy-disk"></i></button>
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

            <?php $carePlans = $carePlans ?? []; ?>
            <section class="split-section">
                <form class="cyber-form care-plan-form" method="post" action="/admin/care-plans">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="original_slug" value="" data-care-original-slug>
                    <h2>Care Plan</h2>
                    <p>Add or edit a maintenance plan sold on <a href="/website-care-plans" target="_blank" rel="noopener">/website-care-plans</a>. Click "Edit" on a plan below to load it here.</p>
                    <div class="form-grid two">
                        <label>Name <input name="name" data-care-field="name" required></label>
                        <label>Slug <small>(blank = auto)</small> <input name="slug" data-care-field="slug"></label>
                        <label>Price <input name="price" data-care-field="price" placeholder="€49"></label>
                        <label>Period <input name="period" data-care-field="period" placeholder="/month"></label>
                        <label>Ideal for <input name="ideal_for" data-care-field="ideal_for" placeholder="Brochure & small business sites"></label>
                        <label>Badge <input name="badge" data-care-field="badge" placeholder="Most popular"></label>
                        <label>Sort order <input name="sort_order" data-care-field="sort_order" type="number" value="100"></label>
                        <label>Checkout URL <small>(Stripe/PayPal link, optional)</small> <input name="checkout_url" data-care-field="checkout_url"></label>
                    </div>
                    <label>Features <small>(one per line)</small> <textarea name="features" rows="7" data-care-field="features"></textarea></label>
                    <div class="checkbox-row">
                        <label><input type="checkbox" name="active" value="1" checked data-care-field="active"> Active (shown on site)</label>
                        <label><input type="checkbox" name="featured" value="1" data-care-field="featured"> Featured (most popular)</label>
                    </div>
                    <button class="pill-button" type="submit">Save Care Plan <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <section class="cyber-card membership-plan-list">
                    <div class="section-heading compact">
                        <span class="status-chip"><span></span> Live plans</span>
                        <h2>Care Plan Tiers</h2>
                    </div>
                    <?php if (!empty($carePlans)): ?>
                        <div class="membership-plan-grid">
                            <?php foreach ($carePlans as $cp): ?>
                                <article class="membership-plan-item <?= empty($cp['active']) ? 'is-paused' : '' ?>">
                                    <header>
                                        <div>
                                            <strong><?= e($cp['name']) ?></strong>
                                            <small><?= e($cp['ideal_for']) ?></small>
                                        </div>
                                        <span class="membership-price"><?= e($cp['price']) ?><em><?= e($cp['period'] ?? '/month') ?></em></span>
                                    </header>
                                    <div class="membership-plan-flags">
                                        <em class="pill-status <?= !empty($cp['active']) ? 'is-live' : 'is-paused' ?>"><?= !empty($cp['active']) ? 'Active' : 'Paused' ?></em>
                                        <?php if (!empty($cp['featured'])): ?><em class="pill-status is-feature">Featured</em><?php endif; ?>
                                        <?php if (!empty($cp['checkout_url'])): ?><em class="pill-status is-gw">Checkout link</em><?php endif; ?>
                                    </div>
                                    <div class="membership-plan-actions">
                                        <button type="button" class="pill-button ghost" data-edit-care-plan='<?= e(json_encode($cp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>'><i class="fa-solid fa-pen"></i> Edit</button>
                                        <form method="post" action="/admin/care-plans/state">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="slug" value="<?= e($cp['slug']) ?>">
                                            <input type="hidden" name="state" value="<?= !empty($cp['active']) ? 'deactivate' : 'activate' ?>">
                                            <button class="pill-button ghost" type="submit"><?= !empty($cp['active']) ? 'Pause' : 'Activate' ?></button>
                                        </form>
                                        <form method="post" action="/admin/care-plans/state" onsubmit="return confirm('Delete this care plan? This cannot be undone.');">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="slug" value="<?= e($cp['slug']) ?>">
                                            <input type="hidden" name="state" value="delete">
                                            <button class="pill-button ghost danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No care plans yet. Add your first plan on the left — it appears on <a href="/website-care-plans">/website-care-plans</a> instantly once active.</p>
                    <?php endif; ?>
                </section>
            </section>
            <script>
            (function () {
                var form = document.querySelector('.care-plan-form');
                if (!form) { return; }
                document.querySelectorAll('[data-edit-care-plan]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var plan = JSON.parse(btn.getAttribute('data-edit-care-plan'));
                        var set = function (name, val) {
                            var el = form.querySelector('[data-care-field="' + name + '"]');
                            if (el) { el.value = val; }
                        };
                        set('name', plan.name || '');
                        set('slug', plan.slug || '');
                        set('price', plan.price || '');
                        set('period', plan.period || '/month');
                        set('ideal_for', plan.ideal_for || '');
                        set('badge', plan.badge || '');
                        set('sort_order', plan.sort_order || 100);
                        set('checkout_url', plan.checkout_url || '');
                        set('features', (plan.features || []).join('\n'));
                        form.querySelector('[data-care-field="featured"]').checked = !!plan.featured;
                        form.querySelector('[data-care-field="active"]').checked = !!plan.active;
                        form.querySelector('[data-care-original-slug]').value = plan.slug || '';
                        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                });
            })();
            </script>
        <?php endif; ?>

        <?php if (($module['title'] ?? '') === 'Coupons'): ?>
            <?php $coupons = $coupons ?? []; $couponContexts = $couponContexts ?? ['all']; ?>
            <section class="split-section">
                <form class="cyber-form" method="post" action="/admin/coupons">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <h2>Create / Update Coupon</h2>
                    <p>Saving an existing code updates it. Codes work on the backlinks, care plan, audit and Speed Rescue order forms — the discounted price is recorded on the order ticket. For hosted Stripe payment links, create the matching promo code in Stripe too.</p>
                    <div class="form-grid two">
                        <label>Code <input name="code" required placeholder="LAUNCH50" style="text-transform:uppercase"></label>
                        <label>Applies to
                            <select name="applies_to">
                                <?php foreach ($couponContexts as $ctx): ?>
                                    <option value="<?= e($ctx) ?>"><?= e(ucwords(str_replace('-', ' ', $ctx))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Type
                            <select name="type">
                                <option value="percent">Percent off (%)</option>
                                <option value="fixed">Fixed amount off (€)</option>
                            </select>
                        </label>
                        <label>Value <input name="value" type="number" step="0.01" min="0.01" required placeholder="50"></label>
                        <label>Max uses <small>(0 = unlimited)</small> <input name="max_uses" type="number" min="0" value="0"></label>
                        <label>Expires <small>(optional)</small> <input name="expires_at" type="date"></label>
                    </div>
                    <div class="checkbox-row">
                        <label><input type="checkbox" name="active" value="1" checked> Active</label>
                    </div>
                    <button class="pill-button" type="submit">Save Coupon <i class="fa-solid fa-floppy-disk"></i></button>
                </form>
                <section class="cyber-card ticket-table-card">
                    <h2>Live Coupons</h2>
                    <?php if (!empty($coupons)): ?>
                        <div class="ticket-table full">
                            <?php foreach ($coupons as $coupon): ?>
                                <div>
                                    <strong><?= e($coupon['code']) ?></strong>
                                    <span>
                                        <?= $coupon['type'] === 'percent' ? e((string) $coupon['value']) . '% off' : '€' . e((string) $coupon['value']) . ' off' ?>
                                        · <?= e(ucwords(str_replace('-', ' ', $coupon['applies_to']))) ?>
                                        <small>Used <?= e((string) $coupon['used_count']) ?><?= $coupon['max_uses'] > 0 ? '/' . e((string) $coupon['max_uses']) : '' ?><?= $coupon['expires_at'] !== '' ? ' · expires ' . e($coupon['expires_at']) : '' ?></small>
                                    </span>
                                    <em><?= !empty($coupon['active']) ? 'active' : 'paused' ?></em>
                                    <span class="coupon-row-actions">
                                        <form method="post" action="/admin/coupons/state">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="code" value="<?= e($coupon['code']) ?>">
                                            <input type="hidden" name="state" value="<?= !empty($coupon['active']) ? 'deactivate' : 'activate' ?>">
                                            <button class="pill-button ghost" type="submit"><?= !empty($coupon['active']) ? 'Pause' : 'Activate' ?></button>
                                        </form>
                                        <form method="post" action="/admin/coupons/state" onsubmit="return confirm('Delete this coupon?');">
                                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="code" value="<?= e($coupon['code']) ?>">
                                            <input type="hidden" name="state" value="delete">
                                            <button class="pill-button ghost danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No coupons yet. Create your first code on the left — try <strong>LAUNCH50</strong> for a 50% launch discount.</p>
                    <?php endif; ?>
                </section>
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

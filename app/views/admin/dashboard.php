<section class="admin-shell">
    <aside class="admin-sidebar">
        <a class="brand admin-brand" href="/admin/dashboard">
            <span class="brand-logo-wrap">
                <img class="brand-logo" src="<?= asset('images/crest-web-media-logo.webp') ?>" alt="Crest Web Media Admin" width="170" height="60">
                <span class="brand-comet" aria-hidden="true"></span>
            </span>
        </a>
        <nav aria-label="Admin menu">
            <?php foreach ($modules as $module): ?>
                <a href="/admin/modules/<?= e($module['slug']) ?>">
                    <i class="fa-solid <?= e($module['icon']) ?>"></i>
                    <span><?= e($module['title']) ?></span>
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
                <span class="status-chip"><span></span> CMS Dashboard</span>
                <h1>Control Center</h1>
                <p>Manage content, support, SEO, security, media and operating signals from one sidebar-driven workspace.</p>
            </div>
            <a class="pill-button" href="/support" target="_blank" rel="noopener">View Support Form <i class="fa-solid fa-up-right-from-square"></i></a>
        </header>

        <?php if (!empty($notice)): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

        <section class="cyber-card admin-migration-panel">
            <span class="module-orbit"><i class="fa-solid fa-database"></i></span>
            <div>
                <span class="status-chip"><span></span> Safe database upgrade</span>
                <h2>Add Missing CMS Tables</h2>
                <p>Use this after replacing website files on Cloudways. It creates any missing enterprise CMS, commerce, typography, script injection, license and secure download tables without reinstalling the website.</p>
            </div>
            <form method="post" action="/admin/cms/add-missing-tables">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="pill-button" type="submit"><i class="fa-solid fa-database"></i> Add Missing Tables</button>
            </form>
        </section>

        <div class="stat-grid dashboard-stats">
            <?php foreach ($stats as $label => $value): ?>
                <article class="metric-card"><strong><?= e($value) ?></strong><span><?= e($label) ?></span></article>
            <?php endforeach; ?>
        </div>

        <section class="admin-work-grid">
            <article class="cyber-card admin-command-panel">
                <h2>Operations Queue</h2>
                <p>Quick access to the modules most likely to need attention after launch.</p>
                <div class="admin-quick-list">
                    <?php foreach (array_slice($modules, 0, 6) as $module): ?>
                        <a href="/admin/modules/<?= e($module['slug']) ?>">
                            <i class="fa-solid <?= e($module['icon']) ?>"></i>
                            <span><strong><?= e($module['title']) ?></strong><small><?= e($module['summary']) ?></small></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="cyber-card ticket-table-card">
                <h2>Recent Tickets</h2>
                <?php if (!empty($tickets)): ?>
                    <div class="ticket-table">
                        <?php foreach ($tickets as $ticket): ?>
                            <a href="/admin/modules/tickets">
                                <strong><?= e($ticket['reference'] ?? '') ?></strong>
                                <span><?= e($ticket['subject'] ?? '') ?></span>
                                <em><?= e($ticket['priority'] ?? 'normal') ?></em>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No support tickets yet. New customer issues will appear here.</p>
                <?php endif; ?>
            </article>
        </section>
    </div>
</section>

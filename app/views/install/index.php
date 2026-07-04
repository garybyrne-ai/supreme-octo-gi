<section class="subhero install-hero">
    <span class="status-chip"><span></span> Setup Wizard</span>
    <h1>Install Crest Web Media</h1>
    <p>Enter your MySQL database details, create the first admin user, and load the demo website content in one pass.</p>
</section>

<section class="section narrow">
    <form class="cyber-form install-form" method="post" action="/install">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="_install_token" value="<?= e($installToken ?? '') ?>">
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

        <fieldset>
            <legend>Database</legend>
            <div class="form-grid two">
                <label>Database Host
                    <input name="db_host" required value="<?= e($old['db_host'] ?? '') ?>" autocomplete="off">
                </label>
                <label>Database Port
                    <input name="db_port" required value="<?= e($old['db_port'] ?? '3306') ?>" inputmode="numeric" autocomplete="off">
                </label>
                <label>Database Name
                    <input name="db_name" required value="<?= e($old['db_name'] ?? '') ?>" autocomplete="off">
                </label>
                <label>Database Username
                    <input name="db_user" required value="<?= e($old['db_user'] ?? '') ?>" autocomplete="off">
                </label>
                <label class="span-two">Database Password
                    <input type="password" name="db_pass" autocomplete="off">
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Website</legend>
            <div class="form-grid two">
                <label>Site Name
                    <input name="site_name" required value="<?= e($old['site_name'] ?? '') ?>">
                </label>
                <label>Site URL
                    <input name="site_url" required value="<?= e($old['site_url'] ?? '') ?>">
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Admin User</legend>
            <div class="form-grid two">
                <label>Admin Name
                    <input name="admin_name" required value="<?= e($old['admin_name'] ?? '') ?>" autocomplete="name">
                </label>
                <label>Admin Email
                    <input type="email" name="admin_email" required value="<?= e($old['admin_email'] ?? '') ?>" autocomplete="email">
                </label>
                <label class="span-two">Admin Password
                    <input type="password" name="admin_password" required minlength="10" autocomplete="new-password">
                </label>
            </div>
        </fieldset>

        <label class="check-toggle">
            <input type="checkbox" name="demo_data" value="1" <?= !empty($old['demo_data']) ? 'checked' : '' ?>>
            <span>Install demo data: services, FAQs, blog post, settings and admin role</span>
        </label>

        <button class="pill-button" type="submit">Install Website <i class="fa-solid fa-database"></i></button>
    </form>
</section>

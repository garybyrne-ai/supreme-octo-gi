<section class="subhero install-hero">
    <span class="status-chip"><span></span> Installed</span>
    <h1>Crest Web Media Is Ready</h1>
    <p>The database tables, admin account, configuration file and selected demo data have been created.</p>
    <div class="button-row centered">
        <a class="pill-button" href="/">View Website <i class="fa-solid fa-arrow-right"></i></a>
        <a class="pill-button ghost" href="/admin">Open Admin <i class="fa-solid fa-lock"></i></a>
    </div>
    <?php if (!empty($adminEmail)): ?>
        <p class="install-note">Admin email: <strong><?= e($adminEmail) ?></strong></p>
    <?php endif; ?>
</section>


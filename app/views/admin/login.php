<section class="subhero admin-hero">
    <span class="status-chip"><span></span> Secure CMS</span>
    <h1>Admin Login</h1>
    <p>Login attempts are logged with IP, user agent, language and device fingerprint data.</p>
</section>
<section class="section narrow">
    <form class="cyber-form" method="post" action="/admin/login">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <label>Email <input type="email" name="email" required value="admin@crestwebmedia.com"></label>
        <label>Password <input type="password" name="password" required placeholder="Your installer password"></label>
        <label class="captcha-field">PHP Captcha
            <span class="captcha-box"><?= e($captcha['question'] ?? '') ?> = ?</span>
            <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Enter answer">
        </label>
        <button class="pill-button" type="submit">Enter Dashboard <i class="fa-solid fa-lock"></i></button>
    </form>
</section>

<section class="subhero">
    <span class="status-chip"><span></span> Send a Project Brief</span>
    <h1>Send The Context Behind The Project</h1>
    <p>Share the offer, market, current website, technical constraints and outcome you want. Crest Web Media builds websites, applications, API integrations, SEO systems and AI workflows for clients across Ireland, the UK, the USA and Europe.</p>
</section>
<?php $contact = $contact ?? (new \App\Models\ContentRepository())->contact(); ?>
<section class="split-section reveal">
    <form class="cyber-form" method="post" action="/contact">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <?php if (!empty($success)): ?><div class="notice success"><?= e($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <label>Name <input name="name" required autocomplete="name"></label>
        <label>Email <input type="email" name="email" required autocomplete="email"></label>
        <label>Service
            <select name="service">
                <option>Website Development</option>
                <option>Web Design</option>
                <option>App Development</option>
                <option>API Integration</option>
                <option>Penetration Testing</option>
                <option>Support Ticket</option>
                <option>SEO</option>
                <option>Maintenance and Support</option>
            </select>
        </label>
        <label>Project Notes <textarea name="message" rows="6" required></textarea></label>
        <label class="captcha-field">PHP Captcha
            <span class="captcha-box"><?= e($captcha['question'] ?? '') ?> = ?</span>
            <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Enter answer">
        </label>
        <button class="pill-button" type="submit">Send Message <i class="fa-solid fa-paper-plane"></i></button>
    </form>
    <aside class="cyber-card contact-card">
        <h2>Contact Channels</h2>
        <p><i class="fa-solid fa-location-dot"></i> Remote studio delivery with written-first coordination across global time zones.</p>
        <p><i class="fa-solid fa-envelope"></i> <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a></p>
        <p><i class="fa-brands fa-whatsapp"></i> <a href="<?= e($contact['whatsapp_url']) ?>">WhatsApp <?= e($contact['phone_display']) ?></a></p>
        <p><i class="fa-solid fa-envelope-open-text"></i> Email-first project conversations with clear written context</p>
        <p><i class="fa-solid fa-ticket"></i> <a href="/support">Open a support ticket</a></p>
        <?php foreach ($contact['locations'] as $location): ?>
            <p><i class="fa-solid fa-location-dot"></i> <?= e($location['name']) ?> - <?= e($location['type']) ?></p>
        <?php endforeach; ?>
        <div class="map-grid" aria-hidden="true"></div>
    </aside>
</section>

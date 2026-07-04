<section class="subhero support-hero">
    <span class="status-chip"><span></span> Customer Support</span>
    <h1>Open a Support Ticket</h1>
    <p>Use the ticketing system for website fixes, security reports, SEO issues, support retainers and launch questions. Each ticket gets a reference number for tracking.</p>
</section>

<?php $old = $old ?? []; ?>
<section class="split-section reveal">
    <form class="cyber-form support-form" method="post" action="/support/tickets">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <?php if (!empty($success)): ?><div class="notice success"><?= e($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

        <div class="form-grid two">
            <label>Name <input name="name" required autocomplete="name" value="<?= e($old['name'] ?? '') ?>"></label>
            <label>Email <input type="email" name="email" required autocomplete="email" value="<?= e($old['email'] ?? '') ?>"></label>
        </div>
        <label>Subject <input name="subject" required maxlength="180" value="<?= e($old['subject'] ?? '') ?>"></label>
        <label>Priority
            <select name="priority">
                <?php foreach (['normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($old['priority'] ?? 'normal') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Issue Details <textarea name="message" rows="7" required maxlength="3000"><?= e($old['message'] ?? '') ?></textarea></label>
        <label class="captcha-field">PHP Captcha
            <span class="captcha-box"><?= e($captcha['question'] ?? '') ?> = ?</span>
            <input name="captcha" inputmode="numeric" required autocomplete="off" placeholder="Enter answer">
        </label>
        <button class="pill-button" type="submit">Create Ticket <i class="fa-solid fa-ticket"></i></button>
    </form>

    <aside class="cyber-card support-card">
        <h2>Support Channels</h2>
        <p><i class="fa-solid fa-envelope"></i> <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a></p>
        <p><i class="fa-brands fa-whatsapp"></i> <a href="<?= e($contact['whatsapp_url']) ?>">WhatsApp <?= e($contact['phone_display']) ?></a></p>
        <div class="ticket-flow">
            <span><b>01</b> Create ticket</span>
            <span><b>02</b> Get reference</span>
            <span><b>03</b> Triage and reply</span>
            <span><b>04</b> Fix and close</span>
        </div>
        <?php if (!empty($reference)): ?>
            <div class="ticket-reference">
                <span>Your Reference</span>
                <strong><?= e($reference) ?></strong>
            </div>
        <?php endif; ?>
    </aside>
</section>

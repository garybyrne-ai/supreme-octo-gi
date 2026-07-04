<section class="subhero">
    <span class="status-chip"><span></span> Answers</span>
    <h1>Frequently Asked Questions</h1>
    <p>Practical answers about projects, support, performance and security.</p>
</section>
<section class="triple-panel reveal">
    <article class="cyber-card"><h2>Locations</h2><p>Crest Web Media operates through Dublin, Ireland and Shimla, Himachal Pradesh, India.</p></article>
    <article class="cyber-card"><h2>Communication</h2><p>Project updates run through email, WhatsApp and the support ticket system so everything stays written, trackable and clear.</p></article>
    <article class="cyber-card"><h2>Support</h2><p>Maintenance plans can include updates, backups, monitoring, security checks and improvement reports.</p></article>
</section>
<section class="section narrow reveal">
    <div class="faq-list">
        <?php foreach ($faqs as $faq): ?>
            <details>
                <summary><?= e($faq['question']) ?></summary>
                <p><?= e($faq['answer']) ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="subhero">
    <span class="status-chip"><span></span> Client Signals</span>
    <h1>Testimonials</h1>
    <p>Feedback from businesses that needed fast, secure and commercial digital execution.</p>
</section>
<section class="section reveal">
    <div class="testimonial-grid">
        <?php foreach ($testimonials as $testimonial): ?>
            <article class="testimonial-card">
                <p><?= e($testimonial['quote']) ?></p>
                <div><span class="avatar"><?= e(substr($testimonial['name'], 0, 1)) ?></span><strong><?= e($testimonial['name']) ?><small><?= e($testimonial['role']) ?> - <?= e($testimonial['country']) ?></small></strong></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>


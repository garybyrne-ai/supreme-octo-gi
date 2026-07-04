<section class="subhero">
    <span class="status-chip"><span></span> Insights</span>
    <h1>Blog</h1>
    <p>Practical notes on performance, SEO, security, AI-assisted development and CMS architecture.</p>
</section>
<section class="triple-panel reveal">
    <article class="cyber-card"><h2>Performance</h2><p>Core Web Vitals, speed budgets, caching and front-end decisions that protect conversion rates.</p></article>
    <article class="cyber-card"><h2>SEO</h2><p>Technical SEO, schema, internal linking and content architecture for service businesses and platforms.</p></article>
    <article class="cyber-card"><h2>Security</h2><p>Practical web security notes for forms, admin panels, APIs, WordPress and custom PHP systems.</p></article>
</section>
<section class="section reveal">
    <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
            <article class="cyber-card blog-card">
                <span class="kicker"><?= e($post['category']) ?> - <?= e($post['reading_time']) ?> - <?= e((string) ($post['word_count'] ?? '2000+')) ?> words</span>
                <h2><a href="/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
                <p><?= e($post['excerpt']) ?></p>
                <?php if (!empty($post['focus_keyword'])): ?>
                    <span class="keyword-pill"><?= e($post['focus_keyword']) ?></span>
                <?php endif; ?>
                <a class="small-link" href="/blog/<?= e($post['slug']) ?>">Read Article</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

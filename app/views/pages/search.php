<section class="subhero">
    <span class="status-chip"><span></span> Search</span>
    <h1>Search Results</h1>
    <form class="search-bar" action="/search">
        <input name="q" value="<?= e($query) ?>" placeholder="Search services, posts, SEO, PHP...">
        <button class="icon-button" type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
</section>
<section class="section reveal">
    <div class="blog-grid">
        <?php foreach ($results as $result): ?>
            <article class="cyber-card">
                <span class="kicker"><?= e($result['category'] ?? 'Service') ?></span>
                <h3><?= e($result['title']) ?></h3>
                <p><?= e($result['summary'] ?? $result['excerpt'] ?? '') ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (!$results): ?>
            <p>No results yet. Try SEO, PHP, security, WordPress or app development.</p>
        <?php endif; ?>
    </div>
</section>


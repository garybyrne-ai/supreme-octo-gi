<article class="article-page">
    <header class="subhero">
        <span class="status-chip"><span></span> <?= e($post['category']) ?> - <?= e($post['reading_time']) ?> - <?= e((string) ($post['word_count'] ?? '2000+')) ?> words</span>
        <h1><?= e($post['title']) ?></h1>
        <p><?= e($post['excerpt']) ?></p>
    </header>

    <section class="article-seo-panel">
        <div>
            <span class="kicker">SEO Focus</span>
            <h2><?= e($post['focus_keyword'] ?? $post['category']) ?></h2>
            <p><?= e($post['meta_description'] ?? $post['excerpt']) ?></p>
        </div>
        <?php if (!empty($post['secondary_keywords'])): ?>
            <div class="keyword-cloud">
                <?php foreach ($post['secondary_keywords'] as $keyword): ?>
                    <span><?= e($keyword) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="article-body">
        <?php if (!empty($post['body_sections'])): ?>
            <nav class="article-toc" aria-label="Article table of contents">
                <strong>Contents</strong>
                <?php foreach ($post['body_sections'] as $index => $section): ?>
                    <a href="#section-<?= $index + 1 ?>"><?= e($section['heading']) ?></a>
                <?php endforeach; ?>
            </nav>

            <?php foreach ($post['body_sections'] as $index => $section): ?>
                <section id="section-<?= $index + 1 ?>" class="article-section">
                    <h2><?= e($section['heading']) ?></h2>
                    <?php foreach ($section['paragraphs'] as $paragraph): ?>
                        <p><?= e($paragraph) ?></p>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($post['body'] as $paragraph): ?>
                <p><?= e($paragraph) ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Implementation Checklist</h2>
        <ul class="check-list">
            <?php foreach (($post['checklist'] ?? []) as $item): ?>
                <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>

        <?php if (!empty($post['faq'])): ?>
            <h2>FAQs</h2>
            <div class="faq-list article-faq">
                <?php foreach ($post['faq'] as $faq): ?>
                    <details>
                        <summary><?= e($faq['question']) ?></summary>
                        <p><?= e($faq['answer']) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <aside class="article-cta cyber-card">
            <span class="kicker">Crest Web Media</span>
            <h2>Turn this trend into a working website, app or growth system.</h2>
            <p>Use the free tools, request a quote or open a support conversation. The goal is practical implementation, not theory.</p>
            <div class="button-row">
                <a class="pill-button" href="/ai-website-growth-consultant">Run AI Growth Consultant <i class="fa-solid fa-brain"></i></a>
                <a class="pill-button ghost" href="/contact">Start a Project <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </aside>
    </section>
</article>


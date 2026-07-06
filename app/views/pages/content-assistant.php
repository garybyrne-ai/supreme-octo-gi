<section class="subhero tools-hero">
    <span class="status-chip"><span></span> AI Content Assistant</span>
    <h1>Write SEO Copy, Outlines &amp; Social Posts in Seconds</h1>
    <p>Turn a topic or keyword into ready-to-use meta descriptions, title tags, blog outlines, FAQs, product copy, social posts, email subject lines and calls to action. Edit, copy and ship.</p>
    <?php if (empty($aiLive)): ?>
        <p class="tool-inline-note"><i class="fa-solid fa-wand-magic-sparkles"></i> Running on built-in copy templates. Add an AI provider key in admin for fully generative output.</p>
    <?php endif; ?>
</section>

<?php require base_path('app/views/partials/tool-access-gate.php'); ?>

<section class="split-section reveal">
    <form class="cyber-form glass-tool" method="post" action="/ai-content-assistant/generate">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Generate content</h2>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <label>Content type
            <select name="type">
                <?php foreach (($contentTypes ?? []) as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= (($contentInput['type'] ?? '') === $key) ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Topic or keyword
            <input name="topic" placeholder="web design for cafes in Cork" value="<?= e($contentInput['topic'] ?? '') ?>" required>
        </label>
        <label>Focus keyword <small>(optional)</small>
            <input name="keyword" placeholder="cafe website design" value="<?= e($contentInput['keyword'] ?? '') ?>">
        </label>
        <label>Audience <small>(optional)</small>
            <input name="audience" placeholder="small cafe owners" value="<?= e($contentInput['audience'] ?? '') ?>">
        </label>
        <label>Tone
            <select name="tone">
                <?php foreach (($contentTones ?? []) as $tone): ?>
                    <option value="<?= e($tone) ?>" <?= (($contentInput['tone'] ?? 'Professional') === $tone) ? 'selected' : '' ?>><?= e($tone) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="pill-button" type="submit">Generate Copy <i class="fa-solid fa-wand-magic-sparkles"></i></button>
    </form>

    <aside class="cyber-card tool-result-card">
        <?php if (!empty($contentResult)): ?>
            <span class="kicker"><?= e($contentResult['label']) ?> · <?= $contentResult['source'] === 'ai' ? 'AI' : 'Template' ?></span>
            <h2><?= count($contentResult['items']) ?> variations</h2>
            <p><?= e($contentResult['note']) ?></p>
        <?php else: ?>
            <span class="kicker">Content Engine</span>
            <h2>From keyword to copy.</h2>
            <p>Pick a content type, describe your topic and get polished, SEO-aware copy you can paste straight into your site, ads or emails.</p>
        <?php endif; ?>
    </aside>
</section>

<?php if (!empty($contentResult)): ?>
    <section class="tool-results reveal">
        <article class="cyber-card tool-result-card ai-content-output">
            <h2><?= e($contentResult['label']) ?></h2>
            <div class="ai-content-list">
                <?php foreach ($contentResult['items'] as $i => $item): ?>
                    <div class="ai-content-item">
                        <b><?= $i + 1 ?></b>
                        <p data-copy-source><?= e($item) ?></p>
                        <button type="button" class="ai-copy-btn" data-copy-prev title="Copy"><i class="fa-solid fa-copy"></i></button>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="ai-content-foot"><i class="fa-solid fa-circle-info"></i> Always review AI-assisted copy for accuracy and brand voice before publishing.</p>
        </article>
    </section>
<?php endif; ?>

<?php require base_path('app/views/partials/tool-pro-cta.php'); ?>

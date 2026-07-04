<section class="subhero forum-hero">
    <span class="status-chip"><span></span> <?= e($topic['category'] ?? 'Forum') ?></span>
    <h1><?= e($topic['title']) ?></h1>
    <p><?= e($topic['excerpt']) ?></p>
    <div class="button-row">
        <a class="pill-button ghost" href="/forum"><i class="fa-solid fa-arrow-left"></i> Forum Home</a>
        <a class="pill-button" href="#reply">Reply Status <i class="fa-solid fa-reply"></i></a>
    </div>
</section>

<?php if (!empty($notice)): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

<?php if (!empty($backlinkList)): ?>
    <section class="cyber-card backlink-resource reveal">
        <div class="section-heading left compact">
            <span class="status-chip"><span></span> Real Links</span>
            <h2>Top 50 Irish Backlink and Citation Places to Research</h2>
            <p>Use these for legitimate profiles, citations, events, chambers, supplier pages and PR research. Check relevance and submission rules before posting anywhere.</p>
        </div>
        <div class="backlink-grid">
            <?php foreach ($backlinkList as $index => $link): ?>
                <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener">
                    <span><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                    <strong><?= e($link['name']) ?></strong>
                    <small><?= e($link['url']) ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="forum-thread reveal">
    <?php foreach ($topic['posts'] as $post): ?>
        <article class="forum-post cyber-card">
            <aside>
                <span class="forum-avatar"><?= e($post['author']['avatar'] ?? 'CW') ?></span>
                <strong><?= e($post['author']['name'] ?? 'Member') ?></strong>
                <small><?= e($post['author']['role'] ?? 'Forum member') ?></small>
                <em><?= e($post['author']['city'] ?? '') ?></em>
            </aside>
            <div>
                <time datetime="<?= e($post['created_at'] ?? '') ?>"><?= e(date('M j, Y g:i A', strtotime((string) ($post['created_at'] ?? 'now')))) ?></time>
                <p><?= nl2br(e($post['body'] ?? '')) ?></p>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="cyber-card forum-reply-card reveal" id="reply">
    <h2>Reply to this topic</h2>
    <?php if (empty($member)): ?>
        <p>Please use the Login/Register icon in the header to create an account. Forum posting is available after admin approval.</p>
    <?php elseif (empty($member['forum_verified'])): ?>
        <p>Your account is registered, but forum posting is pending admin approval. This keeps the forum clean and stops spam links.</p>
    <?php else: ?>
        <form class="cyber-form" method="post" action="/forum/<?= e($topic['slug']) ?>/posts">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label>Your reply
                <textarea name="body" rows="7" required minlength="20" maxlength="2500" placeholder="Add a useful reply, resource or experience..."></textarea>
            </label>
            <button class="pill-button" type="submit">Post Reply <i class="fa-solid fa-paper-plane"></i></button>
        </form>
    <?php endif; ?>
</section>

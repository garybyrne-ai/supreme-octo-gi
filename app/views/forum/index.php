<section class="subhero forum-hero">
    <span class="status-chip"><span></span> Crest Tech Forum</span>
    <h1>SEO, Web Development, AI, Security and Growth Discussions</h1>
    <p>A moderated tech forum with practical threads for Irish, UK, USA and European businesses. Read freely, register to request posting access, and admin approval is required before replies go live.</p>
    <div class="button-row">
        <a class="pill-button" href="#forum-topics">Browse Topics <i class="fa-solid fa-comments"></i></a>
        <a class="pill-button ghost" href="/forum/top-50-irish-websites-where-businesses-can-create-legitimate-backlinks-1">Top Irish Backlink Sites <i class="fa-solid fa-link"></i></a>
    </div>
</section>

<?php if (!empty($notice)): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

<section class="forum-status-strip reveal">
    <article class="cyber-card"><strong><?= e((string) count($topics)) ?></strong><span>Active topics</span></article>
    <article class="cyber-card"><strong><?= e((string) count($users)) ?></strong><span>Members</span></article>
    <article class="cyber-card"><strong><?= !empty($member['forum_verified']) ? 'Approved' : (!empty($member) ? 'Pending' : 'Register') ?></strong><span>Posting status</span></article>
</section>

<section class="split-section reveal">
    <div class="cyber-card forum-feature-card">
        <span class="kicker">Featured Thread</span>
        <h2>Top 50 Irish websites where businesses can create legitimate backlinks</h2>
        <p>Directory profiles, chambers, Irish business platforms, event sites and trusted resources for clean citation building. The thread includes real URLs and discussion around quality control.</p>
        <a class="pill-button" href="/forum/top-50-irish-websites-where-businesses-can-create-legitimate-backlinks-1">Open Thread <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <aside class="cyber-card forum-rules-card">
        <h2>Secure Forum Rules</h2>
        <ul class="check-list">
            <li>Visitors can read topics without registering.</li>
            <li>Only registered members can request posting access.</li>
            <li>Admins approve users before they can post.</li>
            <li>Links should be relevant, useful and non-spammy.</li>
        </ul>
    </aside>
</section>

<section class="forum-board" id="forum-topics">
    <div class="section-heading left">
        <span class="status-chip"><span></span> Discussions</span>
        <h2>Latest Forum Topics</h2>
    </div>
    <div class="forum-topic-list">
        <?php foreach ($topics as $topic): ?>
            <a class="forum-topic-row" href="/forum/<?= e($topic['slug']) ?>">
                <span class="forum-avatar"><?= e($topic['author']['avatar'] ?? 'CW') ?></span>
                <span>
                    <strong><?= e($topic['title']) ?></strong>
                    <small><?= e($topic['excerpt']) ?></small>
                    <em><?= e($topic['category']) ?> - <?= e(date('M j, Y', strtotime((string) $topic['created_at']))) ?> by <?= e($topic['author']['name']) ?></em>
                </span>
                <b><?= e((string) $topic['replies']) ?> replies</b>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php
/** @var array<string,mixed> $client */
$client = $client ?? [];
?>
<section class="subhero case-study-hero">
    <span class="status-chip"><span></span> <?= e($client['industry'] ?? 'Case Study') ?></span>
    <div class="case-study-hero-logo"><img src="<?= e($client['logo'] ?? '') ?>" alt="<?= e($client['name'] ?? '') ?> logo" width="260" height="96"></div>
    <h1><?= e($client['name'] ?? '') ?></h1>
    <p><?= e($client['summary'] ?? '') ?></p>
    <?php if (!empty($client['url'])): ?>
        <div class="button-row"><a class="pill-button ghost" href="<?= e($client['url']) ?>" target="_blank" rel="noopener">Visit the live site <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
    <?php endif; ?>
</section>

<section class="section narrow reveal">
    <?php if (!empty($client['services'])): ?>
        <div class="case-study-tags center">
            <?php foreach ($client['services'] as $service): ?><span><?= e($service) ?></span><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($client['challenge'])): ?>
        <div class="case-study-block">
            <h2><i class="fa-solid fa-circle-exclamation"></i> The challenge</h2>
            <p><?= e($client['challenge']) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($client['work'])): ?>
        <div class="case-study-block">
            <h2><i class="fa-solid fa-screwdriver-wrench"></i> What we did</h2>
            <p><?= e($client['work']) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($client['results'])): ?>
        <div class="case-study-block">
            <h2><i class="fa-solid fa-chart-line"></i> The results</h2>
            <ul class="check-list">
                <?php foreach ($client['results'] as $result): ?><li><?= e($result) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</section>

<section class="section narrow reveal">
    <div class="cyber-card care-includes">
        <h2><i class="fa-solid fa-rocket"></i> Could this be your business?</h2>
        <p>Website, SEO audit and workflow automation — the same playbook, tailored to you. <a href="/contact">Send a project brief</a>, <a href="/website-audit">get a €49 audit</a>, or explore <a href="/website-care-plans">care plans</a>.</p>
    </div>
    <p style="text-align:center;margin-top:18px"><a class="small-link" href="/portfolio"><i class="fa-solid fa-arrow-left"></i> Back to all case studies</a></p>
</section>

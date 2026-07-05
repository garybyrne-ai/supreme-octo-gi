<?php
/** @var array<int, array<string,mixed>> $clients */
$clients = $clients ?? [];
$memberName = $memberName ?? '';
?>
<section class="subhero">
    <span class="status-chip"><span></span> Client Case Studies</span>
    <h1>What We Actually <span>Built, Ranked &amp; Automated</span></h1>
    <p><?= $memberName !== '' ? 'Welcome back, ' . e($memberName) . '. ' : '' ?>Here's the real work behind the logos — websites, SEO audits and workflow automation delivered for growing businesses. Please keep these details confidential.</p>
</section>

<section class="section reveal">
    <div class="case-study-grid">
        <?php foreach ($clients as $client): ?>
            <article class="cyber-card case-study-card">
                <div class="case-study-logo"><img src="<?= e($client['logo']) ?>" alt="<?= e($client['name']) ?> logo" loading="lazy" width="220" height="82"></div>
                <span class="case-study-industry"><?= e($client['industry']) ?></span>
                <h2><?= e($client['name']) ?></h2>
                <p><?= e($client['summary']) ?></p>
                <?php if (!empty($client['services'])): ?>
                    <div class="case-study-tags">
                        <?php foreach ($client['services'] as $service): ?><span><?= e($service) ?></span><?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a class="small-link" href="/case-studies/<?= e($client['slug']) ?>">Read the full case study <i class="fa-solid fa-arrow-right"></i></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section narrow reveal">
    <div class="cyber-card care-includes">
        <h2><i class="fa-solid fa-rocket"></i> Want results like these?</h2>
        <p>We do the same for you: a fast website, a technical SEO audit and automated workflows that save hours every week. <a href="/contact">Send a project brief</a> or <a href="/website-audit">start with a €49 audit</a>.</p>
    </div>
</section>

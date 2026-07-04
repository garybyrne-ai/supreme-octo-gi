<section class="subhero">
    <span class="status-chip"><span></span> Stack</span>
    <h1>Technologies & Tools</h1>
    <p>Backend, frontend, platform, security and cloud tooling used to build scalable digital systems.</p>
</section>
<section class="triple-panel reveal">
    <article class="cyber-card"><h2>PHP + MySQL Core</h2><p>Reliable server-side foundations for CMS platforms, admin panels, APIs and custom business workflows.</p></article>
    <article class="cyber-card"><h2>Modern Frontend</h2><p>Responsive UI with Tailwind-style systems, JavaScript interactions, performance budgets and accessible components.</p></article>
    <article class="cyber-card"><h2>Security + Cloud</h2><p>Kali Linux, FreeBSD, Docker, Linux and deployment practices for safer releases and easier maintenance.</p></article>
</section>
<section class="section reveal">
    <div class="tech-groups">
        <?php foreach (array_unique(array_column($technologies, 'group')) as $group): ?>
            <article class="cyber-card">
                <h2><?= e($group) ?></h2>
                <div class="tag-cloud">
                    <?php foreach (array_filter($technologies, fn ($tech) => $tech['group'] === $group) as $tech): ?>
                        <span class="tech-chip"><img src="<?= asset('images/' . $tech['image']) ?>" alt="<?= e($tech['name']) ?> logo" loading="lazy"><?= e($tech['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

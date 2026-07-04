<section class="subhero">
    <span class="status-chip"><span></span> Workflow</span>
    <h1>Our Proven Process</h1>
    <p>A structured path from discovery to launch and continuous optimization.</p>
</section>
<section class="split-section reveal">
    <div>
        <h2>Calm delivery, clear milestones.</h2>
        <p>Every project starts with the business goal, not the technology. The process turns goals into pages, systems, content, measurements and launch checks that keep the project moving without confusion.</p>
    </div>
    <div class="cyber-card">
        <h2>How communication works</h2>
        <ul class="check-list">
            <li>Clear scope and milestone plan before build starts.</li>
            <li>Async updates that work across global time zones without slowing delivery.</li>
            <li>Launch checklist covering performance, security, SEO and forms.</li>
        </ul>
    </div>
</section>
<section class="section process-section reveal">
    <div class="process-grid page-process-grid">
        <?php foreach ($process as $item): ?>
            <article class="process-node">
                <div class="process-node-top">
                    <span><?= e($item['step']) ?></span>
                    <i class="fa-solid <?= e($item['icon']) ?>"></i>
                </div>
                <div class="process-node-body">
                    <h3><?= e($item['title']) ?></h3>
                    <p><?= e($item['body']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

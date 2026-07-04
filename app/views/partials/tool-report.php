<?php
/**
 * Premium, white-label, printable tool report.
 * Expects $report:
 *   tool (string), icon (string, fa-*), target (string), score (int 0-100),
 *   summary (string), checks (list of ['label','present'(bool),'value','advice'?]),
 *   facts (optional list of ['label','value']),
 *   recommendations (optional list<string>; derived from failed checks if absent).
 */
$report = $report ?? [];
$score = max(0, min(100, (int) ($report['score'] ?? 0)));
$checks = $report['checks'] ?? [];
$facts = $report['facts'] ?? [];
$tool = (string) ($report['tool'] ?? 'Report');
$icon = (string) ($report['icon'] ?? 'fa-gauge-high');
$target = (string) ($report['target'] ?? '');

$grade = $score >= 90 ? 'A' : ($score >= 75 ? 'B' : ($score >= 60 ? 'C' : ($score >= 40 ? 'D' : 'F')));
$gradeWord = ['A' => 'Excellent', 'B' => 'Good', 'C' => 'Fair', 'D' => 'Needs work', 'F' => 'Critical'][$grade];
$gradeClass = 'grade-' . strtolower($grade);

$passed = 0;
foreach ($checks as $c) {
    if (!empty($c['present'])) {
        $passed++;
    }
}
$total = count($checks);

$recommendations = $report['recommendations'] ?? [];
if ($recommendations === []) {
    foreach ($checks as $c) {
        if (empty($c['present'])) {
            $recommendations[] = $c['advice'] ?? ('Add or fix: ' . ($c['label'] ?? 'check'));
        }
    }
}

$circumference = 326.7256; // 2 * pi * 52
$offset = $circumference * (1 - $score / 100);
$reportId = 'report-' . substr(md5($tool . $target), 0, 8);
?>
<section class="tool-report reveal" id="<?= e($reportId) ?>" data-tool-report data-tool-name="<?= e($tool) ?>">
    <div class="report-letterhead" aria-hidden="true" data-report-letterhead>
        <img data-wl-logo alt="" hidden>
        <div>
            <strong data-wl-name>Crest Web Media</strong>
            <span>Growth Lab · <?= e($tool) ?> Report</span>
        </div>
        <em data-report-date></em>
    </div>

    <header class="report-head">
        <div class="report-head-title">
            <span class="report-tool-chip"><i class="fa-solid <?= e($icon) ?>"></i> <?= e($tool) ?></span>
            <h2><?= e($tool) ?> Report</h2>
            <?php if ($target !== ''): ?><p class="report-target"><i class="fa-solid fa-crosshairs"></i> <?= e($target) ?></p><?php endif; ?>
        </div>
        <div class="report-toolbar" data-report-toolbar>
            <label>Agency / brand
                <input type="text" data-wl-input-name placeholder="Your agency name" autocomplete="organization">
            </label>
            <label>Logo URL
                <input type="url" data-wl-input-logo placeholder="https://.../logo.png">
            </label>
            <button class="pill-button" type="button" data-report-print><i class="fa-solid fa-file-arrow-down"></i> Download PDF</button>
        </div>
    </header>

    <div class="report-scoreboard">
        <div class="report-donut <?= e($gradeClass) ?>" role="img" aria-label="Score <?= e((string) $score) ?> out of 100, grade <?= e($grade) ?>">
            <svg viewBox="0 0 120 120" width="132" height="132">
                <circle class="donut-track" cx="60" cy="60" r="52" fill="none" stroke-width="12"></circle>
                <circle class="donut-value" cx="60" cy="60" r="52" fill="none" stroke-width="12"
                        stroke-dasharray="<?= e((string) round($circumference, 2)) ?>"
                        stroke-dashoffset="<?= e((string) round($offset, 2)) ?>"
                        stroke-linecap="round" transform="rotate(-90 60 60)"></circle>
            </svg>
            <div class="report-donut-center">
                <strong><?= e((string) $score) ?></strong>
                <span>/ 100</span>
            </div>
        </div>
        <div class="report-grade-block">
            <span class="report-grade <?= e($gradeClass) ?>"><?= e($grade) ?></span>
            <div>
                <strong><?= e($gradeWord) ?></strong>
                <?php if ($total > 0): ?><span><?= e((string) $passed) ?> of <?= e((string) $total) ?> checks passing</span><?php endif; ?>
                <?php if (!empty($report['summary'])): ?><p><?= e($report['summary']) ?></p><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($facts)): ?>
        <div class="report-facts">
            <?php foreach ($facts as $fact): ?>
                <div><span><?= e($fact['label']) ?></span><strong><?= e($fact['value']) ?></strong></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($checks)): ?>
        <div class="report-checks">
            <?php foreach ($checks as $check): ?>
                <?php $ok = !empty($check['present']); ?>
                <div class="report-check <?= $ok ? 'is-pass' : 'is-fail' ?>">
                    <i class="fa-solid <?= $ok ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                    <div>
                        <strong><?= e($check['label'] ?? '') ?></strong>
                        <small><?= e(excerpt((string) ($check['value'] ?? ''), 160)) ?></small>
                    </div>
                    <em class="report-check-tag"><?= $ok ? 'Pass' : 'Fix' ?></em>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($recommendations)): ?>
        <div class="report-reco">
            <h3><i class="fa-solid fa-list-check"></i> Prioritised recommendations</h3>
            <ol>
                <?php foreach (array_slice($recommendations, 0, 10) as $rec): ?>
                    <li><?= e($rec) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    <?php endif; ?>

    <footer class="report-footer">
        <span data-wl-footer>Generated by Crest Web Media Growth Lab</span>
        <div class="report-actions no-print">
            <?php $memberIsPro = $memberIsPro ?? false; $member = $member ?? null; ?>
            <?php if ($memberIsPro): ?>
                <form method="post" action="/account/reports/save" class="report-save-form">
                    <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
                    <input type="hidden" name="tool" value="<?= e($tool) ?>">
                    <input type="hidden" name="target" value="<?= e($target) ?>">
                    <input type="hidden" name="score" value="<?= e((string) $score) ?>">
                    <input type="hidden" name="grade" value="<?= e($grade) ?>">
                    <input type="hidden" name="summary" value="<?= e((string) ($report['summary'] ?? '')) ?>">
                    <button class="pill-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save report</button>
                </form>
            <?php elseif ($member): ?>
                <a class="pill-button ghost" href="/tools-pricing"><i class="fa-solid fa-lock"></i> Upgrade to save (download is free)</a>
            <?php else: ?>
                <a class="pill-button ghost" href="#tool-access"><i class="fa-solid fa-user-plus"></i> Sign in to save reports</a>
            <?php endif; ?>
        </div>
    </footer>
</section>

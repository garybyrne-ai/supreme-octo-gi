<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\ToolsController;
use App\Core\Controller;
use App\Models\PendingOrderRepository;
use App\Models\RankTrackerRepository;
use App\Services\AuditLogger;
use App\Services\LeadMailer;
use App\Services\MonitorService;

/**
 * Token-guarded cron endpoints. Point a scheduled task at
 * /cron/run-monitors?key=YOUR_KEY (set MONITOR_CRON_KEY), or run the CLI script
 * cron/run-monitors.php from crontab.
 */
final class CronController extends Controller
{
    public function runMonitors(): void
    {
        $configured = (string) ($this->config['monitor_cron_key'] ?? '');
        $provided = (string) ($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');

        if ($configured === '' || !hash_equals($configured, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $summary = (new MonitorService())->runDue();
        (new AuditLogger())->log('cron.monitors.run', $summary);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true] + $summary);
    }

    /**
     * Emails a one-time recovery nudge to anyone who started a code-shop
     * checkout but never paid. Point a scheduled task at
     * /cron/run-abandoned-orders?key=YOUR_KEY (same MONITOR_CRON_KEY).
     */
    public function runAbandonedOrders(): void
    {
        $configured = (string) ($this->config['monitor_cron_key'] ?? '');
        $provided = (string) ($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');

        if ($configured === '' || !hash_equals($configured, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $repo = new PendingOrderRepository();
        $due = $repo->dueForRecovery();
        $base = rtrim((string) ($this->config['url'] ?? ''), '/');
        $mailer = new LeadMailer();
        $sent = 0;

        foreach ($due as $order) {
            $slug = (string) ($order['slug'] ?? '');
            $productUrl = $base . '/code-shop/' . rawurlencode($slug);
            try {
                $ok = $mailer->sendAbandonedOrderRecovery((string) $order['email'], $order, $productUrl);
            } catch (\Throwable) {
                $ok = false;
            }
            // Mark recovered regardless of SMTP result: the JSONL mail log keeps
            // a record, and we never want to spam the same person on every run.
            $repo->markRecovered((string) ($order['id'] ?? ''));
            if ($ok) {
                $sent++;
            }
        }

        $summary = ['due' => count($due), 'emailed' => $sent];
        (new AuditLogger())->log('cron.abandoned_orders.run', $summary);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true] + $summary);
    }

    /**
     * Refreshes the organic position of every tracked keyword that is due.
     * Point a daily scheduled task at /cron/run-rank-tracker?key=YOUR_KEY.
     */
    public function runRankTracker(): void
    {
        $configured = (string) ($this->config['monitor_cron_key'] ?? '');
        $provided = (string) ($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');

        if ($configured === '' || !hash_equals($configured, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $repo = new RankTrackerRepository();
        $tools = new ToolsController($this->config);
        $due = $repo->due();
        $checked = 0;
        // Bound outbound work per run so the cron stays fast.
        foreach (array_slice($due, 0, 40) as $item) {
            try {
                $position = $tools->rankFor($item['keyword'], $item['domain'], $item['location']);
            } catch (\Throwable) {
                continue;
            }
            $repo->recordCheck($item['email'], $item['id'], $position);
            $checked++;
        }

        $summary = ['due' => count($due), 'checked' => $checked];
        (new AuditLogger())->log('cron.rank_tracker.run', $summary);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true] + $summary);
    }

    /**
     * Takes a daily on-page SEO + PageSpeed snapshot of every Pro member's saved
     * website, feeding the dashboard's live Website Analytics graph. Point a
     * daily scheduled task at /cron/run-site-metrics?key=YOUR_KEY.
     */
    public function runSiteMetrics(): void
    {
        $configured = (string) ($this->config['monitor_cron_key'] ?? '');
        $provided = (string) ($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');

        if ($configured === '' || !hash_equals($configured, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $tools = new ToolsController($this->config);
        $metrics = new \App\Models\SiteMetricsRepository();
        $today = gmdate('Y-m-d');
        $captured = 0;
        $eligible = 0;

        // Auto-tracking is a Pro benefit; free members refresh manually.
        foreach ((new \App\Models\MemberRepository())->recent(500) as $member) {
            $website = (string) ($member['website'] ?? '');
            $email = (string) ($member['email'] ?? '');
            if ($website === '' || $email === '' || !\App\Models\MemberRepository::isPro($member)) {
                continue;
            }
            $eligible++;

            // Skip sites already captured today so the run stays cheap.
            $latest = $metrics->latest($email);
            if (is_array($latest) && ($latest['date'] ?? '') === $today) {
                continue;
            }
            if ($captured >= 40) {
                break; // bound outbound work per run
            }

            try {
                $snapshot = $tools->siteSnapshot($website);
            } catch (\Throwable) {
                continue;
            }
            if ($snapshot['seo'] === null && $snapshot['psi'] === null) {
                continue;
            }
            $metrics->record($email, $snapshot);
            $captured++;
        }

        $summary = ['eligible' => $eligible, 'captured' => $captured];
        (new AuditLogger())->log('cron.site_metrics.run', $summary);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true] + $summary);
    }

    /**
     * Runs the scheduled weekly full-site crawl for every Pro member with a
     * saved website that is due, records the result and emails them when the
     * site-wide score regresses or new critical issues appear. Point a daily
     * scheduled task at /cron/run-site-crawls?key=YOUR_KEY (it self-throttles
     * to once a week per site).
     */
    public function runSiteCrawls(): void
    {
        $configured = (string) ($this->config['monitor_cron_key'] ?? '');
        $provided = (string) ($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');

        if ($configured === '' || !hash_equals($configured, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $tools = new ToolsController($this->config);
        $crawls = new \App\Models\SiteCrawlRepository();
        $mailer = new LeadMailer();
        $now = time();
        $crawled = 0;
        $alerts = 0;

        foreach ((new \App\Models\MemberRepository())->recent(500) as $member) {
            $website = (string) ($member['website'] ?? '');
            $email = (string) ($member['email'] ?? '');
            if ($website === '' || $email === '' || !\App\Models\MemberRepository::isPro($member)) {
                continue;
            }
            if (!$crawls->isDue($email, $now)) {
                continue;
            }
            if ($crawled >= 15) {
                break; // crawling is heavy — bound work per run
            }

            $previous = $crawls->get($email)['latest'] ?? null;

            try {
                $result = $tools->crawlSite($website, \App\Services\SiteCrawler::HARD_CAP);
            } catch (\Throwable) {
                $crawls->deferRetry($email, $now);
                continue;
            }
            if (($result['crawled'] ?? 0) === 0) {
                $crawls->deferRetry($email, $now);
                continue;
            }

            $crawls->record($email, $result, $now);
            $crawled++;

            $changes = $this->siteCrawlRegressions($previous, $result);
            if ($changes !== []) {
                try {
                    $mailer->sendSiteCrawlAlert($email, (string) ($member['name'] ?? ''), $result, $changes);
                    $alerts++;
                } catch (\Throwable) {
                    // Email is best-effort; the crawl record is already saved.
                }
            }
        }

        $summary = ['crawled' => $crawled, 'alerts' => $alerts];
        (new AuditLogger())->log('cron.site_crawls.run', $summary);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true] + $summary);
    }

    /**
     * Compare a previous crawl summary to a fresh crawl and describe any
     * regressions worth emailing about. Returns [] when nothing regressed (or
     * there is no prior crawl to compare against — the first run is a baseline).
     *
     * @param array<string, mixed>|null $previous
     * @param array<string, mixed>      $current
     * @return array<int, string>
     */
    private function siteCrawlRegressions(?array $previous, array $current): array
    {
        if (!is_array($previous)) {
            return [];
        }

        $changes = [];
        $prevScore = (int) ($previous['site_score'] ?? 0);
        $curScore = (int) ($current['site_score'] ?? 0);
        if ($prevScore - $curScore >= 5) {
            $changes[] = 'Site-wide score dropped from ' . $prevScore . ' to ' . $curScore . ' out of 100.';
        }

        // New critical (weight 3) issue types that were not present last week.
        $prevLabels = [];
        foreach (is_array($previous['issues'] ?? null) ? $previous['issues'] : [] as $i) {
            $prevLabels[(string) ($i['label'] ?? '')] = true;
        }
        foreach (is_array($current['issues'] ?? null) ? $current['issues'] : [] as $i) {
            $label = (string) ($i['label'] ?? '');
            if ($label !== '' && (int) ($i['weight'] ?? 1) >= 3 && !isset($prevLabels[$label])) {
                $changes[] = 'New critical issue: “' . $label . '” now affects ' . (int) ($i['pages'] ?? 0) . ' page(s).';
            }
        }

        return array_slice($changes, 0, 8);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Controllers\ToolsController;
use App\Models\MemberRepository;
use App\Models\PendingOrderRepository;
use App\Models\RankTrackerRepository;
use App\Models\SiteCrawlRepository;
use App\Models\SiteMetricsRepository;

/**
 * Runs the site's scheduled background jobs (monitors, rank tracking, daily
 * site metrics, weekly full-site crawls, abandoned-order recovery).
 *
 * Each job self-throttles internally (per-item next-due timestamps), so it is
 * safe to call every job on every tick — they no-op when nothing is due. This
 * class is the single source of truth for that work; the HTTP /cron/* endpoints
 * and the traffic-driven web heartbeat both delegate here.
 *
 * The heartbeat (tick) lets the automation run with NO server crontab: on a
 * throttled interval it runs after the visitor's response is already flushed,
 * so it never slows a page. Hosts that do have cron can keep hitting the
 * /cron/* endpoints instead (more reliable on low-traffic sites).
 */
final class Scheduler
{
    /** Minimum gap between web-heartbeat ticks. */
    private const TICK_INTERVAL = 900; // 15 minutes
    /** A held lock older than this is treated as stale (a crashed run). */
    private const LOCK_TIMEOUT = 600; // 10 minutes

    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * Traffic-driven heartbeat. Returns a summary, or ['skipped'=>reason] when
     * it is not yet time or another tick holds the lock. Safe to call on every
     * request — cheap when throttled.
     *
     * @return array<string, mixed>
     */
    public function tick(?int $now = null): array
    {
        $now ??= time();
        $state = $this->readState();

        if ($now - (int) ($state['last_tick'] ?? 0) < self::TICK_INTERVAL) {
            return ['skipped' => 'throttled'];
        }
        if (!$this->acquireLock($now)) {
            return ['skipped' => 'locked'];
        }

        // Record the tick time up front so a mid-run crash can't cause a hot loop.
        $this->writeState(['last_tick' => $now] + $state);

        $summary = [];
        try {
            $summary['monitors'] = $this->runMonitors();
            $summary['ranks'] = $this->runRankTracker();
            $summary['metrics'] = $this->runSiteMetrics();
            $summary['crawls'] = $this->runSiteCrawls();
            $summary['abandoned'] = $this->runAbandonedOrders();
        } finally {
            $this->releaseLock();
        }

        (new AuditLogger())->log('scheduler.tick', ['via' => 'web']);
        return $summary;
    }

    /** @return array<string, int> */
    public function runMonitors(): array
    {
        return (new MonitorService())->runDue();
    }

    /** @return array{due:int, checked:int} */
    public function runRankTracker(): array
    {
        $repo = new RankTrackerRepository();
        $tools = new ToolsController($this->config);
        $due = $repo->due();
        $checked = 0;
        foreach (array_slice($due, 0, 40) as $item) {
            try {
                $position = $tools->rankFor($item['keyword'], $item['domain'], $item['location']);
            } catch (\Throwable) {
                continue;
            }
            $repo->recordCheck($item['email'], $item['id'], $position);
            $checked++;
        }

        return ['due' => count($due), 'checked' => $checked];
    }

    /** @return array{eligible:int, captured:int} */
    public function runSiteMetrics(): array
    {
        $tools = new ToolsController($this->config);
        $metrics = new SiteMetricsRepository();
        $today = gmdate('Y-m-d');
        $captured = 0;
        $eligible = 0;

        foreach ((new MemberRepository())->recent(500) as $member) {
            $website = (string) ($member['website'] ?? '');
            $email = (string) ($member['email'] ?? '');
            if ($website === '' || $email === '' || !MemberRepository::isPro($member)) {
                continue;
            }
            $eligible++;

            $latest = $metrics->latest($email);
            if (is_array($latest) && ($latest['date'] ?? '') === $today) {
                continue;
            }
            if ($captured >= 40) {
                break;
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

        return ['eligible' => $eligible, 'captured' => $captured];
    }

    /** @return array{crawled:int, alerts:int} */
    public function runSiteCrawls(): array
    {
        $tools = new ToolsController($this->config);
        $crawls = new SiteCrawlRepository();
        $mailer = new LeadMailer();
        $now = time();
        $crawled = 0;
        $alerts = 0;

        foreach ((new MemberRepository())->recent(500) as $member) {
            $website = (string) ($member['website'] ?? '');
            $email = (string) ($member['email'] ?? '');
            if ($website === '' || $email === '' || !MemberRepository::isPro($member)) {
                continue;
            }
            if (!$crawls->isDue($email, $now)) {
                continue;
            }
            if ($crawled >= 15) {
                break;
            }

            $previous = $crawls->get($email)['latest'] ?? null;

            try {
                $result = $tools->crawlSite($website, SiteCrawler::HARD_CAP);
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
                    // best-effort email
                }
            }
        }

        return ['crawled' => $crawled, 'alerts' => $alerts];
    }

    /** @return array{due:int, emailed:int} */
    public function runAbandonedOrders(): array
    {
        $repo = new PendingOrderRepository();
        $due = $repo->dueForRecovery();
        $base = rtrim((string) ($this->config['url'] ?? ''), '/');
        $mailer = new LeadMailer();
        $sent = 0;

        foreach ($due as $order) {
            $productUrl = $base . '/code-shop/' . rawurlencode((string) ($order['slug'] ?? ''));
            try {
                $ok = $mailer->sendAbandonedOrderRecovery((string) $order['email'], $order, $productUrl);
            } catch (\Throwable) {
                $ok = false;
            }
            $repo->markRecovered((string) ($order['id'] ?? ''));
            if ($ok) {
                $sent++;
            }
        }

        return ['due' => count($due), 'emailed' => $sent];
    }

    /**
     * Week-over-week regression lines for a site crawl (empty on first crawl or
     * when nothing regressed).
     *
     * @param array<string, mixed>|null $previous
     * @param array<string, mixed>      $current
     * @return array<int, string>
     */
    public function siteCrawlRegressions(?array $previous, array $current): array
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

    // ---- Heartbeat lock/state (a small JSON file, no DB needed) ----

    private function acquireLock(int $now): bool
    {
        $lock = $this->lockPath();
        $dir = dirname($lock);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // Clear a stale lock from a crashed run.
        if (is_file($lock) && ($now - (int) @filemtime($lock)) > self::LOCK_TIMEOUT) {
            @unlink($lock);
        }

        $handle = @fopen($lock, 'x'); // atomic create-if-absent
        if ($handle === false) {
            return false;
        }
        @fwrite($handle, (string) $now);
        @fclose($handle);
        return true;
    }

    private function releaseLock(): void
    {
        @unlink($this->lockPath());
    }

    /** @return array<string, mixed> */
    private function readState(): array
    {
        $path = $this->statePath();
        if (!is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) @file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string, mixed> $state */
    private function writeState(array $state): void
    {
        $dir = dirname($this->statePath());
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->statePath(), json_encode($state, JSON_PRETTY_PRINT), LOCK_EX);
    }

    private function statePath(): string
    {
        return base_path('storage/data/scheduler-state.json');
    }

    private function lockPath(): string
    {
        return base_path('storage/data/scheduler.lock');
    }
}

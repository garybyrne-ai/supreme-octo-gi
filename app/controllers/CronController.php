<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuditLogger;
use App\Services\Scheduler;

/**
 * Token-guarded cron endpoints. Point a scheduled task at
 * /cron/run-monitors?key=YOUR_KEY (set MONITOR_CRON_KEY), or run the CLI script
 * cron/run-monitors.php from crontab.
 *
 * All the actual work lives in {@see Scheduler}, which is also driven
 * automatically by the traffic-based web heartbeat — so the automation runs
 * even on hosts without a real crontab. These endpoints stay for hosts that do
 * have cron (more reliable on low-traffic sites).
 */
final class CronController extends Controller
{
    public function runMonitors(): void
    {
        $this->run('cron.monitors.run', fn (Scheduler $s): array => $s->runMonitors());
    }

    public function runAbandonedOrders(): void
    {
        $this->run('cron.abandoned_orders.run', fn (Scheduler $s): array => $s->runAbandonedOrders());
    }

    public function runRankTracker(): void
    {
        $this->run('cron.rank_tracker.run', fn (Scheduler $s): array => $s->runRankTracker());
    }

    public function runSiteMetrics(): void
    {
        $this->run('cron.site_metrics.run', fn (Scheduler $s): array => $s->runSiteMetrics());
    }

    public function runSiteCrawls(): void
    {
        $this->run('cron.site_crawls.run', fn (Scheduler $s): array => $s->runSiteCrawls());
    }

    /**
     * Shared guard + JSON responder for every cron endpoint.
     *
     * @param callable(Scheduler):array<string, mixed> $job
     */
    private function run(string $logKey, callable $job): void
    {
        $configured = (string) ($this->config['monitor_cron_key'] ?? '');
        $provided = (string) ($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');

        if ($configured === '' || !hash_equals($configured, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $summary = $job(new Scheduler($this->config));
        (new AuditLogger())->log($logKey, $summary);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true] + $summary);
    }
}

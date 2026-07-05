<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PendingOrderRepository;
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
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuditLogger;
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
}

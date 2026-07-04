<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MemberRepository;
use App\Models\MonitorRepository;

/**
 * Runs due website monitors, detects regressions against the previous run and
 * emails the member. Alert conditions: score drop, a passing check now failing
 * (e.g. SPF/DMARC/HSTS), or a TLS certificate nearing expiry.
 */
final class MonitorService
{
    private const SCORE_DROP_THRESHOLD = 5;
    private const TLS_EXPIRY_ALERT_DAYS = 14;
    private const INTERVAL_SECONDS = 604800; // weekly

    public function __construct(
        private readonly MonitorRepository $monitors = new MonitorRepository(),
        private readonly WebsiteScanService $scanner = new WebsiteScanService(),
        private readonly MemberRepository $members = new MemberRepository(),
        private readonly LeadMailer $mailer = new LeadMailer(),
    ) {
    }

    /**
     * @return array{checked:int, alerts:int, skipped:int}
     */
    public function runDue(?int $now = null): array
    {
        $now ??= time();
        $checked = 0;
        $alerts = 0;
        $skipped = 0;

        foreach ($this->monitors->due($now) as $monitor) {
            $email = (string) ($monitor['email'] ?? '');
            $member = $this->members->findByEmail($email);

            // Monitoring is a Pro feature — pause quietly if the member lapsed.
            if ($member === null || !MemberRepository::isPro($member)) {
                $this->monitors->update($email, (string) $monitor['id'], [
                    'active' => false,
                    'last_status' => 'paused',
                    'last_message' => 'Paused — an active Growth Lab Pro membership is required.',
                    'next_due_at' => gmdate('c', $now + self::INTERVAL_SECONDS),
                ]);
                $skipped++;
                continue;
            }

            $report = $this->scan((string) $monitor['type'], (string) $monitor['target']);
            $checked++;

            if ($report === null) {
                $this->monitors->update($email, (string) $monitor['id'], [
                    'last_status' => 'unreachable',
                    'last_message' => 'The target could not be reached on this run.',
                    'last_checked_at' => gmdate('c', $now),
                    'next_due_at' => gmdate('c', $now + self::INTERVAL_SECONDS),
                ]);
                continue;
            }

            $changes = $this->detectRegressions($monitor, $report);

            $this->monitors->update($email, (string) $monitor['id'], [
                'last_score' => (int) $report['score'],
                'last_signals' => $report['signals'] ?? [],
                'last_status' => $changes === [] ? 'ok' : 'alert',
                'last_message' => $changes === [] ? 'No regressions detected.' : implode(' ', $changes),
                'last_checked_at' => gmdate('c', $now),
                'next_due_at' => gmdate('c', $now + self::INTERVAL_SECONDS),
            ]);

            if ($changes !== []) {
                $this->mailer->sendMonitorAlert($email, (string) ($member['name'] ?? ''), $monitor, $report, $changes);
                (new AuditLogger())->log('monitor.alert', ['email' => $email, 'type' => $monitor['type'], 'target' => $monitor['target']]);
                $alerts++;
            }
        }

        return ['checked' => $checked, 'alerts' => $alerts, 'skipped' => $skipped];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function scan(string $type, string $target): ?array
    {
        return match ($type) {
            'security_headers' => $this->scanner->headersReport($target),
            'dns_email' => $this->scanner->dnsEmailReport($target),
            'tls' => $this->scanner->tlsReportNormalised($target),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $monitor
     * @param array<string, mixed> $report
     * @return array<int, string>
     */
    private function detectRegressions(array $monitor, array $report): array
    {
        $changes = [];
        $lastScore = $monitor['last_score'] ?? null;
        $newScore = (int) $report['score'];

        if ($lastScore !== null && $newScore <= (int) $lastScore - self::SCORE_DROP_THRESHOLD) {
            $changes[] = sprintf('Score dropped from %d to %d.', (int) $lastScore, $newScore);
        }

        $lastSignals = is_array($monitor['last_signals'] ?? null) ? $monitor['last_signals'] : [];
        $newSignals = $report['signals'] ?? [];

        foreach ($newSignals as $label => $passing) {
            if ($label === 'days_remaining') {
                continue;
            }
            if (array_key_exists($label, $lastSignals) && $lastSignals[$label] === true && $passing === false) {
                $changes[] = sprintf('“%s” is now failing.', $label);
            }
        }

        if (($monitor['type'] ?? '') === 'tls') {
            $days = (int) ($newSignals['days_remaining'] ?? 999);
            $prevDays = (int) ($lastSignals['days_remaining'] ?? 999);
            if ($days <= self::TLS_EXPIRY_ALERT_DAYS && ($lastScore === null || $prevDays > self::TLS_EXPIRY_ALERT_DAYS || $prevDays > $days)) {
                $changes[] = sprintf('TLS certificate expires in %d day%s.', $days, $days === 1 ? '' : 's');
            }
        }

        return $changes;
    }
}

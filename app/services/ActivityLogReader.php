<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Reads the append-only audit log (written by AuditLogger to
 * storage/logs/audit-YYYY-MM-DD.jsonl) for the admin Activity Logs view.
 */
final class ActivityLogReader
{
    /**
     * Most recent audit events across the last few days, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 120, int $days = 14): array
    {
        $entries = [];
        $dir = base_path('storage/logs');
        for ($i = 0; $i < $days; $i++) {
            $file = $dir . '/audit-' . gmdate('Y-m-d', time() - $i * 86400) . '.jsonl';
            if (!is_file($file)) {
                continue;
            }
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $decoded = json_decode($line, true);
                if (is_array($decoded)) {
                    $entries[] = $decoded;
                }
            }
            if (count($entries) >= $limit * 2) {
                break;
            }
        }

        usort($entries, static fn (array $a, array $b): int => strcmp((string) ($b['timestamp'] ?? ''), (string) ($a['timestamp'] ?? '')));

        return array_slice($entries, 0, $limit);
    }
}

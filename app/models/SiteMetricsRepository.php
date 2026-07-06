<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Per-member time-series of a website's SEO health signals — the data behind
 * the dashboard's live "Website Analytics" graph. Stores one snapshot per day
 * (the newest same-day run replaces the earlier one) keyed by member email, so
 * the trend of on-page SEO score and PageSpeed performance can be plotted over
 * time. Free, self-generated data — no external index required.
 */
final class SiteMetricsRepository
{
    private const MAX_POINTS = 180; // ~6 months of daily snapshots per member

    /**
     * Append (or replace today's) snapshot for a member's site.
     *
     * @param array<string, mixed> $metrics e.g. ['seo' => 82, 'psi' => 64, 'words' => 640]
     */
    public function record(string $email, array $metrics): void
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return;
        }

        $all = $this->all();
        $series = $all[$email] ?? [];
        $today = gmdate('Y-m-d');

        $point = [
            'date' => $today,
            'seo' => isset($metrics['seo']) ? (int) $metrics['seo'] : null,
            'psi' => isset($metrics['psi']) ? (int) $metrics['psi'] : null,
            'words' => isset($metrics['words']) ? (int) $metrics['words'] : null,
            'at' => gmdate('c'),
        ];

        // Replace an existing same-day reading rather than stacking duplicates.
        $replaced = false;
        foreach ($series as &$existing) {
            if (($existing['date'] ?? '') === $today) {
                $existing = $point;
                $replaced = true;
                break;
            }
        }
        unset($existing);

        if (!$replaced) {
            $series[] = $point;
        }

        // Keep chronological order and cap the history length.
        usort($series, static fn (array $a, array $b): int => strcmp((string) ($a['date'] ?? ''), (string) ($b['date'] ?? '')));
        if (count($series) > self::MAX_POINTS) {
            $series = array_slice($series, -self::MAX_POINTS);
        }

        $all[$email] = array_values($series);
        $this->save($all);
    }

    /**
     * The chronological snapshot history for a member.
     *
     * @return array<int, array<string, mixed>>
     */
    public function history(string $email): array
    {
        $email = strtolower(trim($email));
        $series = $this->all()[$email] ?? [];
        return is_array($series) ? array_values($series) : [];
    }

    /**
     * The most recent snapshot, or null when there is no history yet.
     *
     * @return array<string, mixed>|null
     */
    public function latest(string $email): ?array
    {
        $series = $this->history($email);
        return $series === [] ? null : $series[count($series) - 1];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function all(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $all
     */
    private function save(array $all): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function path(): string
    {
        return base_path('storage/data/site-metrics.json');
    }
}

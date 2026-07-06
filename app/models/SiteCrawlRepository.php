<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Per-member record of scheduled weekly full-site crawls: the latest crawl
 * summary, a trimmed score history, and when the next crawl is due. Keyed by
 * member email. Powers the dashboard's crawl status and the regression alert
 * email sent when a site's crawled score drops.
 */
final class SiteCrawlRepository
{
    public const INTERVAL_SECONDS = 604800; // weekly
    private const RETRY_SECONDS = 86400;     // re-attempt a failed crawl next day
    private const MAX_HISTORY = 26;          // ~6 months of weekly points

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $email): ?array
    {
        $email = strtolower(trim($email));
        $record = $this->all()[$email] ?? null;
        return is_array($record) ? $record : null;
    }

    public function isDue(string $email, int $now): bool
    {
        $record = $this->get($email);
        if ($record === null) {
            return true;
        }
        $next = strtotime((string) ($record['next_due_at'] ?? '')) ?: 0;
        return $now >= $next;
    }

    /**
     * Store a completed crawl: keep a trimmed "latest" summary, append a history
     * point and schedule the next crawl a week out.
     *
     * @param array<string, mixed> $crawl A SiteCrawler::crawl() result.
     * @return array<string, mixed> the stored record
     */
    public function record(string $email, array $crawl, int $now): array
    {
        $email = strtolower(trim($email));
        $all = $this->all();
        $record = is_array($all[$email] ?? null) ? $all[$email] : [];

        $latest = [
            'host' => (string) ($crawl['host'] ?? ''),
            'start' => (string) ($crawl['start'] ?? ''),
            'site_score' => (int) ($crawl['site_score'] ?? 0),
            'crawled' => (int) ($crawl['crawled'] ?? 0),
            'issue_count' => (int) ($crawl['issue_count'] ?? 0),
            // Trim heavy nested data so the store stays small.
            'issues' => array_slice(array_map(static fn (array $i): array => [
                'label' => (string) ($i['label'] ?? ''),
                'pages' => (int) ($i['pages'] ?? 0),
                'weight' => (int) ($i['weight'] ?? 1),
            ], is_array($crawl['issues'] ?? null) ? $crawl['issues'] : []), 0, 10),
            'worst_pages' => array_slice(array_map(static fn (array $p): array => [
                'url' => (string) ($p['url'] ?? ''),
                'score' => (int) ($p['score'] ?? 0),
            ], is_array($crawl['worst_pages'] ?? null) ? $crawl['worst_pages'] : []), 0, 5),
            'crawled_at' => gmdate('c', $now),
        ];

        $history = is_array($record['history'] ?? null) ? $record['history'] : [];
        $history[] = [
            'date' => gmdate('Y-m-d', $now),
            'score' => $latest['site_score'],
            'issue_count' => $latest['issue_count'],
            'pages' => $latest['crawled'],
        ];
        if (count($history) > self::MAX_HISTORY) {
            $history = array_slice($history, -self::MAX_HISTORY);
        }

        $all[$email] = [
            'last_crawled_at' => gmdate('c', $now),
            'next_due_at' => gmdate('c', $now + self::INTERVAL_SECONDS),
            'latest' => $latest,
            'history' => array_values($history),
        ];
        $this->save($all);

        return $all[$email];
    }

    /**
     * Push the next attempt out by a day after a failed crawl so a broken site
     * isn't retried on every cron tick.
     */
    public function deferRetry(string $email, int $now): void
    {
        $email = strtolower(trim($email));
        $all = $this->all();
        $record = is_array($all[$email] ?? null) ? $all[$email] : [];
        $record['next_due_at'] = gmdate('c', $now + self::RETRY_SECONDS);
        $all[$email] = $record;
        $this->save($all);
    }

    /**
     * @return array<string, mixed>
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
        return base_path('storage/data/site-crawls.json');
    }
}

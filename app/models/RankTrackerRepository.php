<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Enterprise rank tracker: Growth Lab Pro members save a keyword + domain +
 * location and we record the organic position over time, so they can watch
 * trends, best/worst and movement. JSON-backed, keyed by member email — works
 * with no database and survives file replacement.
 */
final class RankTrackerRepository
{
    private const MAX_KEYWORDS = 60;
    private const MAX_HISTORY = 90;

    /**
     * Add a tracked keyword (or return the existing one). Position may be null
     * on first add and filled by the first check.
     *
     * @return array<string, mixed>
     */
    public function add(string $email, string $keyword, string $domain, string $location = 'Ireland'): array
    {
        $email = strtolower(trim($email));
        $keyword = trim($keyword);
        $domain = strtolower(preg_replace('/^www\./', '', trim($domain)) ?? '');
        if ($email === '' || $keyword === '' || $domain === '') {
            throw new \RuntimeException('Keyword and domain are required to track a ranking.');
        }

        $data = $this->load();
        $data[$email] ??= [];

        foreach ($data[$email] as $entry) {
            if (strcasecmp((string) $entry['keyword'], $keyword) === 0 && (string) $entry['domain'] === $domain) {
                return $entry; // already tracked
            }
        }

        if (count($data[$email]) >= self::MAX_KEYWORDS) {
            throw new \RuntimeException('You have reached the ' . self::MAX_KEYWORDS . '-keyword tracking limit. Remove one to add another.');
        }

        $entry = [
            'id' => bin2hex(random_bytes(6)),
            'keyword' => mb_substr($keyword, 0, 120),
            'domain' => $domain,
            'location' => mb_substr(trim($location) ?: 'Ireland', 0, 60),
            'current_position' => null,
            'best_position' => null,
            'previous_position' => null,
            'history' => [],
            'last_checked_at' => null,
            'created_at' => gmdate('c'),
        ];
        $data[$email][] = $entry;
        $this->persist($data);

        return $entry;
    }

    /**
     * Record a fresh position reading for a tracked keyword.
     */
    public function recordCheck(string $email, string $id, ?int $position): void
    {
        $email = strtolower(trim($email));
        $data = $this->load();
        if (!isset($data[$email])) {
            return;
        }

        foreach ($data[$email] as $i => $entry) {
            if ((string) $entry['id'] !== $id) {
                continue;
            }

            $entry['previous_position'] = $entry['current_position'];
            $entry['current_position'] = $position;
            if ($position !== null) {
                $entry['best_position'] = $entry['best_position'] === null
                    ? $position
                    : min((int) $entry['best_position'], $position);
            }
            $entry['history'][] = ['date' => gmdate('Y-m-d'), 'position' => $position];
            if (count($entry['history']) > self::MAX_HISTORY) {
                $entry['history'] = array_slice($entry['history'], -self::MAX_HISTORY);
            }
            $entry['last_checked_at'] = gmdate('c');

            $data[$email][$i] = $entry;
            $this->persist($data);
            return;
        }
    }

    /**
     * Tracked keywords for a member, each enriched with a movement delta and
     * a compact sparkline series (last 12 readings).
     *
     * @return array<int, array<string, mixed>>
     */
    public function forEmail(string $email): array
    {
        $email = strtolower(trim($email));
        $entries = $this->load()[$email] ?? [];

        foreach ($entries as &$entry) {
            $cur = $entry['current_position'];
            $prev = $entry['previous_position'];
            $entry['delta'] = ($cur !== null && $prev !== null) ? ((int) $prev - (int) $cur) : null; // + = improved
            $entry['spark'] = array_map(
                static fn (array $h) => $h['position'],
                array_slice($entry['history'], -12)
            );
        }
        unset($entry);

        return $entries;
    }

    public function delete(string $email, string $id): void
    {
        $email = strtolower(trim($email));
        $data = $this->load();
        if (!isset($data[$email])) {
            return;
        }
        $data[$email] = array_values(array_filter(
            $data[$email],
            static fn (array $e): bool => (string) $e['id'] !== $id
        ));
        $this->persist($data);
    }

    /**
     * Every tracked keyword across all members that is due a re-check (older
     * than $graceHours since its last check), for the cron worker.
     *
     * @return array<int, array{email:string, id:string, keyword:string, domain:string, location:string}>
     */
    public function due(int $graceHours = 20): array
    {
        $now = time();
        $due = [];
        foreach ($this->load() as $email => $entries) {
            foreach ($entries as $entry) {
                $last = $entry['last_checked_at'] ? (strtotime((string) $entry['last_checked_at']) ?: 0) : 0;
                if ($last !== 0 && ($now - $last) < $graceHours * 3600) {
                    continue;
                }
                $due[] = [
                    'email' => (string) $email,
                    'id' => (string) $entry['id'],
                    'keyword' => (string) $entry['keyword'],
                    'domain' => (string) $entry['domain'],
                    'location' => (string) ($entry['location'] ?? 'Ireland'),
                ];
            }
        }

        return $due;
    }

    private function load(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function persist(array $data): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function path(): string
    {
        return base_path('storage/data/rank-tracker.json');
    }
}

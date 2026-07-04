<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Scheduled website monitors for Pro members. Stored as JSON keyed by member
 * email. Each monitor re-runs a scan on a schedule and alerts on regressions.
 */
final class MonitorRepository
{
    public const TYPES = [
        'security_headers' => 'Security Headers',
        'dns_email' => 'DNS & Email Security',
        'tls' => 'TLS / SSL Certificate',
    ];

    private const MAX_PER_MEMBER = 25;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forEmail(string $email): array
    {
        $email = strtolower(trim($email));

        return array_values($this->all()[$email] ?? []);
    }

    public function add(string $email, string $type, string $target): array
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            throw new \RuntimeException('A member email is required.');
        }
        if (!array_key_exists($type, self::TYPES)) {
            throw new \RuntimeException('Choose a valid monitor type.');
        }

        $target = trim($target);
        if ($target === '') {
            throw new \RuntimeException('Enter a website or domain to monitor.');
        }

        $all = $this->all();
        $list = $all[$email] ?? [];
        if (count($list) >= self::MAX_PER_MEMBER) {
            throw new \RuntimeException('You have reached the maximum number of monitors.');
        }

        foreach ($list as $monitor) {
            if (($monitor['type'] ?? '') === $type && strcasecmp((string) ($monitor['target'] ?? ''), $target) === 0) {
                throw new \RuntimeException('You are already monitoring that target for this check.');
            }
        }

        $entry = [
            'id' => bin2hex(random_bytes(6)),
            'email' => $email,
            'type' => $type,
            'target' => $target,
            'frequency' => 'weekly',
            'active' => true,
            'last_score' => null,
            'last_signals' => [],
            'last_status' => 'pending',
            'last_message' => 'Awaiting first scan.',
            'last_checked_at' => null,
            'next_due_at' => gmdate('c'),
            'created_at' => gmdate('c'),
        ];

        array_unshift($list, $entry);
        $all[$email] = $list;
        $this->persist($all);

        return $entry;
    }

    public function delete(string $email, string $id): void
    {
        $email = strtolower(trim($email));
        $all = $this->all();
        if (!isset($all[$email])) {
            return;
        }

        $all[$email] = array_values(array_filter(
            $all[$email],
            static fn (array $m): bool => ($m['id'] ?? '') !== $id
        ));
        $this->persist($all);
    }

    /**
     * Persist the result of a scan back onto a monitor.
     *
     * @param array<string, mixed> $patch
     */
    public function update(string $email, string $id, array $patch): void
    {
        $email = strtolower(trim($email));
        $all = $this->all();
        if (!isset($all[$email])) {
            return;
        }

        foreach ($all[$email] as &$monitor) {
            if (($monitor['id'] ?? '') === $id) {
                $monitor = array_replace($monitor, $patch);
                break;
            }
        }
        unset($monitor);

        $this->persist($all);
    }

    /**
     * Every active monitor across all members whose next run is due.
     *
     * @return array<int, array<string, mixed>>
     */
    public function due(?int $now = null): array
    {
        $now ??= time();
        $due = [];

        foreach ($this->all() as $monitors) {
            foreach ($monitors as $monitor) {
                if (empty($monitor['active'])) {
                    continue;
                }
                $next = strtotime((string) ($monitor['next_due_at'] ?? '')) ?: 0;
                if ($next <= $now) {
                    $due[] = $monitor;
                }
            }
        }

        return $due;
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
     * @param array<string, array<int, array<string, mixed>>> $all
     */
    private function persist(array $all): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function path(): string
    {
        return base_path('storage/data/monitors.json');
    }
}

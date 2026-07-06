<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Client portal work log. The admin posts updates (work done, reports, backups,
 * notes) against a client's email; the logged-in member sees their own entries
 * on their dashboard. JSON-backed, keyed by lowercased email.
 */
final class ClientPortalRepository
{
    public const TYPES = ['update', 'report', 'backup', 'seo', 'note'];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forEmail(string $email): array
    {
        $email = strtolower(trim($email));
        $entries = $this->load()[$email] ?? [];
        if (!is_array($entries)) {
            return [];
        }
        usort($entries, static fn (array $a, array $b): int => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));

        return $entries;
    }

    /**
     * @return array<int, array<string, mixed>> recent entries across all clients (admin view)
     */
    public function recent(int $limit = 60): array
    {
        $all = [];
        foreach ($this->load() as $email => $entries) {
            foreach ((array) $entries as $entry) {
                if (is_array($entry)) {
                    $all[] = ['email' => $email] + $entry;
                }
            }
        }
        usort($all, static fn (array $a, array $b): int => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));

        return array_slice($all, 0, $limit);
    }

    public function add(array $input): string
    {
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Enter a valid client email address.');
        }
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            throw new \RuntimeException('A title is required.');
        }

        $type = (string) ($input['type'] ?? 'update');
        if (!in_array($type, self::TYPES, true)) {
            $type = 'update';
        }

        $dateRaw = trim((string) ($input['date'] ?? ''));
        $date = $dateRaw !== '' && strtotime($dateRaw) !== false ? date('Y-m-d', (int) strtotime($dateRaw)) : gmdate('Y-m-d');

        $entry = [
            'id' => bin2hex(random_bytes(6)),
            'date' => $date,
            'type' => $type,
            'title' => mb_substr($title, 0, 160),
            'note' => mb_substr(trim((string) ($input['note'] ?? '')), 0, 2000),
            'link' => (function (string $l): string {
                $l = trim($l);
                return $l !== '' && filter_var($l, FILTER_VALIDATE_URL) ? mb_substr($l, 0, 300) : '';
            })((string) ($input['link'] ?? '')),
            'created_at' => gmdate('c'),
        ];

        $data = $this->load();
        $data[$email] = $data[$email] ?? [];
        array_unshift($data[$email], $entry);
        $this->persist($data);

        return $entry['id'];
    }

    public function delete(string $email, string $id): void
    {
        $email = strtolower(trim($email));
        $data = $this->load();
        if (!isset($data[$email]) || !is_array($data[$email])) {
            return;
        }
        $data[$email] = array_values(array_filter($data[$email], static fn (array $e): bool => ($e['id'] ?? '') !== $id));
        $this->persist($data);
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
        file_put_contents($this->path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function path(): string
    {
        return base_path('storage/data/client-portal.json');
    }
}

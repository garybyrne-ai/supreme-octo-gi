<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Stores tool reports for Pro members. Only the last three reports per member
 * are retained (free members cannot save — they can view and download only).
 */
final class SavedReportRepository
{
    private const MAX_PER_MEMBER = 3;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forEmail(string $email): array
    {
        $email = strtolower(trim($email));
        $all = $this->all();

        return array_values($all[$email] ?? []);
    }

    /**
     * @param array<string, mixed> $report
     * @return array<int, array<string, mixed>>
     */
    public function save(string $email, array $report): array
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            throw new \RuntimeException('A member email is required to save a report.');
        }

        $entry = [
            'id' => bin2hex(random_bytes(6)),
            'tool' => mb_substr(trim((string) ($report['tool'] ?? 'Report')), 0, 80),
            'target' => mb_substr(trim((string) ($report['target'] ?? '')), 0, 255),
            'score' => max(0, min(100, (int) ($report['score'] ?? 0))),
            'grade' => (string) ($report['grade'] ?? ''),
            'summary' => mb_substr(trim((string) ($report['summary'] ?? '')), 0, 300),
            'created_at' => gmdate('c'),
        ];

        $all = $this->all();
        $list = $all[$email] ?? [];
        array_unshift($list, $entry);
        $all[$email] = array_slice($list, 0, self::MAX_PER_MEMBER);
        $this->persist($all);

        return array_values($all[$email]);
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
            static fn (array $r): bool => ($r['id'] ?? '') !== $id
        ));
        $this->persist($all);
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
        return base_path('storage/data/saved-reports.json');
    }
}

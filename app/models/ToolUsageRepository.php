<?php

declare(strict_types=1);

namespace App\Models;

final class ToolUsageRepository
{
    private const FREE_DAILY_LIMIT = 3;

    public function status(string $email): array
    {
        $email = strtolower(trim($email));
        $date = gmdate('Y-m-d');
        $used = $this->used($email, $date);

        return [
            'limit' => self::FREE_DAILY_LIMIT,
            'used' => $used,
            'remaining' => max(0, self::FREE_DAILY_LIMIT - $used),
            'date' => $date,
        ];
    }

    public function consume(string $email, string $tool): array
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            throw new \RuntimeException('Tool user email is required.');
        }

        $data = $this->all();
        $date = gmdate('Y-m-d');
        $key = hash('sha256', $email);
        $bucket = $data[$date][$key] ?? ['email' => $email, 'count' => 0, 'events' => []];

        if ((int) ($bucket['count'] ?? 0) >= self::FREE_DAILY_LIMIT) {
            return $this->status($email) + ['allowed' => false];
        }

        $bucket['email'] = $email;
        $bucket['count'] = (int) ($bucket['count'] ?? 0) + 1;
        $bucket['events'][] = [
            'tool' => preg_replace('/[^a-z0-9_-]/i', '', $tool),
            'at' => gmdate('c'),
        ];
        $bucket['events'] = array_slice($bucket['events'], -30);

        $data[$date][$key] = $bucket;
        $this->write($this->prune($data));

        return $this->status($email) + ['allowed' => true];
    }

    private function used(string $email, string $date): int
    {
        $data = $this->all();
        $key = hash('sha256', strtolower(trim($email)));
        return (int) ($data[$date][$key]['count'] ?? 0);
    }

    private function all(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    private function write(array $data): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function prune(array $data): array
    {
        krsort($data);
        return array_slice($data, 0, 14, true);
    }

    private function path(): string
    {
        return base_path('storage/data/tool-usage.json');
    }
}

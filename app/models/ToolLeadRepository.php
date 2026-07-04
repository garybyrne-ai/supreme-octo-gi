<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Security;

final class ToolLeadRepository
{
    public function store(array $lead): void
    {
        $payload = [
            'name' => trim((string) $lead['name']),
            'email' => strtolower(trim((string) $lead['email'])),
            'source' => trim((string) ($lead['source'] ?? 'tools')),
            'ip_address' => Security::clientIp(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
            'created_at' => gmdate('c'),
        ];

        if (is_file(base_path('config/installed.php')) && $this->storeDatabase($payload)) {
            return;
        }

        $this->storeFile($payload);
    }

    public function recent(int $limit = 20): array
    {
        if (is_file(base_path('config/installed.php'))) {
            try {
                $stmt = Database::connection()->query('SELECT name, email, source, created_at FROM tool_leads ORDER BY created_at DESC LIMIT ' . max(1, min(100, $limit)));
                return $stmt->fetchAll() ?: [];
            } catch (\Throwable) {
            }
        }

        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $rows = array_filter(explode(PHP_EOL, trim((string) file_get_contents($path))));
        $leads = array_values(array_filter(array_map(static fn (string $row): array => json_decode($row, true) ?: [], $rows)));
        usort($leads, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
        return array_slice($leads, 0, $limit);
    }

    private function storeDatabase(array $payload): bool
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO tool_leads (name, email, source, ip_address, user_agent)
                 VALUES (:name, :email, :source, :ip_address, :user_agent)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), source = VALUES(source), updated_at = NOW()'
            );
            $stored = $stmt->execute([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'source' => $payload['source'],
                'ip_address' => $payload['ip_address'],
                'user_agent' => $payload['user_agent'],
            ]);

            $newsletter = Database::connection()->prepare(
                'INSERT INTO newsletter (email, status) VALUES (:email, "subscribed")
                 ON DUPLICATE KEY UPDATE status = "subscribed"'
            );
            $newsletter->execute(['email' => $payload['email']]);

            return $stored;
        } catch (\Throwable) {
            return false;
        }
    }

    private function storeFile(array $payload): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function path(): string
    {
        return base_path('storage/data/tool-leads.jsonl');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class SupportTicketRepository
{
    public function create(array $ticket): string
    {
        $reference = 'CWM-' . strtoupper(bin2hex(random_bytes(3)));
        $payload = array_replace($ticket, [
            'reference' => $reference,
            'status' => 'open',
            'created_at' => gmdate('c'),
        ]);

        if (is_file(base_path('config/installed.php')) && $this->storeInDatabase($payload)) {
            return $reference;
        }

        $this->storeInFile($payload);
        return $reference;
    }

    public function recent(int $limit = 12): array
    {
        if (is_file(base_path('config/installed.php'))) {
            try {
                $stmt = Database::connection()->query(
                    'SELECT reference, name, email, subject, priority, status, created_at
                     FROM support_tickets
                     ORDER BY created_at DESC
                     LIMIT ' . max(1, min(50, $limit))
                );

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable) {
            }
        }

        $path = $this->storagePath();
        if (!is_file($path)) {
            return [];
        }

        $rows = array_filter(explode(PHP_EOL, trim((string) file_get_contents($path))));
        $tickets = array_map(static fn (string $row): array => json_decode($row, true) ?: [], $rows);
        $tickets = array_values(array_filter($tickets));
        usort($tickets, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return array_slice($tickets, 0, $limit);
    }

    private function storeInDatabase(array $payload): bool
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO support_tickets
                    (reference, name, email, subject, priority, message, status, ip_address, user_agent)
                 VALUES
                    (:reference, :name, :email, :subject, :priority, :message, :status, :ip_address, :user_agent)'
            );

            return $stmt->execute([
                'reference' => $payload['reference'],
                'name' => $payload['name'],
                'email' => $payload['email'],
                'subject' => $payload['subject'],
                'priority' => $payload['priority'],
                'message' => $payload['message'],
                'status' => $payload['status'],
                'ip_address' => $payload['ip_address'],
                'user_agent' => $payload['user_agent'],
            ]);
        } catch (\Throwable) {
            return false;
        }
    }

    private function storeInFile(array $payload): void
    {
        $dir = dirname($this->storagePath());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->storagePath(), json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function storagePath(): string
    {
        return base_path('storage/data/support-tickets.jsonl');
    }
}

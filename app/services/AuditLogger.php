<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Security;

final class AuditLogger
{
    public function log(string $event, array $context = []): void
    {
        $payload = [
            'timestamp' => gmdate('c'),
            'event' => $event,
            'fingerprint' => Security::deviceFingerprint(),
            'context' => $context,
        ];

        $path = base_path('storage/logs/audit-' . gmdate('Y-m-d') . '.jsonl');
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}


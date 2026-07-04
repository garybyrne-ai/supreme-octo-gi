<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use PDOException;

final class MigrationService
{
    public function addMissingTables(): array
    {
        $pdo = Database::connection();
        $this->ensureMigrationLog($pdo);

        $files = glob(base_path('database/migrations/*.sql')) ?: [];
        sort($files, SORT_STRING);

        $summary = [
            'files' => [],
            'statements' => 0,
            'tables' => [],
            'warnings' => [],
        ];

        foreach ($files as $file) {
            if (str_starts_with(basename($file), '001_')) {
                continue;
            }

            $result = $this->runSqlFile($pdo, $file);
            $summary['files'][] = basename($file);
            $summary['statements'] += $result['statements'];
            $summary['tables'] = array_values(array_unique(array_merge($summary['tables'], $result['tables'])));
            $summary['warnings'] = array_merge($summary['warnings'], $result['warnings']);

            $this->recordMigration($pdo, basename($file), hash_file('sha256', $file) ?: '');
        }

        return $summary;
    }

    private function ensureMigrationLog(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS cms_migrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(190) NOT NULL UNIQUE,
                checksum_sha256 CHAR(64) NOT NULL,
                ran_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function runSqlFile(PDO $pdo, string $path): array
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new \RuntimeException('Could not read migration file: ' . basename($path));
        }

        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];
        $ran = 0;
        $tables = [];
        $warnings = [];

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            if (preg_match('/^(CREATE\s+DATABASE|USE|SOURCE)\b/i', $statement)) {
                continue;
            }

            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $statement, $matches)) {
                $tables[] = $matches[1];
            }

            try {
                $pdo->exec($statement);
            } catch (PDOException $exception) {
                if (!$this->isForeignKeyCreateFailure($statement, $exception)) {
                    throw $exception;
                }

                $fallbackStatement = $this->removeForeignKeyClauses($statement);
                if ($fallbackStatement === $statement) {
                    throw $exception;
                }

                $pdo->exec($fallbackStatement);
                $warnings[] = sprintf(
                    '%s was created without foreign keys because the existing database schema has incompatible key definitions.',
                    $tables[array_key_last($tables)] ?? 'A table'
                );
            }

            $ran++;
        }

        return [
            'statements' => $ran,
            'tables' => array_values(array_unique($tables)),
            'warnings' => $warnings,
        ];
    }

    private function isForeignKeyCreateFailure(string $statement, PDOException $exception): bool
    {
        if (!preg_match('/^CREATE\s+TABLE\b/i', $statement)) {
            return false;
        }

        $message = $exception->getMessage();

        return str_contains($message, 'errno: 150')
            || str_contains($message, 'Foreign key constraint is incorrectly formed')
            || str_contains($message, 'Cannot add foreign key constraint');
    }

    private function removeForeignKeyClauses(string $statement): string
    {
        $lines = preg_split('/\r?\n/', $statement) ?: [];
        $kept = [];

        foreach ($lines as $line) {
            if (preg_match('/\bFOREIGN\s+KEY\b/i', $line)) {
                continue;
            }

            $kept[] = $line;
        }

        $cleaned = implode(PHP_EOL, $kept);

        return preg_replace('/,\s*\r?\n\s*\)\s*ENGINE=/i', PHP_EOL . ') ENGINE=', $cleaned) ?? $cleaned;
    }

    private function recordMigration(PDO $pdo, string $migration, string $checksum): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO cms_migrations (migration, checksum_sha256, ran_at)
             VALUES (:migration, :checksum_sha256, UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE checksum_sha256 = VALUES(checksum_sha256), ran_at = VALUES(ran_at)'
        );
        $stmt->execute([
            'migration' => $migration,
            'checksum_sha256' => $checksum,
        ]);
    }
}

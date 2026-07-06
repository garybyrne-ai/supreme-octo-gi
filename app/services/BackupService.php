<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Lightweight backups: bundles all editable JSON data files (members, content,
 * settings, orders …) into a single downloadable JSON snapshot, and lists what
 * is stored so an admin can see and export their data. No database required.
 */
final class BackupService
{
    /**
     * The data files that make up a content/settings backup.
     *
     * @return array<int, array{name: string, size: int, modified: string}>
     */
    public function inventory(): array
    {
        $items = [];
        foreach ($this->files() as $file) {
            $items[] = [
                'name' => basename($file),
                'size' => (int) filesize($file),
                'modified' => gmdate('Y-m-d H:i', (int) filemtime($file)),
            ];
        }
        usort($items, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $items;
    }

    public function totalBytes(): int
    {
        $total = 0;
        foreach ($this->files() as $file) {
            $total += (int) filesize($file);
        }

        return $total;
    }

    /**
     * Build a single JSON snapshot of every data file.
     */
    public function buildSnapshot(): string
    {
        $bundle = [
            'generated_at' => gmdate('c'),
            'site' => 'Crest Web Media',
            'files' => [],
        ];
        foreach ($this->files() as $file) {
            $decoded = json_decode((string) file_get_contents($file), true);
            $bundle['files'][basename($file)] = $decoded ?? (string) file_get_contents($file);
        }

        return (string) json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function snapshotFilename(): string
    {
        return 'crestwebmedia-backup-' . gmdate('Y-m-d-His') . '.json';
    }

    /**
     * @return array<int, string>
     */
    private function files(): array
    {
        $dir = base_path('storage/data');
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/*.json') ?: [];

        return array_values(array_filter($files, 'is_file'));
    }
}

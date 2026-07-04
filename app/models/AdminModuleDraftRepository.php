<?php

declare(strict_types=1);

namespace App\Models;

final class AdminModuleDraftRepository
{
    private string $path;

    public function __construct()
    {
        $this->path = storage_path('data/admin-module-drafts.json');
    }

    public function all(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->path), true);
        return is_array($data) ? $data : [];
    }

    public function byModule(string $module): array
    {
        $all = $this->all();
        return array_values(array_filter($all, fn (array $item): bool => ($item['module'] ?? '') === $module));
    }

    public function save(array $payload): void
    {
        $module = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($payload['module'] ?? '')));
        $action = trim((string) ($payload['action'] ?? ''));
        $title = trim((string) ($payload['title'] ?? ''));
        $notes = trim((string) ($payload['notes'] ?? ''));
        $status = trim((string) ($payload['status'] ?? 'planned'));

        if ($module === '' || $action === '' || $title === '') {
            throw new \InvalidArgumentException('Module, action and title are required.');
        }

        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $all = $this->all();
        array_unshift($all, [
            'id' => bin2hex(random_bytes(8)),
            'module' => $module,
            'action' => $action,
            'title' => substr($title, 0, 160),
            'notes' => substr($notes, 0, 2000),
            'status' => in_array($status, ['planned', 'in_progress', 'ready', 'done'], true) ? $status : 'planned',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        file_put_contents($this->path, json_encode(array_slice($all, 0, 300), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}

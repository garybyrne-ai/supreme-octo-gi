<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Admin-managed URL redirects (from a site path to a path or absolute URL).
 * Applied by the front controller before routing. JSON-backed CRUD via the
 * shared editable-content base.
 */
final class RedirectRepository extends EditableContentRepository
{
    private const STATUSES = [301, 302, 307, 308];

    protected function filename(): string
    {
        return 'redirects.json';
    }

    protected function normalize(array $row): ?array
    {
        $from = $this->normalizePath((string) ($row['from'] ?? ''));
        $to = trim((string) ($row['to'] ?? ''));
        if ($from === '' || $to === '') {
            return null;
        }
        // `to` may be an absolute URL or a site path.
        if (!preg_match('#^https?://#i', $to)) {
            $to = $this->normalizePath($to);
        }
        if ($to === '' || $to === $from) {
            return null;
        }

        $status = (int) ($row['status'] ?? 301);
        if (!in_array($status, self::STATUSES, true)) {
            $status = 301;
        }

        return ['from' => $from, 'to' => $to, 'status' => $status];
    }

    /**
     * Find a redirect target for a request path.
     *
     * @return array{to: string, status: int}|null
     */
    public function match(string $path): ?array
    {
        $path = $this->normalizePath($path);
        if ($path === '' || $path === '/') {
            return null;
        }
        foreach ($this->saved() as $r) {
            if (($r['from'] ?? '') === $path) {
                return ['to' => (string) $r['to'], 'status' => (int) ($r['status'] ?? 301)];
            }
        }

        return null;
    }

    private function normalizePath(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $value)) {
            return $value; // absolute target, leave as-is
        }
        $value = '/' . ltrim($value, '/');
        // strip a trailing slash except for root
        return $value !== '/' ? rtrim($value, '/') : $value;
    }
}

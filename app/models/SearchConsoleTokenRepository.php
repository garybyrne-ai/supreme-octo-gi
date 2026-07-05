<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Per-member Google OAuth tokens for Search Console, keyed by member email.
 * Stores the refresh token (long-lived), the current access token and its
 * expiry, the chosen site, and an optional manually-imported backlinks list
 * (Google does not expose backlinks via the API, so members upload the CSV).
 */
final class SearchConsoleTokenRepository
{
    public function get(string $email): ?array
    {
        $all = $this->all();
        $email = strtolower(trim($email));
        return isset($all[$email]) && is_array($all[$email]) ? $all[$email] : null;
    }

    public function connected(string $email): bool
    {
        $rec = $this->get($email);
        return $rec !== null && trim((string) ($rec['refresh_token'] ?? '')) !== '';
    }

    public function save(string $email, array $data): void
    {
        $email = strtolower(trim($email));
        $all = $this->all();
        $existing = is_array($all[$email] ?? null) ? $all[$email] : [];
        $all[$email] = array_replace($existing, $data);
        $this->persist($all);
    }

    public function setSite(string $email, string $site): void
    {
        $this->save($email, ['site' => $site]);
    }

    public function saveBacklinks(string $email, array $backlinks, ?string $filename = null): void
    {
        $this->save($email, [
            'backlinks' => array_slice($backlinks, 0, 500),
            'backlinks_imported_at' => gmdate('c'),
            'backlinks_source' => $filename,
        ]);
    }

    public function disconnect(string $email): void
    {
        $email = strtolower(trim($email));
        $all = $this->all();
        unset($all[$email]);
        $this->persist($all);
    }

    private function all(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

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
        return base_path('storage/data/search-console-tokens.json');
    }
}

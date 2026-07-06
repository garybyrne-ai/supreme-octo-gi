<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Base for admin-editable content collections (services, testimonials, FAQs …).
 *
 * Behaviour:
 *  - Before an admin edits anything, no JSON file exists and the site uses the
 *    hard-coded defaults passed to resolve().
 *  - The first time the admin opens the manager, ensureSeeded() writes the
 *    current defaults to JSON so there is a real list to edit.
 *  - After that the JSON file is authoritative — add / edit / delete / reorder
 *    all operate on it, and the front-end reads it via resolve().
 *
 * JSON-backed, keyed by a stable per-item id. No database required.
 */
abstract class EditableContentRepository
{
    /** Storage filename under storage/data/, e.g. "services.json". */
    abstract protected function filename(): string;

    /**
     * Validate + clean one item for storage. Return null to drop it.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    abstract protected function normalize(array $row): ?array;

    /** True once an admin has seeded/edited this collection. */
    public function isManaged(): bool
    {
        return is_file($this->path());
    }

    /**
     * Stored items (each with an id). Empty array when the file is missing.
     *
     * @return array<int, array<string, mixed>>
     */
    public function saved(): array
    {
        if (!$this->isManaged()) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($this->path()), true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn ($row): ?array => is_array($row) ? $this->finalizeIds($this->normalize($row), $row) : null, $decoded),
            static fn ($row): bool => $row !== null
        ));
    }

    /**
     * The list the front-end should render: the managed JSON once it exists,
     * otherwise the supplied hard-coded defaults (with ids assigned).
     *
     * @param array<int, array<string, mixed>> $defaults
     * @return array<int, array<string, mixed>>
     */
    public function resolve(array $defaults): array
    {
        if ($this->isManaged()) {
            return $this->saved();
        }

        return array_map(fn (array $d): array => $d + ['id' => $this->idFor($d)], $defaults);
    }

    /**
     * Ensure the JSON exists (seed from defaults on first admin visit) and
     * return the editable list.
     *
     * @param array<int, array<string, mixed>> $defaults
     * @return array<int, array<string, mixed>>
     */
    public function ensureSeeded(array $defaults): array
    {
        if (!$this->isManaged()) {
            $seed = array_map(function (array $d): array {
                $clean = $this->normalize($d) ?? [];
                $clean['id'] = $this->idFor($d);
                return $clean;
            }, $defaults);
            $this->persist($seed);
        }

        return $this->saved();
    }

    /**
     * Create or update one item. Returns the item id.
     *
     * @param array<string, mixed> $input
     */
    public function upsert(array $input): string
    {
        $clean = $this->normalize($input);
        if ($clean === null) {
            throw new \RuntimeException('Please fill in the required fields.');
        }

        $id = trim((string) ($input['id'] ?? ''));
        if ($id === '') {
            $id = bin2hex(random_bytes(5));
        }
        $clean['id'] = $id;

        $items = $this->saved();
        $replaced = false;
        foreach ($items as $i => $existing) {
            if ((string) ($existing['id'] ?? '') === $id) {
                $items[$i] = $clean;
                $replaced = true;
                break;
            }
        }
        if (!$replaced) {
            $items[] = $clean;
        }

        $this->persist($items);

        return $id;
    }

    public function delete(string $id): void
    {
        $items = array_values(array_filter(
            $this->saved(),
            static fn (array $item): bool => (string) ($item['id'] ?? '') !== $id
        ));
        $this->persist($items);
    }

    /** Move an item up or down for display ordering. */
    public function move(string $id, string $direction): void
    {
        $items = $this->saved();
        foreach ($items as $i => $item) {
            if ((string) ($item['id'] ?? '') !== $id) {
                continue;
            }
            $swap = $direction === 'up' ? $i - 1 : $i + 1;
            if (isset($items[$swap])) {
                [$items[$i], $items[$swap]] = [$items[$swap], $items[$i]];
                $this->persist($items);
            }
            return;
        }
    }

    /**
     * @param array<string, mixed>|null $clean
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private function finalizeIds(?array $clean, array $row): ?array
    {
        if ($clean === null) {
            return null;
        }
        $clean['id'] = trim((string) ($row['id'] ?? '')) !== '' ? (string) $row['id'] : $this->idFor($row);
        return $clean;
    }

    /** @param array<string, mixed> $item */
    private function idFor(array $item): string
    {
        if (!empty($item['id'])) {
            return (string) $item['id'];
        }
        if (!empty($item['slug'])) {
            return (string) $item['slug'];
        }
        return substr(md5(json_encode($item) ?: (string) mt_rand()), 0, 10);
    }

    /** @param array<int, array<string, mixed>> $items */
    protected function persist(array $items): void
    {
        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->path(), json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    protected function path(): string
    {
        return base_path('storage/data/' . $this->filename());
    }

    protected function text(mixed $value, int $max): string
    {
        return mb_substr(trim(strip_tags((string) $value)), 0, $max);
    }
}
